<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use Generator;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Migration\Util\CastValue;
use MyParcelNL\PrestaShop\Migration\Util\DataMigrator;
use MyParcelNL\PrestaShop\Migration\Util\MigratableValue;
use MyParcelNL\PrestaShop\Migration\Util\ToDeliveryTypeName;
use MyParcelNL\PrestaShop\Migration\Util\ToPackageTypeName;
use MyParcelNL\PrestaShop\Migration\Util\TransformValue;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;

final class PdkDeliveryOptionsMigration extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\PrestaShop\Migration\Util\DataMigrator
     */
    private $dataMigrator;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $orderDataRepository;

    /**
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository $orderDataRepository
     * @param  \MyParcelNL\PrestaShop\Migration\Util\DataMigrator      $dataMigrator
     */
    public function __construct(PsOrderDataRepository $orderDataRepository, DataMigrator $dataMigrator)
    {
        parent::__construct();

        $this->orderDataRepository = $orderDataRepository;
        $this->dataMigrator        = $dataMigrator;
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->migrateDeliveryOptions();

        // Done to avoid constraint errors in the next migration, which also involves the order_data table.
        EntityManager::flush();
    }

    /**
     * @return \Generator<MigratableValue>
     */
    private function getDeliveryOptionsTransformationMap(): Generator
    {
        yield new MigratableValue(
            'carrier',
            DeliveryOptions::CARRIER,
            new TransformValue(function ($value) {
                $carriers     = new Collection(Platform::getCarriers());
                $carrierNames = $carriers->pluck('name');

                return in_array($value, $carrierNames->toArray(), true)
                    ? $value
                    : Platform::get('defaultCarrier');
            })
        );

        yield new MigratableValue('deliveryType', DeliveryOptions::DELIVERY_TYPE, new ToDeliveryTypeName());
        yield new MigratableValue('packageType', DeliveryOptions::PACKAGE_TYPE, new ToPackageTypeName());

        yield new MigratableValue(
            'date',
            DeliveryOptions::DATE,
            new CastValue(CastValue::CAST_DATE, true)
        );

        yield new MigratableValue(
            'labelAmount',
            DeliveryOptions::LABEL_AMOUNT,
            new TransformValue(function ($value) {
                return is_int($value) && $value > 0 ? $value : 1;
            })
        );

        yield new MigratableValue(
            'pickupLocation',
            DeliveryOptions::PICKUP_LOCATION,
            new CastValue(CastValue::CAST_ARRAY, true)
        );

        yield new MigratableValue(
            'shipmentOptions',
            DeliveryOptions::SHIPMENT_OPTIONS,
            new CastValue(CastValue::CAST_ARRAY)
        );
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     */
    private function migrateDeliveryOptions(): void
    {
        $oldValues = $this->getAllRows(AbstractPsMigration::LEGACY_TABLE_DELIVERY_SETTINGS);

        $oldValues->each(function ($row) {
            if (! is_array($row)) {
                return;
            }

            $cartId  = json_decode((string) ($row['id_cart'] ?? ''), true);
            $orderId = $this->getDbValue('orders', 'id_order', "id_cart = $cartId");

            if (! $orderId) {
                Logger::info("No order found for cart $cartId");

                return;
            }

            $deliverySettings = json_decode($row['delivery_settings'] ?? '', true);
            $extraOptions     = json_decode($row['extra_options'] ?? '', true);

            $newDeliveryOptions = $this->transformDeliveryOptions(array_merge($deliverySettings, $extraOptions));

            $this->orderDataRepository->updateOrCreate(
                [
                    'orderId' => (string) $orderId,
                ],
                [
                    'data' => json_encode(['deliveryOptions' => $newDeliveryOptions]),
                ]
            );

            Logger::debug("Migrated delivery options for order $orderId");
        });
    }

    /**
     * @param  null|array $data
     *
     * @return array
     */
    private function transformDeliveryOptions(?array $data): array
    {
        $shipmentOptions = $this->transformShipmentOptions($data['shipmentOptions'] ?? null);

        return array_replace(
            $this->dataMigrator->transform($data, $this->getDeliveryOptionsTransformationMap()),
            [
                DeliveryOptions::SHIPMENT_OPTIONS => $shipmentOptions,
            ]
        );
    }

    /**
     * @param  mixed $data
     *
     * @return array
     */
    private function transformShipmentOptions($data): array
    {
        if (! is_array($data)) {
            return [];
        }

        $shipmentOptionsMigrationMap = array_map(
            static function (string $key): MigratableValue {
                return new MigratableValue($key, $key, new CastValue(CastValue::CAST_TRI_STATE));
            },
            array_keys($data)
        );

        return $this->dataMigrator->transform($data, $shipmentOptionsMigrationMap);
    }
}

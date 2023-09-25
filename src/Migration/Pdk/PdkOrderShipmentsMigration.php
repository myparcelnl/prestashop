<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;

final class PdkOrderShipmentsMigration extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    private $pdkOrderRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     */
    public function __construct(PdkOrderRepositoryInterface $pdkOrderRepository)
    {
        parent::__construct();
        $this->pdkOrderRepository = $pdkOrderRepository;
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->migrateOrderShipments();
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private function migrateOrderShipments(): void
    {
        $orderLabels = $this->getAllRows(AbstractLegacyPsMigration::LEGACY_TABLE_ORDER_LABEL);

        $orderLabels->each(function (array $orderLabel) {
            $orderId    = $orderLabel['id_order'] ?? null;
            $shipmentId = $orderLabel['id_label'] ?? null;

            $pdkOrder = $this->pdkOrderRepository->get($orderId);

            if (! $pdkOrder->externalIdentifier) {
                Logger::info("Order $orderId was not found");

                return;
            }

            if ($pdkOrder->shipments->containsStrict('id', $shipmentId)) {
                Logger::info("Shipment $shipmentId was already migrated for order $orderId");

                return;
            }

            $shipment = $pdkOrder->createShipment();

            $shipment->id      = (int) $shipmentId;
            $shipment->barcode = $orderLabel['barcode'];
            $shipment->status  = $orderLabel['status'];
        });
    }
}

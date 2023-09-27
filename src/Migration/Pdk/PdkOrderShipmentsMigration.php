<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use Throwable;

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

        $ordersToUpdate = $orderLabels->reduce(function (
            PdkOrderCollection $carry,
            array              $orderLabel
        ): PdkOrderCollection {
            $orderId = $orderLabel['id_order'] ?? null;

            try {
                $pdkOrder = $this->pdkOrderRepository->get($orderId);
            } catch (Throwable $e) {
                Logger::error("Order $orderId was not found", ['exception' => $e]);

                return $carry;
            }

            $shipmentId = $orderLabel['id_label'] ?? null;

            if ($pdkOrder->shipments->containsStrict('id', $shipmentId)) {
                Logger::info("Shipment $shipmentId was already migrated for order $orderId");

                return $carry;
            }

            $shipment = $pdkOrder->createShipment();

            $shipment->id      = $shipmentId;
            $shipment->barcode = $orderLabel['barcode'];
            $shipment->status  = $orderLabel['status'];

            $pdkOrder->shipments->push($shipment);

            $carry->put($pdkOrder->externalIdentifier, $pdkOrder);

            return $carry;
        }, new PdkOrderCollection());

        $this->pdkOrderRepository->updateMany($ordersToUpdate->values());
    }
}

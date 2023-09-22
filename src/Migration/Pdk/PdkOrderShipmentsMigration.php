<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;

final class PdkOrderShipmentsMigration extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository $orderShipmentRepository
     */
    public function __construct(PsOrderShipmentRepository $orderShipmentRepository)
    {
        parent::__construct();
        $this->orderShipmentRepository = $orderShipmentRepository;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     */
    public function up(): void
    {
        $this->migrateOrderShipments();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    private function migrateOrderShipments(): void
    {
        $orderLabels = $this->getAllRows(AbstractLegacyPsMigration::LEGACY_TABLE_ORDER_LABEL);

        $orderLabels->each(function (array $orderLabel) {
            $orderId = $orderLabel['id_order'];

            $this->orderShipmentRepository->updateOrCreate(
                [
                    'shipmentId' => (int) $orderLabel['id_label'],
                ],
                [
                    'orderId' => (string) $orderId,
                    'data'    => json_encode([
                        'id'                  => $orderLabel['id_label'] ?? null,
                        'orderId'             => $orderId,
                        'referenceIdentifier' => $orderId,
                        'barcode'             => $orderLabel['barcode'] ?? null,
                        'status'              => $orderLabel['status'] ?? null,
                    ]),
                ]
            );
        });
    }
}

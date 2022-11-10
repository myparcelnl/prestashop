<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Factory\OrderStatus;

use Configuration;
use MyParcelNL\PrestaShop\Collection\OrderStatusUpdateCollection;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Entity\OrderStatus\DeliveredOrderStatusUpdate;
use MyParcelNL\PrestaShop\Entity\OrderStatus\PrintedOrderStatusUpdate;
use MyParcelNL\PrestaShop\Entity\OrderStatus\ShippedOrderStatusUpdate;

class OrderStatusUpdateCollectionFactory
{
    private const SHIPMENT_STATUS_PENDING_REGISTERED         = 2;
    private const SHIPMENT_STATUS_EN_ROUTE_HANDED_TO_CARRIER = 3;
    private const SHIPMENT_STATUS_DELIVERED                  = 7;
    private const SHIPMENT_STATUS_PRINTED_STAMP              = 14;

    /**
     * @param  int $shipmentId
     * @param  int $shipmentStatus
     *
     * @return \MyParcelNL\PrestaShop\Collection\OrderStatusUpdateCollection
     * @throws \Exception
     */
    public static function create(int $shipmentId, int $shipmentStatus): OrderStatusUpdateCollection
    {
        $updates = new OrderStatusUpdateCollection();

        if (self::SHIPMENT_STATUS_PRINTED_STAMP === $shipmentStatus) {
            $updates->push(new PrintedOrderStatusUpdate($shipmentId));

            if (Configuration::get(Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME)) {
                $updates->push(new ShippedOrderStatusUpdate($shipmentId));
            }

            return $updates;
        }

        if ($shipmentStatus >= self::SHIPMENT_STATUS_PENDING_REGISTERED) {
            $updates->push(new PrintedOrderStatusUpdate($shipmentId, true));
        }

        if ($shipmentStatus >= self::SHIPMENT_STATUS_EN_ROUTE_HANDED_TO_CARRIER) {
            $updates->push(new ShippedOrderStatusUpdate($shipmentId, true));
        }

        if ($shipmentStatus >= self::SHIPMENT_STATUS_DELIVERED) {
            $updates->push(new DeliveredOrderStatusUpdate($shipmentId, true));
        }

        return $updates;
    }
}

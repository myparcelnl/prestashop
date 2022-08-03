<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\OrderStatus;

use MyParcelNL\PrestaShop\Constant;

class DeliveredOrderStatusUpdate extends AbstractOrderStatusUpdate
{
    /**
     * @return string
     */
    public function getOrderStatusSetting(): string
    {
        return Constant::DELIVERED_ORDER_STATUS_CONFIGURATION_NAME;
    }
}

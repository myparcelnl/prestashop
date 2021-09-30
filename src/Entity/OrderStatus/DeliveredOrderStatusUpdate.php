<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity\OrderStatus;

use Gett\MyparcelBE\Constant;

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

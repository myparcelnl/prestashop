<?php

namespace Gett\MyparcelBE\Listener;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Service\Order\OrderStatusChange;

class MyparcelOrderLabelListener
{
    /**
     * @param $orderId
     * @param $status
     */
    public static function prePersist($orderId, $status): void
    {
        $orderStatusChange = new OrderStatusChange();

        if ($status) {
            $orderStatusChange->changeOrderStatus($orderId, $status, Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME);
        }
    }
}


<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Logger;

use Configuration;
use Gett\MyparcelBE\Constant;
use Order;

class OrderLogger extends FileLogger
{
    /**
     * @param  array{message: \Throwable|array|string, order: \Gett\MyparcelBE\Model\Core\Order|int} $message
     * @param  int                                                                                   $level
     */
    public static function addLog(
        $message,
        int $level = self::DEBUG
    ): void {
        if (! Configuration::get(Constant::API_LOGGING_CONFIGURATION_NAME)) {
            return;
        }

        $order   = $message['order'];
        $orderId = (int) ($order instanceof Order ? $order->id : $order);
        $string  = self::createMessage($message['message'], $level);

        self::getLogger()
            ->log(sprintf('Order #%d: %s', $orderId, $string));
    }
}

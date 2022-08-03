<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Logger;

use InvalidArgumentException;
use Order;

class OrderLogger extends PdkLogger
{
    public function log($level, $message, array $context = []): void
    {
        if (! isset($context['order'])) {
            throw new InvalidArgumentException(
                sprintf('You must pass "order" in context when using %s', static::class)
            );
        }

        $order   = $context['order'];
        $orderId = (int) ($order instanceof Order ? $order->id : $order);

        parent::log($level, sprintf('Order #%d: %s', $orderId, $message), $context);
    }
}

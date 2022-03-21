<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Factory;

use Gett\MyparcelBE\Entity\Cache;
use Gett\MyparcelBE\Model\Core\Order;

class OrderFactory
{
    /**
     * @param  int|string $orderId
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function create($orderId): Order
    {
        return Cache::remember("myparcelbe_order_$orderId", static function () use ($orderId) {
            return new Order($orderId);
        });
    }
}

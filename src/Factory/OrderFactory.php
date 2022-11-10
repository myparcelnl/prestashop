<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Factory;

use MyParcelNL\PrestaShop\Entity\Cache;
use MyParcelNL\PrestaShop\Model\Core\Order;

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
        return Cache::remember("myparcelnl_order_$orderId", static function () use ($orderId) {
            return new Order($orderId);
        });
    }
}

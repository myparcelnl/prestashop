<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Factory;

use MyParcelNL\PrestaShop\Entity\Cache;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\OrderSettings\OrderSettings;

class OrderSettingsFactory
{
    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order|int|string $orderOrId
     *
     * @return \MyParcelNL\PrestaShop\OrderSettings\OrderSettings
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function create($orderOrId): OrderSettings
    {
        $order = $orderOrId instanceof Order ? $orderOrId : OrderFactory::create($orderOrId);

        return Cache::remember("myparcelnl_order_settings_{$order->getId()}", static function () use ($order) {
            return new OrderSettings($order);
        });
    }
}

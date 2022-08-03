<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Factory;

use Gett\MyparcelBE\Entity\Cache;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\OrderSettings\OrderSettings;

class OrderSettingsFactory
{
    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order|int|string $orderOrId
     *
     * @return \Gett\MyparcelBE\OrderSettings\OrderSettings
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public static function create($orderOrId): OrderSettings
    {
        $order = $orderOrId instanceof Order ? $orderOrId : OrderFactory::create($orderOrId);

        return Cache::remember("myparcelbe_order_settings_{$order->getId()}", static function () use ($order) {
            return new OrderSettings($order);
        });
    }
}

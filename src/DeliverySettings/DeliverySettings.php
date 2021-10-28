<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use OrderCore;

class DeliverySettings
{
    /**
     * @param \OrderCore $order
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    public static function getDeliveryOptionsFromOrder(OrderCore $order): AbstractDeliveryOptionsAdapter
    {
        return DeliverySettingsRepository::getInstance()::getDeliveryOptionsByCartId((int) $order->id_cart);
    }

    /**
     * @param \OrderCore $order
     *
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    public static function getExtraOptionsFromOrder(OrderCore $order): ExtraOptions
    {
        return DeliverySettingsRepository::getInstance()::getExtraOptionsByCartId((int) $order->id_cart);
    }

    public static function setDeliveryOptionsForOrder(
        AbstractDeliveryOptionsAdapter $deliveryOptions,
        OrderCore $order
    ): void
    {
        DeliverySettingsRepository::getInstance()::setDeliveryOptionsForOrder($deliveryOptions, $order);
    }

    public static function setExtraOptionsForOrder(ExtraOptions $extraOptions, OrderCore $order): void
    {
        DeliverySettingsRepository::getInstance()::setExtraOptionsForOrder($extraOptions, $order);
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public static function persist(): void
    {
        DeliverySettingsRepository::getInstance()::persist();
    }
}

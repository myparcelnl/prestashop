<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use Gett\MyparcelBE\Model\Core\Order;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;

class DeliverySettings
{
    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    public static function getDeliveryOptionsFromOrder(Order $order): AbstractDeliveryOptionsAdapter
    {
        return DeliverySettingsRepository::getInstance()::getDeliveryOptionsByCartId((int) $order->id_cart);
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    public static function getExtraOptionsFromOrder(Order $order): ExtraOptions
    {
        return DeliverySettingsRepository::getInstance()::getExtraOptionsByCartId((int) $order->id_cart);
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public static function persist(): void
    {
        DeliverySettingsRepository::getInstance()::persist();
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     * @param  \Gett\MyparcelBE\Model\Core\Order                                          $order
     *
     * @return void
     */
    public static function setDeliveryOptionsForOrder(
        AbstractDeliveryOptionsAdapter $deliveryOptions,
        Order                          $order
    ): void {
        DeliverySettingsRepository::getInstance()::setDeliveryOptionsForOrder($deliveryOptions, $order);
    }

    /**
     * @param  \Gett\MyparcelBE\DeliverySettings\ExtraOptions $extraOptions
     * @param  \Gett\MyparcelBE\Model\Core\Order              $order
     *
     * @return void
     */
    public static function setExtraOptionsForOrder(ExtraOptions $extraOptions, Order $order): void
    {
        DeliverySettingsRepository::getInstance()::setExtraOptionsForOrder($extraOptions, $order);
    }
}

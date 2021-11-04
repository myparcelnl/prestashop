<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use Exception;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use Order;

class DeliveryOptions
{
    /**
     * @param  int $cartId
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter|null
     * @throws \Exception
     */
    public static function getFromCart(int $cartId): ?AbstractDeliveryOptionsAdapter
    {
        $deliveryOptions = self::queryByCart($cartId);

        if ($deliveryOptions) {
            return DeliveryOptionsAdapterFactory::create($deliveryOptions);
        }

        return null;
    }

    /**
     * @param  \Order|int $orderOrId
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter|null
     * @throws \Exception
     */
    public static function getFromOrder($orderOrId): ?AbstractDeliveryOptionsAdapter
    {
        $order           = self::getOrder($orderOrId);
        $deliveryOptions = self::queryByOrder($order);

        if ($deliveryOptions) {
            return DeliveryOptionsAdapterFactory::create($deliveryOptions);
        }

        return null;
    }

    /**
     * @param  int $cartId
     *
     * @return array
     * @deprecated use getFromCart() – Visibility of this method will be reduced in the future.
     */
    public static function queryByCart(int $cartId): array
    {
        $deliveryOptions = DeliverySettingsRepository::getInstance()::getDeliveryOptionsByCartId($cartId);

        if (! $deliveryOptions) {
            return [];
        }

        return $deliveryOptions->toArray();
    }

    /**
     * @param  \Order $order
     *
     * @return null|array|object
     */
    public static function queryByOrder(Order $order): array
    {
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return self::queryByCart((int) $order->id_cart);
    }

    /**
     * @param  int $orderId
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @deprecated use getFromOrder() – Visibility of this method will be reduced in the future.
     */
    public static function queryByOrderId(int $orderId): array
    {
        $order = new Order($orderId);
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return self::queryByCart((int) $order->id_cart);
    }

    /**
     * @param  \Order|int $orderOrId
     *
     * @return \Order
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    private static function getOrder($orderOrId): Order
    {
        if (is_int($orderOrId)) {
            return new Order($orderOrId);
        }

        if (is_a($orderOrId, Order::class)) {
            return $orderOrId;
        }

        throw new Exception('Order or order_id must be passed.');
    }
}

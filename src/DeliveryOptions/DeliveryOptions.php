<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Exception;
use Gett\MyparcelBE\Adapter\DeliveryOptionsFromDefaultExportSettingsAdapter;
use Gett\MyparcelBE\Adapter\DeliveryOptionsFromFormAdapter;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\DeliverySettings\DeliverySettingsRepository;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use Order;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

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

        if (! empty($deliveryOptions)) {
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
     * @param  int   $cartId
     * @param  array $deliveryOptions
     * @param  array $extraOptions
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public static function save(int $cartId, array $deliveryOptions = [], array $extraOptions = []): void
    {
        $values = [
            'id_cart' => $cartId,
        ];

        if (! empty($deliveryOptions)) {
            $values['delivery_settings'] = pSQL(json_encode($deliveryOptions));
        }

        if (! empty($extraOptions)) {
            $values['extra_options'] = pSQL(json_encode($extraOptions));
        }

        Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->insert(
                Table::TABLE_DELIVERY_SETTINGS,
                $values,
                false,
                true,
                Db::REPLACE
            );
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     * @param  array                             $values
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     * @throws \Exception
     */
    public static function updateDeliveryOptions(
        \Gett\MyparcelBE\Model\Core\Order $order,
        array                             $values = []
    ): AbstractDeliveryOptionsAdapter {
        $deliveryOptionsToMerge = self::getDeliveryOptionsAdapters($order);

        if (! empty($values)) {
            $deliveryOptionsToMerge[] = new DeliveryOptionsFromFormAdapter($values);
        }

        $deliveryOptions = DeliveryOptionsMerger::create(...$deliveryOptionsToMerge);
        self::save($order->getIdCart(), $deliveryOptions->toArray());
        return $deliveryOptions;
    }

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\DbQuery $query
     *
     * @return array
     */
    private static function executeQuery(DbQuery $query): array
    {
        $query->orderBy('id_delivery_setting DESC');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getValue($query);

        if ('null' === $result || empty($result)) {
            return [];
        }

        return json_decode($result, true);
    }

    /**
     * @param  int $psCarrierId
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private static function getDefaultExportDeliveryOptions(int $psCarrierId): AbstractDeliveryOptionsAdapter
    {
        $defaultExportSettings = DefaultExportSettingsRepository::getInstance()
            ->getByCarrier($psCarrierId);

        return new DeliveryOptionsFromDefaultExportSettingsAdapter($defaultExportSettings);
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter[]
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private static function getDeliveryOptionsAdapters(\Gett\MyparcelBE\Model\Core\Order $order): array
    {
        $deliveryOptionsArray = [];

        $defaultExportDeliveryOptions = self::getDefaultExportDeliveryOptions($order->getIdCarrier());
        $deliveryOptionsArray[]       = $defaultExportDeliveryOptions;

        $orderDeliveryOptions = self::getFromOrder($order->getId());
        if ($orderDeliveryOptions) {
            $deliveryOptionsArray[] = $orderDeliveryOptions;
        }

        return $deliveryOptionsArray;
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

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use Gett\MyparcelBE\Database\Table;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use OrderCore;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

if (! class_exists('DeliverySettingsRepository')) :

class DeliverySettingsRepository
{
    private static $instance;

    private static $deliverySettingsByCartId = [];

    public static function setDeliveryOptionsForOrder(
        AbstractDeliveryOptionsAdapter $deliveryOptions,
        OrderCore $order
    ): void
    {
        self::$deliverySettingsByCartId[$order->id_cart]['deliveryOptions'] = $deliveryOptions;
    }

    public static function setExtraOptionsForOrder(ExtraOptions $extraOptions, OrderCore $order): void
    {
        self::$deliverySettingsByCartId[$order->id_cart]['extraOptions'] = $extraOptions;
    }

    private static function loadDeliverySettingsByCartId(int $cartId): void
    {
        if (array_key_exists($cartId, self::$deliverySettingsByCartId)) {
            return;
        }

        $query = self::getSelectQuery();
        $query->where('id_cart = ' . $cartId);

        $row = self::executeQuery($query);

        if (! $row) {
            self::$deliverySettingsByCartId[$cartId] = [
                'deliveryOptions' => new DeliveryOptionsV3Adapter(),
                'extraOptions'    => new ExtraOptions(),
            ];

            return;
        }
        try {
            $deliveryOptions = DeliveryOptionsAdapterFactory::create(json_decode($row['delivery_settings'], true) ??[]);
        } catch (\Exception $e) {
            $deliveryOptions = new DeliveryOptionsV3Adapter();
        }
        self::$deliverySettingsByCartId[$cartId]['deliveryOptions'] = $deliveryOptions;

        if (isset($row['extra_options'])) {
            $extraOptions = json_decode($row['extra_options'], true);
        } else {
            $extraOptions = [];
        }
        self::$deliverySettingsByCartId[$cartId]['extraOptions'] = new ExtraOptions($extraOptions);
    }

    /**
     * @param int $cartId
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    public static function getDeliveryOptionsByCartId(int $cartId): ?AbstractDeliveryOptionsAdapter
    {
        self::loadDeliverySettingsByCartId($cartId);

        return self::$deliverySettingsByCartId[$cartId]['deliveryOptions'];
    }

    /**
     * @param int $cartId
     *
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    public static function getExtraOptionsByCartId(int $cartId): ExtraOptions
    {
        self::loadDeliverySettingsByCartId($cartId);

        return self::$deliverySettingsByCartId[$cartId]['extraOptions'];
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public static function persist(): void
    {
        foreach (self::$deliverySettingsByCartId as $cartId => $deliverySettings) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)
                ->insert(
                    Table::TABLE_DELIVERY_SETTINGS,
                    [
                        'id_cart'           => $cartId,
                        'delivery_settings' => pSQL(json_encode($deliverySettings['deliveryOptions']->toArray())),
                        'extra_options'     => pSQL(json_encode($deliverySettings['extraOptions']->toArray())),
                    ],
                    false,
                    true,
                    Db::REPLACE
                );
        }
    }

    /**
     * @param  DbQuery $query
     *
     * @return array
     */
    private static function executeQuery(DbQuery $query): array
    {
        $query->orderBy('id_delivery_setting DESC');

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->getRow($query);

        return $result ?: [];
    }

    /**
     * @return DbQuery
     */
    private static function getSelectQuery(): DbQuery
    {
        $query = new DbQuery();
        $query->select('delivery_settings, extra_options');
        $query->from(Table::TABLE_DELIVERY_SETTINGS);
        return $query;
    }

    /**
     * @return self
     */
    public static function getInstance(): DeliverySettingsRepository
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

endif;

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use Exception;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Entity\Cache;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

class DeliverySettingsRepository
{
    /**
     * @param  int $cartId
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    public static function getDeliveryOptionsByCartId(int $cartId): ?AbstractDeliveryOptionsAdapter
    {
        return self::getByCartId($cartId)['deliveryOptions'];
    }

    /**
     * @param  int $cartId
     *
     * @return \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     */
    public static function getExtraOptionsByCartId(int $cartId): ExtraOptions
    {
        return self::getByCartId($cartId)['extraOptions'];
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
     * @param  int $cartId
     *
     * @return array{
     *     delivery_options: \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter,
     *     extraOptions: \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     * }
     */
    private static function getByCartId(int $cartId): array
    {
        return Cache::remember("myparcelbe_cart_delivery_settings_$cartId", static function () use ($cartId) {
            $query = self::getSelectQuery();
            $query->where("id_cart = $cartId");

            $row = self::executeQuery($query);

            if (! $row) {
                return [
                    'deliveryOptions' => new DeliveryOptionsV3Adapter(),
                    'extraOptions'    => new ExtraOptions(),
                ];
            }

            try {
                $deliveryOptions = DeliveryOptionsAdapterFactory::create(
                    json_decode($row['delivery_settings'], true) ?? []
                );
            } catch (Exception $e) {
                $deliveryOptions = new DeliveryOptionsV3Adapter();
            }

            $array['deliveryOptions'] = $deliveryOptions;

            $extraOptions = [];
            if (! empty($row['extra_options'])) {
                $extraOptions = json_decode($row['extra_options'], true);
            }

            $array['extraOptions'] = new ExtraOptions($extraOptions);

            return $array;
        });
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
}

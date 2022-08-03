<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliverySettings;

use Exception;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Entity\Cache;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

class DeliverySettingsRepository
{
    /**
     * @param  int $cartId
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     */
    public static function getDeliveryOptionsByCartId(int $cartId): ?DeliveryOptions
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
     *     delivery_options: \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions,
     *     extraOptions: \Gett\MyparcelBE\DeliverySettings\ExtraOptions
     * }
     */
    private static function getByCartId(int $cartId): array
    {
        return Cache::remember("myparcelbe_cart_delivery_settings_$cartId", static function () use ($cartId) {
            $query = self::getSelectQuery();
            $query->where("id_cart = $cartId");

            $row             = self::executeQuery($query);
            $deliveryOptions = new DeliveryOptions();

            if (! $row) {
                return [
                    'deliveryOptions' => $deliveryOptions,
                    'extraOptions'    => new ExtraOptions(),
                ];
            }

            try {
                $deliveryOptionsData = json_decode($row['delivery_settings'], true);
                $array               = Arr::only($deliveryOptionsData, array_keys($deliveryOptions->getAttributes()));

                $deliveryOptions->fill($array);
            } catch (Exception $e) {
                // Nothing
            }

            $extraOptions = [];

            if (! empty($row['extra_options'])) {
                $extraOptions = json_decode($row['extra_options'], true);
            }

            $array['deliveryOptions'] = $deliveryOptions;
            $array['extraOptions']    = new ExtraOptions($extraOptions);

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

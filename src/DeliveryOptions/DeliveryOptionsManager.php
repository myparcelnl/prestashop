<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\DeliveryOptions;

use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\DeliverySettings\DeliverySettingsRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

class DeliveryOptionsManager
{
    /**
     * @param  int $cartId
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions|null
     * @throws \Exception
     */
    public static function getFromCart(int $cartId): ?DeliveryOptions
    {
        $deliveryOptions = self::queryByCart($cartId);

        if ($deliveryOptions) {
            return new DeliveryOptions($deliveryOptions);
        }

        return null;
    }

    /**
     * @param  int $cartId
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @deprecated use getFromCart() – Visibility of this method will be reduced in the future.
     */
    public static function queryByCart(int $cartId): array
    {
        $deliveryOptions = DeliverySettingsRepository::getDeliveryOptionsByCartId($cartId);

        if (! $deliveryOptions) {
            return [];
        }

        return $deliveryOptions->toArray();
    }

    /**
     * @param  int                                                 $cartId
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     * @param  array                                               $extraOptions
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \PrestaShopDatabaseException
     */
    public static function save(int $cartId, ?DeliveryOptions $deliveryOptions, array $extraOptions = []): void
    {
        $values = [
            'id_cart' => $cartId,
        ];

        if ($deliveryOptions) {
            $values['delivery_settings'] = pSQL(json_encode($deliveryOptions->toArray()));
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
}

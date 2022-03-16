<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\DeliverySettings\DeliverySettingsRepository;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

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
     * @param  int $cartId
     *
     * @return array
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
}

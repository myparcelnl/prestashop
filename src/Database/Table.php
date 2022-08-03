<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Database;

class Table
{
    public const TABLE_SETTINGS              = 'myparcelbe_settings';
    public const TABLE_ORDER_DATA            = 'myparcelbe_order_data';
    public const TABLE_CART_DELIVERY_OPTIONS = 'myparcelbe_cart_delivery_options';
    public const TABLE_PRODUCT_SETTINGS      = 'myparcelbe_product_settings';
    /**
     * @deprecated
     */
    public const  TABLE_CARRIER_CONFIGURATION = 'myparcelbe_carrier_configuration';
    /**
     * @deprecated
     */
    public const  TABLE_DELIVERY_SETTINGS = 'myparcelbe_delivery_settings';
    /**
     * @deprecated
     */
    public const  TABLE_ORDER_LABEL = 'myparcelbe_order_label';
    /**
     * @deprecated
     */
    public const  TABLE_PRODUCT_CONFIGURATION = 'myparcelbe_product_configuration';
    private const PREFIX                      = _DB_PREFIX_;

    /**
     * @param  string $table
     *
     * @return string
     */
    public static function withPrefix(string $table): string
    {
        return self::PREFIX . $table;
    }
}

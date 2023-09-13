<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Database;

final class Table
{
    public const TABLE_CARRIER_MAPPING       = 'myparcelnl_carrier_mapping';
    public const TABLE_CART_DELIVERY_OPTIONS = 'myparcelnl_cart_delivery_options';
    public const TABLE_ORDER_DATA            = 'myparcelnl_order_data';
    public const TABLE_ORDER_SHIPMENT        = 'myparcelnl_order_shipment';
    public const TABLE_PRODUCT_SETTINGS      = 'myparcelnl_product_settings';

    /**
     * @param  string $table
     *
     * @return string
     */
    public static function withPrefix(string $table): string
    {
        return _DB_PREFIX_ . $table;
    }
}

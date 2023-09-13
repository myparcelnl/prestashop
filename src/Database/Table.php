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
    /** @deprecated */
    public const LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    /** @deprecated */
    public const LEGACY_TABLE_DELIVERY_SETTINGS = 'myparcelnl_delivery_settings';
    /** @deprecated */
    public const LEGACY_TABLE_ORDER_LABEL = 'myparcelnl_order_label';
    /** @deprecated */
    public const LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';

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

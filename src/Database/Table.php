<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Database;

class Table
{
    private const PREFIX                      = _DB_PREFIX_;

    public const TABLE_CARRIER_CONFIGURATION = 'myparcelbe_carrier_configuration';
    public const TABLE_DELIVERY_SETTINGS     = 'myparcelbe_delivery_settings';
    public const TABLE_ORDER_LABEL           = 'myparcelbe_order_label';
    public const TABLE_PRODUCT_CONFIGURATION = 'myparcelbe_product_configuration';

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

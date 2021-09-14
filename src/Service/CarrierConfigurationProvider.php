<?php

namespace Gett\MyparcelBE\Service;

use Db;
use Gett\MyparcelBE\Database\Table;

class CarrierConfigurationProvider
{
    /**
     * @var array
     */
    public static $configuration;

    /**
     * @var string
     */
    protected static $table = Table::TABLE_PRODUCT_CONFIGURATION;

    /**
     * @param  int    $carrier_id
     * @param  string $name
     * @param  null   $default
     *
     * @return null|mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $carrier_id, string $name, $default = null)
    {
        if (! isset(static::$configuration[$carrier_id][$name])) {
            $table  = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
            $result = Db::getInstance()
                ->executeS(
                    <<<SQL
SELECT name,value FROM `$table` WHERE id_carrier = $carrier_id 
SQL
                );

            foreach ($result as $item) {
                static::$configuration[$carrier_id][$item['name']] = $item['value'];
            }
        }

        return isset(static::$configuration[$carrier_id][$name]) && static::$configuration[$carrier_id][$name]
            ? static::$configuration[$carrier_id][$name] : $default;
    }

    /**
     * @param  int    $carrier_id
     * @param  string $name
     * @param  string $value
     */
    public static function updateValue(int $carrier_id, string $name, string $value): void
    {
        Db::getInstance()
            ->update(
                self::$table,
                ['value' => pSQL($value)],
                'id_carrier = ' . $carrier_id . ' AND name = "' . pSQL($name) . '" '
            );
    }
}

<?php

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Database\Table;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

class CarrierConfigurationProvider
{
    /**
     * @var array
     */
    private static $configuration;

    /**
     * @var string
     */
    private static $table = Table::TABLE_CARRIER_CONFIGURATION;

    /**
     * @param  int    $carrierId
     * @param  string $name
     * @param  null   $default
     *
     * @return null|mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $carrierId, string $name, $default = null)
    {
        if (! isset(static::$configuration[$carrierId][$name])) {
            $query = (new DbQuery())
                ->select('name, value')
                ->from(self::$table)
                ->where('id_carrier = ' . $carrierId);

            $result = Db::getInstance()
                ->executeS($query);

            foreach ($result as $item) {
                static::$configuration[$carrierId][$item['name']] = $item['value'];
            }
        }

        return isset(static::$configuration[$carrierId][$name]) && static::$configuration[$carrierId][$name]
            ? static::$configuration[$carrierId][$name]
            : $default;
    }

    /**
     * @param  int    $carrierId
     * @param  string $name
     * @param  string $value
     */
    public static function updateValue(int $carrierId, string $name, string $value): void
    {
        Db::getInstance()
            ->update(
                self::$table,
                ['value' => pSQL($value)],
                'id_carrier = ' . $carrierId . ' AND name = "' . pSQL($name) . '" '
            );
    }
}

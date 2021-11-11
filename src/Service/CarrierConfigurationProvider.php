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
     * @var array
     */
    private static $legacyCarrierIdMap;

    /**
     * @var string
     */
    private static $table = Table::TABLE_CARRIER_CONFIGURATION;

    /**
     * @param int    $carrierId
     * @param string $name
     * @param null   $default
     *
     * @return null|mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $carrierId, string $name, $default = null)
    {
        self::fetchLegacyCarrierIdMap();
        if (! isset(static::$configuration[$carrierId][$name])) {
            $query = (new DbQuery())->select('name, value')
                ->from(self::$table)
                ->where('id_carrier = ' . (static::$legacyCarrierIdMap[$carrierId] ?? 0));

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
     * @param int    $carrierId
     * @param string $name
     * @param string $value
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

    /**
     * @throws \PrestaShopDatabaseException
     */
    private static function fetchLegacyCarrierIdMap(): void
    {
        if (isset(static::$legacyCarrierIdMap)) {
            return;
        }

        $query = new DbQuery();
        $query->select('current.id_carrier as current_carrier_id, old.id_carrier as old_carrier_id');
        $query->from(Table::withPrefix('carrier'), 'current');
        $query->innerJoin(Table::withPrefix('carrier'), 'old', 'old.name = current.name');
        $query->where('current.active=1');
        $query->where('current.deleted=0');
        $query->orderBy('current.id_carrier DESC');

        $rows = Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($query);
        foreach ($rows as $row) {
            static::$legacyCarrierIdMap[$row['old_carrier_id']] = $row['current_carrier_id'];
        }
    }
}

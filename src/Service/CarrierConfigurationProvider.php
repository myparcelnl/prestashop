<?php

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Entity\Cache;
use MyParcelBE;
use MyParcelNL\Sdk\src\Support\Collection;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

class CarrierConfigurationProvider
{
    public const COLUMN_ID_CARRIER = 'id_carrier';
    public const COLUMN_NAME       = 'name';
    public const COLUMN_VALUE      = 'value';

    /**
     * @var \MyParcelNL\Sdk\src\Support\Collection
     */
    private static $configuration;

    /**
     * @var string
     */
    private static $table = Table::TABLE_CARRIER_CONFIGURATION;

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    public static function all(): Collection
    {
        if (isset(static::$configuration)) {
            return static::$configuration;
        }

        $query = (new DbQuery())->select(implode(',', [self::COLUMN_NAME, self::COLUMN_VALUE, self::COLUMN_ID_CARRIER]))
            ->from(self::$table);

        $result = Db::getInstance()
            ->executeS($query);

        static::$configuration = new Collection($result);

        return static::$configuration;
    }

    public static function getPsCarriers(): array
    {
        return Cache::remember('ps_carriers', function () {
            $table      = Table::withPrefix('carrier');
            $moduleName = MyParcelBE::getModule()->name;
            $carriers   = Db::getInstance()
                ->executeS(
                    <<<SQL
SELECT *
FROM $table
WHERE external_module_name = '{$moduleName}'
         AND deleted = 0 
         ORDER BY position
         LIMIT 0, 50
SQL
                );
            return $carriers;
        });
    }


    /**
     * @param  int    $carrierId
     * @param  string $name
     * @param  null   $default
     *
     * @return mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $carrierId, string $name, $default = null)
    {
        $first = self::all()
            ->where(self::COLUMN_NAME, $name)
            ->where(self::COLUMN_ID_CARRIER, $carrierId)
            ->first();

        return $first ? $first['value'] : $default;
    }

    /**
     * @param int    $carrierId
     * @param string $name
     * @param string $value
     *
     * @return bool may return true even when nothing is updated, so verify this yourself if necessary
     */
    public static function updateValue(int $carrierId, string $name, string $value): bool
    {
        return Db::getInstance()
            ->update(
                self::$table,
                ['value' => pSQL($value)],
                sprintf('%s = %d AND %s = "%s"', self::COLUMN_ID_CARRIER, $carrierId, self::COLUMN_NAME, pSQL($name))
            );
    }

    public static function upsertValue(int $carrierId, string $name, string $value): bool
    {
        if (false !== self::get($carrierId,$name,false)) {
            return self::updateValue($carrierId, $name, $value);
        }

        $insert[] = [
            'id_carrier' => $carrierId,
            'name'       => pSQL($name),
            'value'      => pSQL($value),
        ];

        return Db::getInstance()
            ->insert(Table::TABLE_CARRIER_CONFIGURATION, $insert);
    }
}

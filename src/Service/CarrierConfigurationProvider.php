<?php

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\PrestaShop\Database\Table;
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
                sprintf('%s = %d AND %s = "%s"', self::COLUMN_ID_CARRIER, $carrierId, self::COLUMN_NAME, pSQL($name))
            );
    }
}

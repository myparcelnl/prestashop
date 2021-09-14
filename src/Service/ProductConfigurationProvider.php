<?php

namespace Gett\MyparcelBE\Service;

use Db;
use Gett\MyparcelBE\Database\Table;

class ProductConfigurationProvider
{
    /**
     * @var array
     */
    public static $products = [];

    /**
     * @param  int    $id_product
     * @param  string $param
     * @param  null   $default
     *
     * @return null|mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $id_product, string $param, $default = null)
    {
        if (! isset(static::$products[$id_product][$param])) {
            $table  = Table::withPrefix(Table::TABLE_PRODUCT_CONFIGURATION);
            $result = Db::getInstance()
                ->executeS(
                    <<<SQL
SELECT name, value FROM $table WHERE id_product = $id_product
SQL
                );
            foreach ($result as $item) {
                static::$products[$id_product][$item['name']] = $item['value'];
            }
        }

        return isset(static::$products[$id_product][$param]) && static::$products[$id_product][$param]
            ? static::$products[$id_product][$param] : $default;
    }
}

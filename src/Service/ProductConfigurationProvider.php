<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\PrestaShop\Database\Table;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\DbQuery;

/**
 * @deprecated
 */
class ProductConfigurationProvider
{
    /**
     * @var array
     */
    public static $products = [];

    /**
     * @param  int    $productId
     * @param  string $param
     * @param  null   $default
     *
     * @return null|mixed
     * @throws \PrestaShopDatabaseException
     */
    public static function get(int $productId, string $param, $default = null)
    {
        if (! isset(static::$products[$productId][$param])) {
            $query = (new DbQuery())
            ->select('name, value')
            ->from(Table::TABLE_PRODUCT_CONFIGURATION)
            ->where('id_product = ' . $productId);

            $result = Db::getInstance()
                ->executeS($query);

            foreach ($result as $item) {
                static::$products[$productId][$item['name']] = $item['value'];
            }
        }

        return isset(static::$products[$productId][$param]) && static::$products[$productId][$param]
            ? static::$products[$productId][$param] : $default;
    }
}

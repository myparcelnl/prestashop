<?php
/**
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelProductSetting
 *
 * @since 2.3.0
 */
class MyParcelProductSetting extends MyParcelObjectModel
{
    const CUSTOMS_DISABLE = 1;
    const CUSTOMS_SKIP = 2;
    const CUSTOMS_ENABLE = 3;

    const CUSTOMS_DISABLE_STRING = 'disable';
    const CUSTOMS_SKIP_STRING = 'skip';
    const CUSTOMS_ENABLE_STRING = 'enable';

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_product_setting',
        'primary' => 'id_myparcel_product_setting',
        'fields' => array(
            'id_product'      => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'classification'  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'CHAR(4)',
            ),
            'country'         => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(255)',
            ),
            'status'          => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
                'db_type'  => 'TINYINT(2)',
            ),
            'age_check'       => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
                'db_type'  => 'TINYINT(1)',
            ),
            'cooled_delivery' => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
                'db_type'  => 'TINYINT(1)',
            ),
        ),
    );
    /** @var int $id_product The Product ID to which this setting belongs */
    public $id_product;
    /** @var string $classification Product ISIC code */
    public $classification;
    /** @var string $country Product country of origin */
    public $country;
    /** @var int $status Product status */
    public $status;
    /** @var bool $age_check Age check */
    public $age_check;
    /** @var bool $cooled_delivery Cooled delivery */
    public $cooled_delivery;
    // @codingStandardsIgnoreEnd

    /**
     * Get MyParcelProductSetting by Product ID or Product object
     *
     * @param int|Product $product
     *
     * @return static
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getByProductId($product)
    {
        $option = new static();
        if ($product instanceof Product) {
            $idProduct = $product->id;
        } else {
            $idProduct = $product;
        }
        $option->id_product = (int) $idProduct;

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_product` = '.(int) $idProduct);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getrow($sql);
        if (empty($result)) {
            return $option;
        }

        $option->hydrate($result);
        // PrestaShop bug
        $option->status = (int) $option->status;
        if ($option->status !== static::CUSTOMS_ENABLE) {
            $defaultStatus = Configuration::get(MyParcel::DEFAULT_CONCEPT_CUSTOMS_STATUS);
            if ($defaultStatus === static::CUSTOMS_ENABLE_STRING) {
                $option->status = static::CUSTOMS_ENABLE;
                if (!$option->classification) {
                    $option->classification = (string) Configuration::get(MyParcel::DEFAULT_CONCEPT_CLASSIFICATION);
                }
                if (!$option->country) {
                    $option->country = (string) Configuration::get(MyParcel::DEFAULT_CONCEPT_COUNTRY_OF_ORIGIN);
                }
            }
        }

        $option->age_check = (bool) $option->age_check;
        $option->cooled_delivery = (bool) $option->cooled_delivery;
        if (!$option->status) {
            $option->status = static::CUSTOMS_DISABLE;
        }

        return $option;
    }

    /**
     * Save info for a single product
     *
     * @param int        $idProduct
     * @param string     $classification
     * @param string     $country
     * @param int|string $status
     * @param bool       $ageCheck
     * @param bool       $cooledDelivery
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.3.0
     */
    public static function saveSingle($idProduct, $classification, $country, $status, $ageCheck = false, $cooledDelivery = false)
    {
        $settings = static::getByProductId($idProduct);
        $settings->classification = $classification;
        $settings->country = $country;
        $settings->age_check = $ageCheck;
        $settings->cooled_delivery = $cooledDelivery;
        $settings->status = static::findNumericStatus($status);

        return $settings->save();
    }

    /**
     * Save info for multiple products
     *
     * @param array $info
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function saveMulti($info)
    {
        $success = true;
        $range = array_map('intval', array_keys($info));
        if (empty($range)) {
            return true;
        }
        $sql = new DbQuery();
        $sql->select('`'.bqSQL(Product::$definition['primary']).'`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`'.bqSQL(Product::$definition['primary']).'` IN ('.implode(',', array_map('intval', $range)).')');
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($results)) {
            $results = array();
        }
        $toUpdate = array_column($results, Product::$definition['primary']);
        $toInsert = array_diff($range, $toUpdate);
        foreach ($toUpdate as $key) {
            $arr = array(
                bqSQL(Product::$definition['primary']) => pSQL($key),
            );
            if (isset($info[$key]['classification']) && preg_match('/^\d{4}$/', $info[$key]['classification'])) {
                $arr['classification'] = pSQL($info[$key]['classification']);
            }
            if (isset($info[$key]['country'])) {
                $arr['country'] = pSQL($info[$key]['country']);
            }
            if (isset($info[$key]['status'])) {
                $arr['status'] = static::findNumericStatus($info[$key]['status']);
            }
            if (isset($info[$key]['ageCheck'])) {
                $arr['age_check'] = (bool) $info[$key]['ageCheck'];
            }
            if (isset($info[$key]['cooledDelivery'])) {
                $arr['cooled_delivery'] = (bool) $info[$key]['cooledDelivery'];
            }
            $success &= Db::getInstance()->update(bqSQL(static::$definition['table']), $arr, '`'.bqSQL(Product::$definition['primary']).'` = '.(int) $key);
        }
        foreach ($toInsert as $key) {
            $arr = array(
                bqSQL(Product::$definition['primary']) => pSQL($key),
            );
            if (isset($info[$key]['classification']) && preg_match('/^\d{4}$/', $info[$key]['classification'])) {
                $arr['classification'] = pSQL($info[$key]['classification']);
            }
            if (isset($info[$key]['country'])) {
                $arr['country'] = pSQL($info[$key]['country']);
            }
            if (isset($info[$key]['status'])) {
                $arr['status'] = static::findNumericStatus($info[$key]['status']);
            }
            if (isset($info[$key]['ageCheck'])) {
                $arr['age_check'] = (bool) $info[$key]['ageCheck'];
            }
            if (isset($info[$key]['cooledDelivery'])) {
                $arr['cooled_delivery'] = (bool) $info[$key]['cooledDelivery'];
            }
            $success &= Db::getInstance()->insert(bqSQL(static::$definition['table']), $arr);
        }

        return (bool) $success;
    }

    /**
     * Get customs lines filled with all the available info
     *
     * @param Order|int $idOrder
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.3.0
     */
    public static function getCustomsLines($idOrder)
    {
        $lines = array();
        if ($idOrder instanceof Order) {
            $order = $idOrder;
        } else {
            $order = new Order($idOrder);
        }
        if (!Validate::isLoadedObject($order)) {
            return $lines;
        }
        $orderDetails = $order->getOrderDetailList();
        /** @var Currency $eurCurrency */
        $eurCurrency = Currency::getCurrencyInstance(Currency::getIdByIsoCode('EUR'));
        /** @var Currency $defaultCurrency */
        $defaultCurrency = Currency::getDefaultCurrency();
        /** @var Currency $orderCurrency */
        $orderCurrency = Currency::getCurrencyInstance($order->id_currency);
        if (!Validate::isLoadedObject($eurCurrency) || !Validate::isLoadedObject($defaultCurrency)) {
            return $lines;
        }
        $conversion = 1;
        if ($eurCurrency->iso_code !== $defaultCurrency->iso_code) {
            $conversion = 1 / $eurCurrency->conversion_rate;
        }
        if ($orderCurrency->iso_code !== $eurCurrency->iso_code) {
            $conversion *= $orderCurrency->conversion_rate;
        }
        foreach ($orderDetails as $orderDetail) {
            $settings = static::getByProductId((int) $orderDetail['product_id']);
            if (!Validate::isLoadedObject($settings) || $settings->status === static::CUSTOMS_DISABLE) {
                return array();
            } elseif ($settings->status === static::CUSTOMS_SKIP) {
                continue;
            }
            $line = array(
                'description'    => Tools::substr($orderDetail['product_name'], 0, 50),
                'amount'         => (int) $orderDetail['product_quantity'],
                'weight'         => (int) ($orderDetail['product_weight'] * $orderDetail['product_quantity'] * (MyParcel::getWeightUnit() === 'kg' ? 1000 : 1)),
                'item_value'     => array(
                    'currency' => 'EUR',
                    'amount'   => (int) ($orderDetail['total_price_tax_incl'] * 100 * $conversion),
                ),
                'classification' => $settings->classification,
                'country'        => $settings->country,
            );

            if (!preg_match('/^\d{4}$/', $line['classification'])) {
                return array();
            }
            if (!$line['country']) {
                return array();
            }
            if (!$line['weight']) {
                return array();
            }
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * Get total weight in grams
     *
     * @param Order|int $idOrder
     *
     * @return float
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.3.0
     */
    public static function getTotalWeight($idOrder)
    {
        $weight = 0;
        if ($idOrder instanceof Order) {
            $order = $idOrder;
        } else {
            $order = new Order($idOrder);
        }
        if (!Validate::isLoadedObject($order)) {
            return (int) ($weight * (MyParcel::getWeightUnit() === 'kg' ? 1000 : 1));
        }
        $orderDetails = $order->getOrderDetailList();
        foreach ($orderDetails as $orderDetail) {
            $weight += (float) ($orderDetail['product_weight'] * $orderDetail['product_quantity']);
        }

        return (int) ($weight * (MyParcel::getWeightUnit() === 'kg' ? 1000 : 1));
    }

    /**
     * Find numeric status enum
     *
     * @param int|string $status
     *
     * @return int
     *
     * @since 2.3.0
     */
    protected static function findNumericStatus($status)
    {
        if (is_string($status)) {
            switch ($status) {
                case static::CUSTOMS_SKIP_STRING:
                    $status = static::CUSTOMS_SKIP;
                    break;
                case static::CUSTOMS_ENABLE_STRING:
                    $status = static::CUSTOMS_ENABLE;
                    break;
                default:
                    $status = static::CUSTOMS_DISABLE;
                    break;
            }
        } elseif (!in_array($status, array(static::CUSTOMS_ENABLE, static::CUSTOMS_SKIP))) {
            $status = static::CUSTOMS_SKIP;
        }

        return (int) $status;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function cartHasAgeCheck(Cart $cart)
    {
        $products = $cart->getProducts();
        $ids = array_map(function ($product) {
            return (int) $product['id_product'];
        }, $products);

        $sql = new DbQuery();
        $sql->select('`id_product`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`age_check` = 1');
        $sql->where('`id_product` IN ('.implode(',', array_map('intval', $ids)).')');

        $hasAgeCheck = (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($hasAgeCheck) {
            return true;
        }
        if (Configuration::get(MyParcel::DEFAULT_CONCEPT_AGE_CHECK)) {
            return true;
        }

        return false;
    }

    /**
     * @param Cart $cart
     *
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function cartHasCooledDelivery(Cart $cart)
    {
        $products = $cart->getProducts();
        $ids = array_map(function ($product) {
            return (int) $product['id_product'];
        }, $products);

        $sql = new DbQuery();
        $sql->select('`id_product`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`cooled_delivery` = 1');
        $sql->where('`id_product` IN ('.implode(',', array_map('intval', $ids)).')');

        $hasCooledDelivery = (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($hasCooledDelivery) {
            return true;
        }
        if (Configuration::get(MyParcel::DEFAULT_CONCEPT_COOLED_DELIVERY)) {
            return true;
        }

        return false;
    }
}

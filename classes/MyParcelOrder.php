<?php
/**
 * 2017-2018 DM Productions B.V.
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
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

require_once dirname(__FILE__).'/../myparcel.php';

/**
 * Class MyParcelOrder
 */
class MyParcelOrder extends MyParcelObjectModel
{
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_order',
        'primary' => 'id_myparcel_order',
        'fields'  => array(
            'id_order'      => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'id_shipment'   => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isInt',
                'required' => true,
                'db_type'  => 'BIGINT(20)',
            ),
            'retour'        => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1)',
            ),
            'tracktrace'    => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(32)',
            ),
            'postcode'      => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'VARCHAR(32)',
            ),
            'postnl_status' => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default'  => '1',
                'db_type'  => 'VARCHAR(255)',
            ),
            'date_upd'      => array(
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
                'db_type'  => 'DATETIME',
            ),
            'postnl_final'  => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1)',
            ),
            'shipment'      => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'TEXT',
            ),
            'type'          => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
                'default'  => '1',
                'db_type'  => 'TINYINT(1)',
            ),
        ),
    );
    /** @var int $id_order Order ID */
    public $id_order;
    /** @var int $id_shipment MyParcel consignment ID */
    public $id_shipment;
    /** @var bool $retour */
    public $retour;
    /** @var string $tracktrace */
    public $tracktrace;
    /** @var string $postcode */
    public $postcode;
    /** @var string $postnl_status */
    public $postnl_status;
    /** @var string $date_upd */
    public $date_upd;
    /** @var bool $postnl_final */
    public $postnl_final;
    /** @var string $shipment */
    public $shipment;
    /** @var int $type */
    public $type;
    // @codingStandardsIgnoreEnd

    /**
     * Get Delivery Option info by Cart
     *
     * @param int $idOrder
     *
     * @return string Delivery from DB
     */
    public static function getByOrder($idOrder)
    {
        $sql = new DbQuery();
        $sql->select('mo.*');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_order` = '.(int) $idOrder);

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        } catch (PrestaShopException $e) {
            $result = false;
        }

        if ($result) {
            return json_decode($result, true);
        }

        return false;
    }

    /**
     * Get MyParcelOrders by Order IDs
     *
     * @param array $idOrders
     *
     * @return array
     */
    public static function getByOrderIds($idOrders)
    {
        if (empty($idOrders)) {
            return array();
        }

        foreach ($idOrders as &$idOrder) {
            $idOrder = (int) $idOrder;
        }

        $sql = new DbQuery();
        $sql->select('mo.*');
        $sql->from(bqSQL(static::$definition['table']), 'mo');
        $sql->where('mo.`id_order` IN ('.implode(', ', $idOrders).')');

        try {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $results = array();
        }

        foreach ($results as &$result) {
            $result['shipment'] = json_decode($result['shipment']);
        }

        return (array) $results;
    }

    /**
     * Get by shipment ID
     *
     * @param int $idShipment
     *
     * @return bool|MyParcelOrder
     *
     * @since 2.0.0
     */
    public static function getByShipmentId($idShipment)
    {
        $sql = new DbQuery();
        $sql->select('mpo.*');
        $sql->from(bqSQL(static::$definition['table']), 'mpo');
        $sql->where('mpo.`id_shipment` = '.(int) $idShipment);

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        } catch (PrestaShopException $e) {
            $result = false;
        }

        if ($result) {
            $mpo = new MyParcelOrder();
            $mpo->hydrate($result);

            return $mpo;
        }

        return false;
    }

    /**
     * Get by shipment ID
     *
     * @param int $idShipment
     *
     * @return bool|Order
     *
     * @since 2.0.5
     */
    public static function getOrderByShipmentId($idShipment)
    {
        $sql = new DbQuery();
        $sql->select('mpo.`id_order`');
        $sql->from(bqSQL(static::$definition['table']), 'mpo');
        $sql->where('mpo.`id_shipment` = '.(int) $idShipment);

        try {
            $idOrder = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        } catch (PrestaShopException $e) {
            $idOrder = false;
        }

        if ($idOrder) {
            return new Order($idOrder);
        }

        return false;
    }

    /**
     * Update shipment status
     *
     * @param int    $idShipment Shipment ID
     * @param string $barcode    Barcode
     * @param int    $statusCode PostNL status code
     * @param string $date       Date
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public static function updateStatus($idShipment, $barcode, $statusCode, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }

        $order = MyParcelOrder::getOrderByShipmentId($idShipment);
        if (Validate::isLoadedObject($order)) {
            if (!$order->shipping_number) {
                // Checking a legacy field is allowed in this case
                static::updateOrderTrackingNumber($order, $barcode);
            }
        }

        try {
            if (Configuration::get(MyParcel::UPDATE_ORDER_STATUSES)) {
                if (Configuration::get(MyParcel::PRINTED_STATUS) && $statusCode >= 2) {
                    MyParcelOrderHistory::setPrinted($idShipment);
                }
                if (Configuration::get(MyParcel::SHIPPED_STATUS) && $statusCode >= 3) {
                    MyParcelOrderHistory::setShipped($idShipment);
                }
                if ($statusCode >= 7 && $statusCode <= 11
                    && Configuration::get(MyParcel::RECEIVED_STATUS)
                ) {
                    MyParcelOrderHistory::setReceived($idShipment);
                }
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("Myparcel module error: {$e->getMessage()}");
        }

        MyParcelOrderHistory::log($idShipment, $statusCode, $date);

        try {
            return (bool) Db::getInstance()->update(
                bqSQL(static::$definition['table']),
                array(
                    'tracktrace'    => pSQL($barcode),
                    'postnl_status' => (int) $statusCode,
                    'date_upd'      => pSQL($date),
                ),
                'id_shipment = '.(int) $idShipment
            );
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function add($autoDate = true, $nullValues = false)
    {
        try {
            $success = (bool) parent::add($autoDate, $nullValues);
        } catch (PrestaShopException $e) {
            $success = false;
        }

        $success &= MyParcelOrderHistory::log($this->id_shipment, $this->postnl_status, $this->date_upd);

        return $success;
    }

    /**
     * Signal that this label has been printed
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public function printed()
    {
        try {
            return Db::getInstance()->update(
                bqSQL(static::$definition['table']),
                array(
                    'postnl_status' => 2, // Registered
                ),
                '`id_shipment` = '.(int) $this->id_shipment.' AND `postnl_status` = 1'
            );
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * Delete a shipment by ID
     *
     * @param int $idShipment
     *
     * @return bool
     */
    public static function deleteShipment($idShipment)
    {
        try {
            return Db::getInstance()->delete(
                bqSQL(static::$definition['table']),
                '`id_shipment` = '.(int) $idShipment
            );
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Update the tracking number of an order.
     *
     * @param int|Order $idOrder    Order ID
     * @param string    $tracktrace Track and trace code
     *
     * @return string Error message
     */
    public static function updateOrderTrackingNumber($idOrder, $tracktrace)
    {
        /* Update shipping number */
        if (!$idOrder instanceof Order) {
            $order = new Order($idOrder);
        } else {
            $order = $idOrder;
        }
        if (!Validate::isLoadedObject($order)) {
            return false;
        }
        try {
            $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return false;
        }
        if (!Validate::isTrackingNumber($tracktrace)) {
            return false;
        } else {
            // Retrocompatibility
            $order->shipping_number = $tracktrace;
            $order->update();

            if (Validate::isLoadedObject($orderCarrier)) {
                // Update order_carrier
                $orderCarrier->tracking_number = pSQL($tracktrace);

                try {
                    return $orderCarrier->update();
                } catch (PrestaShopException $e) {
                    return false;
                }
            }
        }

        return false;
    }
}

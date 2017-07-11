<?php
/**
 * 2017 DM Productions B.V.
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
 * @author     DM Productions B.V. <info@dmp.nl>
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2017 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_') && !defined('_TB_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/autoload.php';

/**
 * Class MyParcelOrderHistory
 */
class MyParcelOrderHistory extends MyParcelObjectModel
{
    // @codingStandardsIgnoreStart
    /** @var int $id_shipment MyParcel consignment ID */
    public $id_shipment;
    /** @var string $postnl_status */
    public $postnl_status;
    /** @var string $date_upd */
    public $date_upd;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_order_history',
        'primary' => 'id_myparcel_order_history',
        'fields'  => array(
            'id_shipment'   => array('type' => self::TYPE_STRING, 'validate' => 'isInt',    'required' => true,                    'db_type' => 'BIGINT(20)'),
            'postnl_status' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'default' => '1', 'db_type' => 'VARCHAR(255)'),
            'date_upd'      => array('type' => self::TYPE_DATE,   'validate' => 'isDate',   'required' => true,                    'db_type' => 'DATETIME'),
        ),
    );

    /**
     * Log a status update
     *
     * @param int    $idShipment   MyParcel shipment ID
     * @param int    $postnlStatus PostNL status
     * @param string $date         Date
     *
     * @return bool Indicates whether the update was successfully logged
     */
    public static function log($idShipment, $postnlStatus, $date)
    {
        return (bool) Db::getInstance()->insert(
            bqSQL(self::$definition['table']),
            array(
                'id_shipment' => (int) $idShipment,
                'postnl_status' => (int) $postnlStatus,
                'date_upd' => date('Y-m-d H:i:s', strtotime($date)),
            )
        );
    }

    /**
     * Get shipment history by Order ID
     *
     * @param int $idOrder Order ID
     *
     * @return array Shipment history
     */
    public static function getShipmentHistoryByOrderId($idOrder)
    {
        $sql = new DbQuery();
        $sql->select('moh.`id_shipment`, moh.`postnl_status`, moh.`date_upd`, mo.`tracktrace`, mo.`shipment`, mo.`postcode`');
        $sql->from('myparcel_order', 'mo');
        $sql->innerJoin(bqSQL(self::$definition['table']), 'moh', 'mo.`id_shipment` = moh.`id_shipment`');
        $sql->where('mo.`id_order` = '.(int) $idOrder);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if ($results && is_array($results)) {
            return self::sortByShipmentId($results);
        }

        return array();
    }

    /**
     * Sort results from getShipmentHistoryByOrderId
     *
     * @param array $results
     *
     * @return array Sorted results
     */
    protected static function sortByShipmentId($results)
    {
        $shipments = array();

        foreach ($results as $result) {
            if (!array_key_exists($result['id_shipment'], $shipments)) {
                $shipments[$result['id_shipment']] = array(
                    'shipment' => Tools::jsonDecode($result['shipment'], true),
                    'tracktrace' => $result['tracktrace'],
                    'postcode' => $result['postcode'],
                    'history' => array(),
                );
            }
            $shipments[$result['id_shipment']]['history'][] = array(
                'postnl_status' => $result['postnl_status'],
                'date_upd' => $result['date_upd'],
            );
        }

        return $shipments;
    }
}

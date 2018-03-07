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
 * Class MyParcelOrderHistory
 */
class MyParcelOrderHistory extends MyParcelObjectModel
{
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_order_history',
        'primary' => 'id_myparcel_order_history',
        'fields'  => array(
            'id_shipment'   => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isInt',
                'required' => true,
                'db_type' => 'BIGINT(20)'
            ),
            'postnl_status' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'default' => '1',
                'db_type' => 'VARCHAR(255)'
            ),
            'date_upd'      => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
                'db_type' => 'DATETIME'
            ),
        ),
    );
    /** @var int $id_shipment MyParcel consignment ID */
    public $id_shipment;
    /** @var string $postnl_status */
    public $postnl_status;
    /** @var string $date_upd */
    public $date_upd;
    // @codingStandardsIgnoreEnd

    /**
     * Log a status update
     *
     * @param int         $idShipment   MyParcel shipment ID
     * @param int         $postnlStatus PostNL status
     * @param string|null $date         Date
     *
     * @return bool Indicates whether the update was successfully logged
     */
    public static function log($idShipment, $postnlStatus, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }

        try {
            return (bool) Db::getInstance()->insert(
                bqSQL(static::$definition['table']),
                array(
                    'id_shipment'   => (int) $idShipment,
                    'postnl_status' => (int) $postnlStatus,
                    'date_upd'      => pSQL($date),
                )
            );
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * Get shipment history by Shipment ID
     *
     * @param int $idShipment Shipment ID
     *
     * @return array Shipment history
     */
    public static function getShipmentHistoryByShipmentId($idShipment)
    {
        $sql = new DbQuery();
        $sql->select('moh.`id_shipment`, moh.`postnl_status`');
        $sql->select('moh.`date_upd`, mo.`tracktrace`, mo.`shipment`, mo.`postcode`');
        $sql->from('myparcel_order', 'mo');
        $sql->innerJoin(bqSQL(static::$definition['table']), 'moh', 'mo.`id_shipment` = moh.`id_shipment`');
        $sql->where('mo.`id_shipment` = '.(int) $idShipment);

        try {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $results = array();
        }

        if ($results && is_array($results)) {
            return $results;
        }

        return array();
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
        $sql->select('moh.`id_shipment`, moh.`postnl_status`');
        $sql->select('moh.`date_upd`, mo.`tracktrace`, mo.`shipment`, mo.`postcode`');
        $sql->from('myparcel_order', 'mo');
        $sql->innerJoin(bqSQL(static::$definition['table']), 'moh', 'mo.`id_shipment` = moh.`id_shipment`');
        $sql->where('mo.`id_order` = '.(int) $idOrder);

        try {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $results = array();
        }

        if ($results && is_array($results)) {
            return static::sortByShipmentId($results);
        }

        return array();
    }

    /**
     * Set printed status
     *
     * @param int $idShipment
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     */
    public static function setPrinted($idShipment)
    {
        try {
            $targetOrderState = (int) Configuration::get(MyParcel::PRINTED_STATUS);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
        if (!$targetOrderState) {
            return;
        }

        if (Configuration::get(MyParcel::NOTIFICATION_MOMENT)) {
            static::sendShippedNotification($idShipment);
        }

        try {
            static::setOrderStatus($idShipment, $targetOrderState, !Configuration::get(MyParcel::NOTIFICATIONS));
        } catch (Exception $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
    }

    /**
     * Set shipped status
     *
     * @param int $idShipment
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     */
    public static function setShipped($idShipment)
    {
        try {
            $targetOrderState = (int) Configuration::get(MyParcel::SHIPPED_STATUS);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
        if (!$targetOrderState) {
            return;
        }

        if (!Configuration::get(MyParcel::NOTIFICATION_MOMENT)) {
            static::sendShippedNotification($idShipment);
        }

        try {
            static::setOrderStatus($idShipment, $targetOrderState, !Configuration::get(MyParcel::NOTIFICATIONS));
        } catch (Exception $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
    }

    /**
     * @param int $idShipment
     *
     * @throws Adapter_Exception
     */
    public static function sendShippedNotification($idShipment)
    {
        try {
            if (Configuration::get(MyParcel::NOTIFICATIONS)) {
                $order = MyParcelOrder::getOrderByShipmentId($idShipment);
                if (!Validate::isLoadedObject($order)) {
                    return;
                }
                $myParcelOrder = MyParcelOrder::getByShipmentId($idShipment);
                if (!Validate::isLoadedObject($myParcelOrder)) {
                    return;
                }
                $shipmentHistory = MyParcelOrderHistory::getShipmentHistoryByShipmentId($idShipment);
                $previousStates = array_map(function ($item) {
                    return (int) $item['postnl_status'];
                }, $shipmentHistory);

                if ((!Configuration::get(MyParcel::NOTIFICATION_MOMENT) && max($previousStates) >= 2
                    || Configuration::get(MyParcel::NOTIFICATION_MOMENT) && max($previousStates) >= 3)
                ) {
                    return;
                }

                $customer = new Customer($order->id_customer);
                $address = new Address($order->id_address_delivery);

                $mailIso = \Language::getIsoById($order->id_lang);
                $mailIsoUpper = Tools::strtoupper($mailIso);
                $countryIso = Tools::strtoupper(Country::getIsoById($address->id_country));

                $templateVars = array(
                    '{firstname}'       => $address->firstname,
                    '{lastname}'        => $address->lastname,
                    '{shipping_number}' => $order->shipping_number,
                    '{followup}'        => "http://postnl.nl/tracktrace/?L={$mailIsoUpper}&B={$order->shipping_number}".
                        "&P={$address->postcode}&D={$countryIso}&T=C",
                );

                $mailType = 'standard';
                $deliveryOption = json_decode($myParcelOrder->myparcel_delivery_option, true);
                if (isset($deliveryOption['options']['package_type'])
                    && $deliveryOption['options']['package_type'] == 2
                ) {
                    $mailType = 'mailboxpackage';
                }


                $dirMail = false;
                if (file_exists(_PS_THEME_DIR_."modules/myparcel/mails/$mailIso/myparcel_{$mailType}_shipped.txt")
                    && file_exists(
                        _PS_THEME_DIR_."modules/myparcel/mails/$mailIso/myparcel_{$mailType}_shipped.html"
                    )
                ) {
                    $dirMail = _PS_THEME_DIR_."modules/myparcel/mails/";
                } elseif (file_exists(dirname(__FILE__)."/../mails/$mailIso/myparcel_{$mailType}_shipped.txt")
                    && file_exists(dirname(__FILE__)."/../mails/$mailIso/myparcel_{$mailType}_shipped.html")
                ) {
                    $dirMail = dirname(__FILE__).'/../mails/';
                }

                if ($dirMail) {
                    Mail::Send(
                        $order->id_lang,
                        "myparcel_{$mailType}_shipped",
                        Mail::l('Your order is on its way', $order->id_lang),
                        $templateVars,
                        (string) $customer->email,
                        null,
                        (string) Configuration::get(
                            'PS_SHOP_EMAIL',
                            null,
                            null,
                            Context::getContext()->shop->id
                        ),
                        (string) Configuration::get(
                            'PS_SHOP_NAME',
                            null,
                            null,
                            Context::getContext()->shop->id
                        ),
                        null,
                        null,
                        $dirMail,
                        false,
                        Context::getContext()->shop->id
                    );
                }
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
    }

    /**
     * Set received status
     *
     * @param int $idShipment
     */
    public static function setReceived($idShipment)
    {
        try {
            $targetOrderState = (int) Configuration::get(MyParcel::RECEIVED_STATUS);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
        if (!$targetOrderState) {
            return;
        }

        try {
            static::setOrderStatus($idShipment, $targetOrderState);
        } catch (Exception $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return;
        }
    }

    /**
     * @param int  $idShipment Shipment ID
     * @param int  $status     Target order state
     * @param bool $addWithEmail
     *
     * @return void
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public static function setOrderStatus($idShipment, $status, $addWithEmail = true)
    {
        $targetOrderState = (int) $status;
        if (!$targetOrderState) {
            return;
        }

        $order = MyParcelOrder::getOrderByShipmentId($idShipment);
        if (Validate::isLoadedObject($order)) {
            $history = $order->getHistory(Context::getContext()->language->id);
            $found = false;
            foreach ($history as $item) {
                if ((int) $item['id_order_state'] === $targetOrderState) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $history = new OrderHistory();
                $history->id_order = (int) $order->id;
                $history->changeIdOrderState($targetOrderState, (int) $order->id, !$order->hasInvoice());
                $history->addWithemail($addWithEmail);
            }
        }
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
                    'shipment'   => json_decode($result['shipment'], true),
                    'tracktrace' => $result['tracktrace'],
                    'postcode'   => $result['postcode'],
                    'history'    => array(),
                );
            }
            $shipments[$result['id_shipment']]['history'][] = array(
                'postnl_status' => $result['postnl_status'],
                'date_upd'      => $result['date_upd'],
            );
        }

        return $shipments;
    }
}

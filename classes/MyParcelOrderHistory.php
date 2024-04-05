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

use MyParcelModule\Firstred\Dot;
use MyParcelModule\MyParcelHttpClient;
use MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository;

if (!defined('_PS_VERSION_')) {
    return;
}

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
                'type'     => self::TYPE_STRING,
                'validate' => 'isInt',
                'required' => true,
                'db_type'  => 'BIGINT(20)',
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
     *
     * @throws PrestaShopDatabaseException
     *
     * @since 2.0.0
     */
    public static function log($idShipment, $postnlStatus, $date = null)
    {
        if (!$date) {
            $date = date('Y-m-d H:i:s');
        }

        return (bool) Db::getInstance()->insert(
            bqSQL(static::$definition['table']),
            array(
                'id_shipment'   => (int) $idShipment,
                'postnl_status' => (int) $postnlStatus,
                'date_upd'      => pSQL($date),
            )
        );
    }

    /**
     * Get shipment history by Shipment ID
     *
     * @param int $idShipment Shipment ID
     *
     * @return array Shipment history
     *
     * @throws PrestaShopDatabaseException
     *
     * @since 2.1.0
     */
    public static function getShipmentHistoryByShipmentId($idShipment)
    {
        $sql = new DbQuery();
        $sql->select('moh.`id_shipment`, moh.`postnl_status`');
        $sql->select('moh.`date_upd`, mo.`tracktrace`, mo.`shipment`, mo.`postcode`');
        $sql->from(bqSQL(MyParcelOrder::$definition['table']), 'mo');
        $sql->innerJoin(bqSQL(static::$definition['table']), 'moh', 'mo.`id_shipment` = moh.`id_shipment`');
        $sql->where('mo.`id_shipment` = '.(int) $idShipment);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($results)) {
            return array();
        }

        return $results;
    }

    /**
     * Set printed status
     *
     * @param int  $idShipment Shipment ID
     * @param bool $mail       Send mail
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @throws ErrorException
     *
     * @since 2.1.0
     * @since 2.3.0 Mail override option
     */
    public static function setPrinted($idShipment, $mail = true)
    {
        $targetOrderState = (int) Configuration::get(MyParcel::PRINTED_STATUS);
        if ($mail && Configuration::get(MyParcel::NOTIFICATION_MOMENT)) {
            static::sendShippedNotification($idShipment);
        }

        if (!$targetOrderState) {
            return;
        }

        static::setOrderStatus($idShipment, $targetOrderState);
    }

    /**
     * Get shipment history by Order ID
     *
     * @param int $idOrder Order ID
     *
     * @return array Shipment history
     *
     * @throws PrestaShopDatabaseException
     *
     * @since 2.1.0
     */
    public static function getShipmentHistoryByOrderId($idOrder)
    {
        $sql = new DbQuery();
        $sql->select('moh.`id_shipment`, moh.`postnl_status`');
        $sql->select('moh.`date_upd`, mo.`tracktrace`, mo.`shipment`, mo.`postcode`');
        $sql->from('myparcel_order', 'mo');
        $sql->innerJoin(bqSQL(static::$definition['table']), 'moh', 'mo.`id_shipment` = moh.`id_shipment`');
        $sql->where('mo.`id_order` = '.(int) $idOrder);

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($results)) {
            return array();
        }

        return static::sortByShipmentId($results);
    }

    /**
     * Set shipped status
     *
     * @param int  $idShipment
     * @param bool $mail
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @throws ErrorException
     *
     * @since 2.1.0
     * @since 2.3.0 Mail override option
     */
    public static function setShipped($idShipment, $mail = true)
    {
        $targetOrderState = (int) Configuration::get(MyParcel::SHIPPED_STATUS);
        if (!Configuration::get(MyParcel::NOTIFICATION_MOMENT) && $mail) {
            static::sendShippedNotification($idShipment);
        }

        if (!$targetOrderState) {
            return;
        }

        static::setOrderStatus($idShipment, $targetOrderState);
    }

    /**
     * @param string $idShipment
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ErrorException
     *
     * @since 2.1.0
     */
    public static function sendShippedNotification($idShipment)
    {
        if (!Configuration::get(MyParcel::NOTIFICATIONS)) {
            return;
        }
        $order = MyParcelOrder::getOrderByShipmentId($idShipment);
        if (!Validate::isLoadedObject($order)) {
            return;
        }
        $myParcelOrder = MyParcelOrder::getByShipmentId($idShipment);
        if (!Validate::isLoadedObject($myParcelOrder)) {
            return;
        }
        $shipmentHistory = MyParcelOrderHistory::getShipmentHistoryByShipmentId($idShipment);
        $previousStates = array_pad(array_column($shipmentHistory, 'postnl_status'), 1, 0);

        if ((Configuration::get(MyParcel::NOTIFICATION_MOMENT) && max($previousStates) >= 2
            || !Configuration::get(MyParcel::NOTIFICATION_MOMENT) && max($previousStates) >= 3)
        ) {
            return;
        }

        $customer = new Customer($order->id_customer);
        if (!Validate::isEmail($customer->email)) {
            return;
        }
        $address = new Address($order->id_address_delivery);
        $shipment = mypa_dot(@json_decode($myParcelOrder->shipment, true));
        if (!($shipment->isEmpty('recipient.postal_code'))) {
            $postcode = strtoupper(str_replace(' ', '', $shipment->get('recipient.postal_code')));
        } else {
            $postcode = strtoupper(str_replace(' ', '', $address->postcode));
        }
        $deliveryRequest = mypa_dot(MyParcelDeliveryOption::getByOrderId($order->id));

        $mailIso = Language::getIsoById($order->id_lang);
        $mailIsoUpper = strtoupper($mailIso);
        $countryIso = strtoupper(Country::getIsoById($address->id_country));
        $templateVars = array(
            '{firstname}'           => $address->firstname,
            '{lastname}'            => $address->lastname,
            '{shipping_number}'     => $order->shipping_number,
            '{followup}'            => "http://postnl.nl/tracktrace/?L={$mailIsoUpper}&B={$order->shipping_number}&P={$postcode}&D={$countryIso}&T=C",
            '{order_name}'          => $order->getUniqReference(),
            '{order_id}'            => $order->id,
            '{utc_offset}'          => date('P'),
        );
        // Assume PHP localization is not available
        $nlDays = array(
            1 => 'maandag',
            2 => 'dinsdag',
            3 => 'woensdag',
            4 => 'donderdag',
            5 => 'vrijdag',
            6 => 'zaterdag',
            0 => 'zondag',
        );
        $nlMonths = array(
            1  => 'januari',
            2  => 'februari',
            3  => 'maart',
            4  => 'april',
            5  => 'mei',
            6  => 'juni',
            7  => 'juli',
            8  => 'augustus',
            9  => 'september',
            10 => 'oktober',
            11 => 'november',
            12 => 'december',
        );
        $tracktraceInfo = mypa_dot(MyParcelOrder::getTrackTraceOnline($idShipment));
        $deliveryDateFrom = $tracktraceInfo->get('data.tracktraces.0.delivery_moment.start.date');
        $deliveryDateTo = $tracktraceInfo->get('data.tracktraces.0.delivery_moment.end.date');
        $dayNumber = (int) date('w', strtotime($deliveryDateFrom));
        $monthNumber = (int) date('n', strtotime($deliveryDateFrom));
        $templateVars['{delivery_street}'] = $shipment->get('recipient.street');
        $templateVars['{delivery_number}'] = "{$shipment->get('recipient.number')}{$shipment->get('recipient.number_suffix')}";
        $templateVars['{delivery_postcode}'] = $shipment->get('recipient.postal_code');
        $templateVars['{delivery_city}'] = $shipment->get('recipient.city');
        $templateVars['{delivery_region}'] = $shipment->get('recipient.region') ?: '-';
        $templateVars['{delivery_cc}'] = $shipment->get('recipient.cc');
        $templateVars['{pickup_name}'] = $shipment->get('pickup.location_name');
        $templateVars['{pickup_street}'] = $shipment->get('pickup.street');
        $templateVars['{pickup_number}'] = $shipment->get('pickup.number');
        $templateVars['{pickup_postcode}'] = strtoupper(str_replace(' ', '', $shipment->get('pickup.postal_code')));
        $templateVars['{pickup_region}'] = $shipment->get('pickup.region') ?: '-';
        $templateVars['{pickup_city}'] = $shipment->get('pickup.city');
        $templateVars['{pickup_cc}'] = $shipment->get('recipient.cc');
        $templateVars['{pickup_phone}'] = $deliveryRequest->get('data.phone_number');
        if (static::trackTraceHasEnoughInfo($tracktraceInfo)) {
            if ($shipment->isEmpty('options.delivery_type') || in_array($shipment->get('options.delivery_type'), array(1, 2, 3))) {
                if ($mailIsoUpper === 'NL') {
                    $templateVars['{delivery_day_name}'] = $nlDays[$dayNumber];
                    $templateVars['{delivery_day}'] = date('j', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day_leading_zero}'] = date('d', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month}'] = date('n', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_leading_zero}'] = date('m', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_name}'] = $nlMonths[$monthNumber];
                    $templateVars['{delivery_year}'] = date('Y', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from}'] = date('H:i', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from_localized}'] = date('H:i', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_to}'] = date('H:i', strtotime($deliveryDateTo));
                    $templateVars['{delivery_time_to_localized}'] = date('H:i', strtotime($deliveryDateTo));
                } else {
                    $templateVars['{delivery_day_name}'] = date('l', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day}'] = date('j', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day_leading_zero}'] = date('d', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month}'] = date('n', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_leading_zero}'] = date('m', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_name}'] = date('F', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_year}'] = date('Y', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from}'] = date('H:i', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from_localized}'] = date('h:i A', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_to}'] = date('H:i', strtotime($deliveryDateTo));
                    $templateVars['{delivery_time_to_localized}'] = date('h:i A', strtotime($deliveryDateTo));
                }
            } elseif (in_array($shipment->get('options.delivery_type'), array(4, 5))) {
                if (!$deliveryRequest->isEmpty('data.latitude') && !$deliveryRequest->isEmpty('data.longitude')) {
                    $googleMapsDestinationLocation = implode(
                        ',',
                        array(
                            $deliveryRequest->get('data.latitude'),
                            $deliveryRequest->get('data.longitude'),
                        )
                    );
                } else {
                    $googleMapsDestinationLocation = implode(
                        ',',
                        array(
                            str_replace(' ', '+', $shipment->get('pickup.street')).' '.str_replace(' ', '+', $shipment->get('pickup.number').$shipment->get('pickup.number_suffix')),
                            str_replace(' ', '+', $shipment->get('pickup.city')),
                            strtoupper($shipment->get('recipient.cc')),
                        )
                    );
                }
                $googleMapsOriginLocation = implode(
                    ',',
                    array(
                        str_replace(' ', '+', $shipment->get('recipient.street')).' '.str_replace(' ', '+', $shipment->get('recipient.number').$shipment->get('recipient.number_suffix')),
                        str_replace(' ', '+', $shipment->get('recipient.city')),
                        strtoupper($shipment->get('recipient.cc')),
                    )
                );
                $templateVars['{google_maps_origin_location}'] = $googleMapsOriginLocation;
                $templateVars['{google_maps_destination_location}'] = $googleMapsDestinationLocation;

                $googleMapsKey = (Configuration::get('PS_API_KEY') ?: Configuration::get('TB_GOOGLE_MAPS_API_KEY'));
                $image = '';
                if ($googleMapsKey) {
                    $curl = new MyParcelHttpClient();
                    $directionsPath = mypa_dot($curl->get("https://maps.googleapis.com/maps/api/directions/json?origin=${googleMapsOriginLocation}&destination={$googleMapsDestinationLocation}&mode=driving&key={$googleMapsKey}"));
                    if ($directionsPath = $directionsPath->get('routes.0.overview_polyline.points')) {
                        $markerImage = rtrim(Tools::getHttpHost(true), '/').__PS_BASE_URI__.ltrim(MyParcel::getMediaPath(_PS_MODULE_DIR_.'myparcel/views/img/LocationPin_PostNL.png'), '/');
                        $image = "https://maps.googleapis.com/maps/api/staticmap?size=600x300&key={$googleMapsKey}&maptype=roadmap&format=png&markers=icon:{$markerImage}%7Clabel:%7C{$googleMapsDestinationLocation}&markers={$googleMapsOriginLocation}&path=color:0xed8c0080|enc%3A{$directionsPath}";
                    }
                }

                if ($mailIsoUpper === 'NL') {
                    $dayNumber = (int) date('w', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day_name}'] = $nlDays[$dayNumber];
                    $templateVars['{delivery_day}'] = date('j', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day_leading_zero}'] = date('d', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month}'] = date('n', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_leading_zero}'] = date('m', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_name}'] = $nlMonths[$monthNumber];
                    $templateVars['{delivery_year}'] = date('Y', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from}'] = '15:00';
                    $templateVars['{delivery_time_from_localized}'] = '15:00';
                } else {
                    $templateVars['{delivery_day_name}'] = date('l', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day}'] = date('d', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_day_leading_zero}'] = date('d', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month}'] = date('m', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_leading_zero}'] = date('m', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_month_name}'] = date('F', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_year}'] = date('Y', strtotime($deliveryDateFrom));
                    $templateVars['{delivery_time_from}'] = '15:00';
                    $templateVars['{delivery_time_from_localized}'] = '03:00 PM';
                }
                foreach (array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') as $day) {
                    $dayFrom = $deliveryRequest->get("data.opening_hours.{$day}.0");
                    if (strpos($dayFrom, '-') !== false) {
                        list($dayFrom) = explode('-', $dayFrom);
                    }
                    $dayTo = $deliveryRequest->get("data.opening_hours.{$day}.".($deliveryRequest->count("data.opening_hours.{$day}") - 1));
                    if (strpos($dayTo, '-') !== false) {
                        list(, $dayTo) = array_pad(explode('-', $dayTo), 2, '');
                    }
                    if ($dayFrom) {
                        $dayFull = "{$dayFrom} - {$dayTo}";
                    } else {
                        $dayFull = Translate::getModuleTranslation('myparcel', 'Closed', 'myparcel');
                    }
                    $templateVars["{opening_hours_{$day}_from}"] = $dayFrom;
                    $templateVars["{opening_hours_{$day}_to}"] = $dayTo;
                    $templateVars["{opening_hours_{$day}}"] = $dayFull;
                }

                $templateVars['{pickup_img}'] = "<img src='{$image}' alt='Pickup location'>";
                $templateVars['{pickup_img_src}'] = "{$image}";
            }
        } else {
            $unknown = Translate::getModuleTranslation('myparcel', 'unknown', 'myparcel');
            $templateVars['{delivery_day_name}'] = "{{$unknown}}";
            $templateVars['{delivery_day}'] = "{{$unknown}}";
            $templateVars['{delivery_day_leading_zero}'] = "{{$unknown}}";
            $templateVars['{delivery_month}'] = "{{$unknown}}";
            $templateVars['{delivery_month_leading_zero}'] = "{{$unknown}}";
            $templateVars['{delivery_month_name}'] = "{{$unknown}}";
            $templateVars['{delivery_year}'] = "{{$unknown}}";
            $templateVars['{delivery_time_from}'] = "{{$unknown}}";
            $templateVars['{delivery_time_from_localized}'] = "{{$unknown}}";
            $templateVars['{delivery_time_to}'] = "{{$unknown}}";
            $templateVars['{delivery_time_to_localized}'] = "{{$unknown}}";
        }

        $mailType = ($shipment->get('options.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_MAILBOX_PACKAGE)
            ? 'mailboxpackage'
            : (static::trackTraceHasEnoughInfo($tracktraceInfo) ? 'standard' : 'standard_noinfo');
        if ($shipment->get('pickup')) {
            $mailType = static::trackTraceHasEnoughInfo($tracktraceInfo) ? 'pickup' : 'pickup_noinfo';
        }

        $mailDir = false;
        if (file_exists(_PS_THEME_DIR_."modules/myparcel/mails/$mailIso/myparcel_{$mailType}_shipped.txt")
            && file_exists(
                _PS_THEME_DIR_."modules/myparcel/mails/$mailIso/myparcel_{$mailType}_shipped.html"
            )
        ) {
            $mailDir = _PS_THEME_DIR_."modules/myparcel/mails/";
        } elseif (file_exists(dirname(__FILE__)."/../mails/$mailIso/myparcel_{$mailType}_shipped.txt")
            && file_exists(dirname(__FILE__)."/../mails/$mailIso/myparcel_{$mailType}_shipped.html")
        ) {
            $mailDir = dirname(__FILE__).'/../mails/';
        }

        if ($mailDir) {
            Mail::Send(
                $order->id_lang,
                "myparcel_{$mailType}_shipped",
                $mailIsoUpper === 'NL' ? "Bestelling {$order->getUniqReference()} is verzonden" : "Order {$order->getUniqReference()} has been shipped",
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
                $mailDir,
                false,
                Context::getContext()->shop->id
            );
        }
    }

    /**
     * Set received status
     *
     * @param int $idShipment
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.1.0
     */
    public static function setReceived($idShipment)
    {
        $targetOrderState = (int) Configuration::get(MyParcel::RECEIVED_STATUS);
        if (!$targetOrderState) {
            return;
        }

        static::setOrderStatus($idShipment, $targetOrderState);
    }

    /**
     * @param int  $idShipment   Shipment ID
     * @param int  $status       Target order state
     * @param bool $addWithEmail Send email as well
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.1.0
     */
    public static function setOrderStatus($idShipment, $status, $addWithEmail = true)
    {
        $targetOrderState = (int) $status;
        if (!$targetOrderState) {
            return;
        }
        $order = MyParcelOrder::getOrderByShipmentId($idShipment);
        $shipment = MyParcelOrder::getByShipmentId($idShipment);
        if (!Validate::isLoadedObject($order) || !Validate::isLoadedObject($shipment)) {
            return;
        }
        if (in_array($order->getCurrentState(), MyParcel::getIgnoredStatuses())) {
            return;
        }
        $shipment = mypa_dot(@json_decode($shipment->shipment, true));

        $idOrder = (int) $order->id;
        $history = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS("SELECT `id_order_state` FROM "._DB_PREFIX_."order_history WHERE `id_order` = $idOrder");
        if (is_array($history)) {
            $history = array_column($history, 'id_order_state');
            if (in_array($targetOrderState, $history)) {
                return;
            }
        }

        $history = new OrderHistory();
        $history->id_order = (int) $order->id;
        $history->changeIdOrderState($targetOrderState, (int) $order->id, !$order->hasInvoice());
        if ($addWithEmail && !in_array((int) $shipment->get('options.package_type'), array(1, 2))) {
            $history->addWithemail();
        } else {
            $history->add();
        }
    }

    /**
     * Sort results from getShipmentHistoryByOrderId
     *
     * @param array $results
     *
     * @return array Sorted results
     *
     * @since 2.1.0
     */
    protected static function sortByShipmentId($results)
    {
        $shipments = array();

        foreach ($results as $result) {
            if (!array_key_exists($result['id_shipment'], $shipments)) {
                $shipments[$result['id_shipment']] = array(
                    'shipment'   => @json_decode($result['shipment'], true),
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

    /**
     * Check if the track trace object has enough info to send detailed mails
     *
     * @param Dot $trackTrace
     *
     * @return bool
     *
     * @since 2.3.0
     */
    protected static function trackTraceHasEnoughInfo(Dot $trackTrace)
    {
        $deliveryDateFrom = $trackTrace->get('data.tracktraces.0.delivery_moment.start.date');

        return $trackTrace->has('barcode')
            && $deliveryDateFrom
            && date('Y-m-d', strtotime($deliveryDateFrom)) > '2019-01-01'
        ;
    }
}

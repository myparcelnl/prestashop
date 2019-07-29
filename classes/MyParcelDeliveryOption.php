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
use MyParcelModule\MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository;

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelDeliveryOption
 *
 * @since 2.0.0
 */
class MyParcelDeliveryOption extends MyParcelObjectModel
{
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_delivery_option',
        'primary' => 'id_myparcel_delivery_option',
        'fields'  => array(
            'id_cart'                  => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'myparcel_delivery_option' => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => true,
                'db_type'  => 'TEXT',
            ),
            'date_delivery'            => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isDate',
                'required' => false,
                'db_type'  => 'DATETIME',
            ),
            'pickup'                   => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(255)',
            ),
        ),
    );
    /**
     * The Cart ID to which this option belongs
     *
     * @var int $id_cart
     */
    public $id_cart;
    /**
     * Raw JSON of a delivery option
     * from which the other options, that were
     * available at the time, have been stripped.
     *
     * @var string $myparcel_delivery_option
     */
    public $myparcel_delivery_option;
    /**
     * Preferred date of delivery
     *
     * @var string $date_delivery
     */
    public $date_delivery;
    /**
     * Information about the pickup
     * - When this variable is filled
     *   the customer has chosen to pick up at
     *   a PostNL location
     *
     * @var string $pickup
     */
    public $pickup;
    // @codingStandardsIgnoreEnd

    /**
     * Get MyParcelDeliveryOption by Cart ID or Cart object
     *
     * @param int|Cart $cart
     * @param bool     $cache
     *
     * @return false|self
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getByCartId($cart, $cache = true)
    {
        if ($cart instanceof Cart) {
            $idCart = $cart->id;
        } else {
            $idCart = $cart;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getrow($sql, $cache);

        if (empty($result)) {
            return false;
        }

        $option = new static();
        $option->hydrate($result);

        return $option;
    }

    /**
     * Get Delivery Option info by Cart
     *
     * @param int|Cart $cart  Cart ID or object
     * @param bool     $cache Enable DB cache
     *
     * @return false|array Delivery from DB
     *
     * @throws PrestaShopException
     * @since 2.2.0 Returns an associative array instead of a class
     * @since 2.0.0
     */
    public static function getRawByCartId($cart, $cache = true)
    {
        if ($cart instanceof Cart) {
            $idCart = $cart->id;
        } else {
            $idCart = $cart;
        }

        $sql = new DbQuery();
        $sql->select('`'.bqSQL(static::$definition['table']).'`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql, $cache);

        if ($result) {
            return @json_decode($result, true);
        }

        return false;
    }

    /**
     * Save raw delivery option to DB
     *
     * @param string $deliveryOption
     * @param int    $idCart
     *
     * @return bool Indicates whether the save was successfully
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function saveRawDeliveryOption($deliveryOption, $idCart)
    {
        $preferredDeliveryDay = static::getPreferredDeliveryDay(json_decode($deliveryOption, true));
        $preferredPickup = static::getPreferredPickup(json_decode($deliveryOption, true));

        $sql = new DbQuery();
        $sql->select('`id_cart`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return (bool) Db::getInstance()->update(
                bqSQL(static::$definition['table']),
                array(
                    bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode(json_decode($deliveryOption)), true)."'"),
                    'date_delivery'                     => date('Y-m-d H:i:s', strtotime($preferredDeliveryDay)),
                    'pickup'                            => bqSQL($preferredPickup),
                ),
                '`id_cart` = '.(int) $idCart
            );
        } else {
            return (bool) Db::getInstance()->insert(
                bqSQL(static::$definition['table']),
                array(
                    bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode(json_decode($deliveryOption)), true)."'"),
                    'id_cart'                           => (int) $idCart,
                    'date_delivery'                     => date('Y-m-d H:i:s', strtotime($preferredDeliveryDay)),
                    'pickup'                            => bqSQL($preferredPickup),
                )
            );
        }
    }

    /**
     * Remove the delivery option info
     *
     * @param int $idCart
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     *
     * @since 2.0.0
     */
    public static function removeDeliveryOption($idCart)
    {
        return Db::getInstance()->update(
            bqSQL(static::$definition['table']),
            array(
                bqSQL(static::$definition['table']) => null,
            ),
            '`id_cart` = '.(int) $idCart,
            1,
            true
        );
    }

    /**
     * Get by Order ID
     *
     * @param int $id Order ID
     *
     * @return mixed
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     *
     * @since 2.2.0
     */
    public static function getByOrderId($id)
    {
        $values = array_values(array_pad(static::getByOrderIds(array($id)), 1, array()));

        return $values[0];
    }

    /**
     * Get by Order IDs
     *
     * @param array $range Order ID range
     *
     * @return array stdClass with `MyParcelDeliveryoption`s
     *
     * @throws Exception
     *
     * @since 2.0.0
     */
    public static function getByOrderIds($range)
    {
        if (is_int($range)) {
            $range = array($range);
        } elseif (is_string($range)) {
            $range = array((int) $range);
        }
        if (!is_array($range) || empty($range)) {
            return array();
        }

        foreach ($range as &$item) {
            $item = (int) $item;
        }

        $sql = new DbQuery();
        $sql->select('o.`id_order`, mdo.`'.bqSQL(static::$definition['table']).'`, a.*');
        $sql->from(bqSQL(static::$definition['table']), 'mdo');
        $sql->innerJoin('orders', 'o', 'mdo.`id_cart` = o.`id_cart`');
        $sql->innerJoin('address', 'a', 'o.`id_address_delivery` = a.`id_address`');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!is_array($results)) {
            $results = array();
        }

        $deliveryOptions = array();
        foreach ($results as $result) {
            $fromDb = mypa_dot(@json_decode($result[static::$definition['table']], true));
            $idOrder = (int) $result['id_order'];
            $order = new Order($idOrder);
            $cc = Tools::strtoupper($fromDb->get('concept.recipient.cc', $fromDb->get('concept.cc')));
            $address = array(
                'street'        => trim($fromDb->get('concept.recipient.street', $fromDb->get('concept.street', ''))),
                'number'        => trim($fromDb->get('concept.recipient.number', $fromDb->get('concept.number', ''))),
                'number_suffix' => trim($fromDb->get('concept.recipient.number_suffix', $fromDb->get('concept.number_suffix', ''))),
            );
            if ($cc === 'NL' && !is_numeric($address['number'])) {
                preg_match(MyParcelConsignmentRepository::SPLIT_STREET_REGEX, "{$address['street']} {$address['number']} {$address['number_suffix']}", $matches);
                $address = array(
                    'street'        => isset($matches['street']) ? $matches['street'] : '',
                    'number'        => isset($matches['number']) ? $matches['number'] : '',
                    'number_suffix' => isset($matches['number_suffix']) ? $matches['number_suffix'] : '',
                );
            } elseif ($cc !== 'NL' && ($address['number'] || $address['number_suffix'])) {
                $address = array(
                    'street'        => "{$address['street']} {$address['number']} {$address['number_suffix']}",
                    'number'        => '',
                    'number_suffix' => '',
                );
            }
            static::checkSpecialDeliveryOption($fromDb);
            $formattedInvoiceNumber = '';
            if (Validate::isLoadedObject($order)) {
                $orderInvoice = $order->getInvoicesCollection();
                /** @var OrderInvoice $orderInvoice */
                $orderInvoice = $orderInvoice->getFirst();
                if (Validate::isLoadedObject($orderInvoice)) {
                    $formattedInvoiceNumber = $orderInvoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
                }
            }

            $deliveryOption = array(
                'idOrder'        => (int) $idOrder,
                'editingAddress' => false,
                'editingCustoms' => false,
                'concept'        => array(
                    'cc'                     => (string) $cc,
                    'person'                 => (string) $fromDb->get('concept.recipient.person', $fromDb->get('concept.person', '')),
                    'company'                => (string) $fromDb->get('concept.recipient.company', $fromDb->get('concept.company', '')),
                    'street'                 => (string) $address['street'],
                    'street_additional_info' => (string) $fromDb->get('concept.recipient.street_additional_info', $fromDb->get('concept.street_additional_info', '')),
                    'number'                 => (string) $address['number'],
                    'number_suffix'          => (string) $address['number_suffix'],
                    'postal_code'            => (string) $fromDb->get('concept.recipient.postal_code', $fromDb->get('concept.postal_code', '')),
                    'city'                   => (string) $fromDb->get('concept.recipient.city', $fromDb->get('concept.city', '')),
                    'region'                 => (string) $fromDb->get('concept.recipient.region', $fromDb->get('concept.region', '')),
                    'email'                  => (string) $fromDb->get('concept.recipient.email', $fromDb->get('concept.email', '')),
                    'phone'                  => (string) $fromDb->get('concept.recipient.phone', $fromDb->get('concept.phone', '')),
                    'delivery_type'          => (int) $fromDb->get('concept.options.delivery_type', $fromDb->get('concept.delivery_type', MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE)),
                    'package_type'           => (int) $fromDb->get('concept.options.package_type', $fromDb->get('concept.package_type', MyParcelConsignmentRepository::DEFAULT_PACKAGE_TYPE)),
                    'only_recipient'         => (boolean) $fromDb->get('concept.options.only_recipient', $fromDb->get('concept.only_recipient', false)),
                    'large_format'           => (boolean) $fromDb->get('concept.options.large_format', $fromDb->get('concept.large_format', false)),
                    'signature'              => (boolean) $fromDb->get('concept.options.signature', $fromDb->get('concept.signature', 0)),
                    'return'                 => (boolean) $fromDb->get('concept.options.return', $fromDb->get('concept.return', false)),
                    'insurance'              => (int) MyParcel::findValidInsuranceAmount($fromDb->get('concept.options.insurance.amount', $fromDb->get('concept.insurance', 0) / 100)) * 100,
                    'cooled_delivery'        => (boolean) $fromDb->get('concept.cooled_delivery', false),
                    'age_check'              => (boolean) $fromDb->get('concept.age_check', false),
                    'delivery_date'          => (string) $fromDb->get('concept.options_delivery_date', $fromDb->get('concept.delivery_date')),
                    'number_of_labels'       => (int) $fromDb->get('concept.number_of_labels', 1),
                    'label_description'      => (string) $fromDb->get('concept.options.label_description', $fromDb->get('concept.label_description', '')),
                    'carrier'                => 1,
                    'weight'                 => (int) $fromDb->get('concept.weight', 1000),
                    'customs'                => !$fromDb->isEmpty('concept.customs.items')
                        ? $fromDb->get('concept.customs')
                        : array(
                        'contents' => 1,
                        'invoice'  => $formattedInvoiceNumber,
                        'weight'   => MyParcelProductSetting::getTotalWeight($fromDb->get('idOrder')),
                        'items'    => MyParcelProductSetting::getCustomsLines($fromDb->get('idOrder')),
                    ),
                ),
            );
            if ($fromDb->has('data')) {
                $deliveryOption['data'] = $fromDb->get('data');
                if ((int) $deliveryOption['concept']['delivery_type'] !== MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE) {
                    $deliveryOption['concept']['delivery_type'] = (int) $fromDb->get('data.time.0.type');
                }
                $deliveryOption['concept']['delivery_date'] = $fromDb->get('data.date');
            }
            if ($fromDb->has('previous')) {
                $deliveryOption['previous'] = $fromDb->get('previous');
            }
            $deliveryOptions[(int) $idOrder] = $deliveryOption;
            if (($key = array_search((int) $idOrder, $range)) !== false) {
                unset($range[$key]);
            }
        }

        if (!empty($range)) {
            foreach(static::createConceptsByOrderIds(array_values($range)) as $newConcept) {
                $deliveryOptions[(int) $newConcept['idOrder']] = $newConcept;
            }
        }

        return $deliveryOptions;
    }

    /**
     * @param Order        $order
     * @param Dot|null     $deliveryOption
     * @param Address|null $address
     * @param bool|null    $mailboxPackage
     * @param bool|null    $digitalStamp
     *
     * @return null|array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.0.0
     * @since 2.2.0 Requires a mypa_dot instance instead of a json_encoded delivery option
     * @since 2.3.0 Added digital stamp option
     */
    public static function createConcept($order, $deliveryOption = null, $address = null, $mailboxPackage = null, $digitalStamp = null)
    {
        if (!$deliveryOption instanceof Dot) {
            $deliveryOption = new Dot();
        }

        if (!$address) {
            $address = new Address($order->id_address_delivery);
        }
        $carrier = new Carrier($order->id_carrier);
        $carrierSetting = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        $sql = new DbQuery();
        $sql->select('`id_cart`');
        $sql->from(bqSQL(Order::$definition['table']));
        $sql->where('`id_order` = '.(int) $order->id);
        $cart = new Cart((int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql));
        $countryIso = Tools::strtoupper(Country::getIsoById($address->id_country));
        $configuration = array(
            MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE      => in_array($countryIso, array_map(function ($country) { return $country['iso_code']; }, array_filter(MyParcel::getCountries(), function ($country) { return in_array($country['region'], array('NL', 'BE', 'EU')); }))) && in_array($countryIso, MyParcel::getLargeFormatCountries()) ? Configuration::get(MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE) : false,
            MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE        => MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL,
            MyParcel::DEFAULT_CONCEPT_AGE_CHECK          => false,
            MyParcel::DEFAULT_CONCEPT_COOLED_DELIVERY    => false,
            MyParcel::DEFAULT_CONCEPT_RETURN             => false,
            MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY => false,
            MyParcel::DEFAULT_CONCEPT_SIGNED             => false,
            MyParcel::DEFAULT_CONCEPT_INSURED            => false,
            MyParcel::DEFAULT_CONCEPT_INSURED_TYPE       => false,
            MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT     => $countryIso === 'NL' ? (int) Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT) : 0,
            MyParcel::LINK_EMAIL                         => Configuration::get(MyParcel::LINK_EMAIL),
            MyParcel::LINK_PHONE                         => Configuration::get(MyParcel::LINK_PHONE),
        );

        if ($countryIso === 'NL') {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = Configuration::get(MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE);
            $configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK] = MyParcelProductSetting::cartHasAgeCheck($cart);
            $configuration[MyParcel::DEFAULT_CONCEPT_RETURN] = Configuration::get(MyParcel::DEFAULT_CONCEPT_RETURN);
            $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = Configuration::get(MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY);
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = Configuration::get(MyParcel::DEFAULT_CONCEPT_SIGNED);
            $configuration[MyParcel::DEFAULT_CONCEPT_INSURED] = Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED);
            $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_TYPE] = Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED_TYPE);
            if ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED]) {
                $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = false;
            }
            if ($configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK]
                && !$configuration[MyParcel::DEFAULT_CONCEPT_COOLED_DELIVERY]
                && !in_array($deliveryOption->get('data.time.0.type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))
            ) {
                $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = true;
                $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = true;
            } else {
                $configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK] = false;
            }
            if (in_array($deliveryOption->get('data.time.0.type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL, MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL_EXPRESS))) {
                $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = true;
                $configuration[MyParcel::DEFAULT_CONCEPT_RETURN] = false;
            } elseif (in_array($deliveryOption->get('data.time.0.type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))) {
                $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = true;
                $configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK] = false;
                if (MyParcelProductSetting::cartHasCooledDelivery($cart)) {
                    $configuration[MyParcel::DEFAULT_CONCEPT_COOLED_DELIVERY] = true;
                }
            }

            if (is_null($mailboxPackage)) {
                if ($deliveryOption instanceof Dot) {
                    $mailboxPackage = $deliveryOption->get('concept.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_MAILBOX_PACKAGE;
                } else {
                    $mailboxPackage = static::checkMailboxPackage(new Cart($order->id_cart));
                }
            }
            if (is_null($digitalStamp)) {
                if ($deliveryOption instanceof Dot) {
                    $digitalStamp = $deliveryOption->get('concept.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_DIGITAL_STAMP;
                } else {
                    $digitalStamp = static::checkDigitalStamp(new Cart($order->id_cart));
                }
            }
            if ($mailboxPackage || $digitalStamp) {
                $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = $mailboxPackage
                    ? MyParcelConsignmentRepository::PACKAGE_TYPE_MAILBOX_PACKAGE
                    : MyParcelConsignmentRepository::PACKAGE_TYPE_DIGITAL_STAMP;
                $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_RETURN] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_INSURED] = false;
                $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT] = 0;
            }

            $matches = MyParcelTools::getParsedAddress($address);
            $street = isset($matches['street']) ? $matches['street'] : '';
            $houseNumber = isset($matches['number']) ? $matches['number'] : '';
            $houseNumberSuffix = isset($matches['number_suffix']) ? $matches['number_suffix'] : '';
        } elseif ($countryIso === 'BE') {
            $matches = MyParcelTools::getParsedAddress($address);
            $boxSeparator = MyParcelTools::isWallonia($address->postcode) ? 'bte' : 'bus';
            $street = "{$matches['street']} {$matches['number']}";
            if ($matches['number_suffix']) {
                $street .= " {$boxSeparator} {$matches['number_suffix']}";
            }
            $houseNumber = '';
            $houseNumberSuffix = '';
        } else {
            $street = $address->address1;
            $houseNumber = '';
            $houseNumberSuffix = '';
        }

        $customer = new Customer($order->id_customer);
        if ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED]) {
            switch ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED_TYPE]) {
                case MyParcel::INSURED_TYPE_100:
                    $insuranceAmount = 10000;
                    break;
                case MyParcel::INSURED_TYPE_250:
                    $insuranceAmount = 25000;
                    break;
                case MyParcel::INSURED_TYPE_500:
                    $insuranceAmount = 50000;
                    break;
                case MyParcel::INSURED_TYPE_500_PLUS:
                    $insuranceAmount = (int) $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT];
                    break;
                default:
                    $insuranceAmount = 0;
                    break;
            }
        } else {
            $insuranceAmount = 0;
        }

        if (Validate::isLoadedObject($carrierSetting)
            && ($carrierSetting->delivery || $carrierSetting->pickup)
        ) {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL;
        }
        if ($deliveryOption instanceof Dot) {
            if ($deliveryOption->get('extraOptions.signature')) {
                $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = (bool) $deliveryOption->get('extraOptions.signature', false);
            }
            if ($deliveryOption->get('extraOptions.onlyRecipient')
                || in_array($deliveryOption->get('data.time.0.type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))
            ) {
                $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = true;
            }
        }
        /** @var Collection $orderInvoice */
        $orderInvoice = $order->getInvoicesCollection();
        $orderInvoice = $orderInvoice->getFirst();
        $formattedInvoiceNumber = '';
        /** @var OrderInvoice $orderInvoice */
        if (Validate::isLoadedObject($orderInvoice)) {
            $formattedInvoiceNumber = $orderInvoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
        }

        $concept = array(
            'cc'                     => Tools::strtoupper(Country::getIsoById($address->id_country)),
            'street'                 => (string) $street,
            'number'                 => (string) $houseNumber,
            'number_suffix'          => (string) $houseNumberSuffix,
            'street_additional_info' => (string) MyParcelTools::getAdditionalAddressLine($address),
            'postal_code'            => (string) $address->postcode,
            'city'                   => (string) $address->city,
            'region'                 => $address->id_state ? (string) State::getNameById($address->id_state) : '',
            'company'                => (string) $address->company,
            'person'                 => (string) $address->firstname.' '.$address->lastname,
            'phone'                  => $configuration[MyParcel::LINK_PHONE]
                ? (string) ($address->phone_mobile ? $address->phone_mobile : $address->phone)
                : '',
            'email'                  => $configuration[MyParcel::LINK_EMAIL] ? (string) $customer->email : '',
            'delivery_type'          => (int) $deliveryOption->get('data.time.0.type', MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE),
            'package_type'           => (int) $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] ?: MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL,
            'only_recipient'         => (bool) $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY],
            'signature'              => (bool) $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED],
            'insurance'              => (int) $insuranceAmount,
            'large_format'           => (bool) $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE],
            'age_check'              => (bool) $configuration[MyParcel::DEFAULT_CONCEPT_AGE_CHECK],
            'cooled_delivery'        => (bool) $configuration[MyParcel::DEFAULT_CONCEPT_COOLED_DELIVERY],
            'label_description'      => static::getLabelDescription($order),
            'number_of_labels'       => 1,
            'carrier'                => 1,
            'weight'                 => $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] === MyParcelConsignmentRepository::PACKAGE_TYPE_DIGITAL_STAMP
                ? static::getDigitalStampWeightForApi(MyParcelProductSetting::getTotalWeight($order->id))
                : MyParcelProductSetting::getTotalWeight($order->id),
            'customs'                => array(
                'contents' => 1,
                'invoice'  => $formattedInvoiceNumber,
                'weight'   => MyParcelProductSetting::getTotalWeight($order->id),
                'items'    => MyParcelProductSetting::getCustomsLines($order->id),
            ),
        );

        if ($deliveryOption instanceof Dot) {
            if ($deliveryDate = $deliveryOption->get('data.date')) {
                $concept['delivery_date'] = date('Y-m-d 00:00:00', strtotime($deliveryDate));
            }
            if ($deliveryType = $deliveryOption->get('data.time.0.type')) {
                $concept['delivery_type'] = (int) $deliveryType;
            }
        }
        if ($configuration[MyParcel::DEFAULT_CONCEPT_RETURN]) {
            $concept['return'] = true;
        }

        return $concept;
    }

    /**
     * Check if mailbox package carrier
     *
     * @param Cart $cart
     *
     * @return bool Indicates whether the associated order should be sent with a mailbox package
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function checkMailboxPackage($cart)
    {
        $carrier = new Carrier($cart->id_carrier);
        $mcds = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!Validate::isLoadedObject($mcds)) {
            $mcds = MyParcelCarrierDeliverySetting::createDefault($carrier->id_reference);
            $mcds->save();
        }

        return (bool) $mcds->mailbox_package;
    }

    /**
     * Check if digital stamp
     *
     * @param Cart $cart
     *
     * @return bool Indicates whether the associated order should be sent with a digital stamp
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function checkDigitalStamp($cart)
    {
        $carrier = new Carrier($cart->id_carrier);
        $mcds = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!Validate::isLoadedObject($mcds)) {
            $mcds = MyParcelCarrierDeliverySetting::createDefault($carrier->id_reference);
            $mcds->save();
        }

        return (bool) $mcds->digital_stamp;
    }

    /**
     * Generate label text for concept
     *
     * @param Order $order
     *
     * @return bool|mixed|string
     *
     * @since 2.3.0 renamed `getLabelConcept` to `getLabelDescription`
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getLabelDescription($order)
    {
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $label = Configuration::get(MyParcel::LABEL_DESCRIPTION);
        $label = str_replace('{order.id}', (int) $order->id, $label);
        $label = str_replace('{order.reference}', pSQL($order->reference), $label);

        return $label;
    }

    /**
     * Get Delivery Option info by Order
     *
     * @param int|Order $order Order ID or object
     *
     * @return string Delivery from DB
     *
     * @since 2.0.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     * @throws Adapter_Exception
     */
    public static function getByOrder($order)
    {
        if ($order instanceof Order) {
            $idOrder = $order->id;
        } else {
            $idOrder = $order;
        }

        return static::getByOrderId($idOrder);
    }

    /**
     * Get concepts by Order IDs
     *
     * @param array $range Range of Order IDs
     *
     * @return array Concepts
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.0.0
     * @since 2.3.0 renamed `getConceptsByOrderIds` to `createConceptsByOrderIds`
     *
     * @deprecated 2.3.0
     *
     */
    public static function getConceptsByOrderIds($range)
    {
        if (method_exists('Tools', 'displayAsDeprecated')) {
            Tools::displayAsDeprecated();
        }

        return static::createConceptsByOrderIds($range);
    }

    /**
     * Get concepts by Order IDs
     *
     * @param array $range Range of Order IDs
     *
     * @return array Concepts
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.0.0
     * @since 2.3.0 renamed `getConceptsByOrderIds` to `createConceptsByOrderIds`
     */
    public static function createConceptsByOrderIds($range)
    {
        $sql = new DbQuery();
        $sql->select('o.`id_order`');
        $sql->from('orders', 'o');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $concepts = array();

        foreach ($results as $result) {
            $concept = array();
            $order = new Order($result['id_order']);
            $concept['concept'] = static::createConcept($order);
            $concept['idOrder'] = (int) $order->id;

            $concepts[] = $concept;
        }

        return $concepts;
    }

    /**
     * Save concept
     *
     * @param Order|int $order
     * @param string    $concept
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     *
     * @since 2.0.0
     */
    public static function saveConcept($order, $concept)
    {
        if ($order instanceof Order) {
            $idOrder = $order->id;
        } else {
            $idOrder = $order;
        }

        if (!$idOrder) {
            return false;
        }

        $concept = @json_decode($concept, true);

        $idCart = Cart::getCartIdByOrderId($idOrder);

        $sql = new DbQuery();
        $sql->select('`id_cart`');
        $sql->select(bqSQL(static::$definition['table']));
        $sql->from(bqSQL(static::$definition['table']), 'mdo');
        $sql->where('mdo.`id_cart` = '.(int) $idCart);

        if ($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $deliveryOption = @json_decode($result[static::$definition['table']], true);
            if (!empty($deliveryOption['concept'])) {
                $deliveryOption['concept'] = $concept;
                return Db::getInstance()->update(
                    bqSQL(static::$definition['table']),
                    array(
                        bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode($deliveryOption), true)."'"),
                    ),
                    '`id_cart` = '.(int) $idCart
                );
            } else {
                $deliveryOption = array('concept' => $concept, 'idOrder' => (int) $idOrder);
                return Db::getInstance()->update(
                    bqSQL(static::$definition['table']),
                    array(
                        bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode($deliveryOption), true)."'"),
                    ),
                    '`id_cart` = '.(int) $idCart
                );
            }
        }

        $deliveryOption = mypa_json_encode(array('concept' => $concept));

        return Db::getInstance()->insert(
            bqSQL(static::$definition['table']),
            array(
                bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL($deliveryOption, true)."'"),
                'id_cart'                           => (int) $idCart,
            )
        );
    }

    /**
     * Save concept data
     *
     * @param Order|int $order
     * @param array|Dot $conceptData
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     *
     * @since 2.2.0
     * @since 2.3.0 Require array/Dot array of concept data
     */
    public static function saveConceptData($order, $conceptData)
    {
        if ($order instanceof Order) {
            $idOrder = $order->id;
        } else {
            $idOrder = $order;
        }
        if (!$idOrder || (!$conceptData instanceof Dot && !is_array($conceptData))) {
            return false;
        }
        if (is_array($conceptData)) {
            $conceptData = mypa_dot($conceptData);
        }

        $idCart = Cart::getCartIdByOrderId($idOrder);
        $deliveryOption = array(
            'idOrder'      => $conceptData->get('idOrder'),
            'concept'      => $conceptData->get('concept'),
            'extraOptions' => $conceptData->get('extraOptions'),
            'data'         => $conceptData->get('data'),
            'previous'     => $conceptData->get('previous'),
        );
        foreach (array_keys($deliveryOption) as $key) {
            if ($deliveryOption[$key] === null) {
                unset($deliveryOption[$key]);
            }
        }
        if (!$deliveryOption['idOrder']) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('`'.bqSQL(static::$definition['primary']).'`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return Db::getInstance()->update(
                bqSQL(static::$definition['table']),
                array(
                    bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode($deliveryOption), true)."'"),
                ),
                '`id_cart` = '.(int) $idCart
            );
        }

        return Db::getInstance()->insert(
            bqSQL(static::$definition['table']),
            array(
                bqSQL(static::$definition['table']) => array('type' => 'sql', 'value' => "'".pSQL(mypa_json_encode($deliveryOption), true)."'"),
                'id_cart'                           => $idCart,
            ),
            '`id_cart` = '.(int) $idCart
        );
    }

    /**
     * Calculates the next delivery date
     *
     * If a time is passed, this function will only add up the days, keeping the exact time intact
     *
     * @param string $previousDeliveryDate Delivery date (format: `Y-m-d H:i:s`)
     * @param string $shippingDate         Shipping date (format: `Y-m-d H:i:s`)
     *
     * @return string (format: `Y-m-d H:i:s`)
     *
     * @throws Exception
     *
     * @since 2.2.0
     */
    public static function getDeliveryDay($previousDeliveryDate, $shippingDate = '')
    {
        $deliveryDate = new DateTime($previousDeliveryDate);
        $holidays = static::getHolidaysForYear(date('Y', strtotime($previousDeliveryDate)));
        do {
            $deliveryDate->add(new DateInterval('P1D'));
        } while (in_array($deliveryDate->format('Y-m-d'), $holidays) || $deliveryDate->format('w') == 0);
        if (in_array(date('Y-m-d', strtotime($shippingDate)), $holidays)) {
            $deliveryDate->add(new DateInterval('P1D'));
        }

        return $deliveryDate->format('Y-m-d H:i:s');
    }

    /**
     * Get preferred delivery day from delivery option
     *
     * @param array $option
     *
     * @return string
     *
     * @since 2.2.0
     */
    public static function getPreferredDeliveryDay($option)
    {
        if (isset($option['data']['date'])) {
            $deliveryDate = $option['data']['date'];
            if (isset($option['data']['time'][0]['start'])) {
                $time = "{$option['data']['time'][0]['start']}";
            } else {
                $time = '15:00:00';
            }
            if ($time === '15:00:00') {
                $time = '15:00:00';
            }

            $deliveryDate = "{$deliveryDate} {$time}";
        } else {
            $deliveryDate = '1970-01-01 00:00:00';
        }

        return $deliveryDate;
    }

    /**
     * Get preferred delivery day from delivery option
     *
     * @param array $option
     *
     * @return string|null
     *
     * @since 2.2.0
     */
    public static function getPreferredPickup($option)
    {
        if (isset($option['data']['location_code'])) {
            return "{$option['data']['location']}, {$option['data']['street']} {$option['data']['number']}, {$option['data']['city']}";
        }

        return null;
    }

    /**
     * Calculates amount of days remaining
     * i.e. preferred delivery date the day tomorrow => today = 0
     * i.e. preferred delivery date the day after tomorrow => today + tomorrow = 1
     * i.e. preferred delivery date the day after tomorrow, but one holiday => today + holiday = 0
     *
     * 0 means: should ship today
     * < 0 means: should've shipped in the past
     * anything higher means: you've got some more time
     *
     * @param string $shippingDate          Shipping date (format: `Y-m-d H:i:s`)
     * @param string $preferredDeliveryDate Customer preference
     *
     * @return int
     *
     * @throws Exception
     *
     * @since 2.2.0
     */
    public static function getShippingDaysRemaining($shippingDate, $preferredDeliveryDate)
    {
        // Remove the hours/minutes/seconds
        $shippingDate = date('Y-m-d 00:00:00', strtotime($shippingDate));

        // Find the nearest delivery date
        $nearestDeliveryDate = static::getDeliveryDay($shippingDate);

        // Calculate the interval
        $nearestDeliveryDate = new DateTime($nearestDeliveryDate);
        $preferredDeliveryDate = new DateTime(date('Y-m-d 00:00:00', strtotime($preferredDeliveryDate)));

        $daysRemaining = (int) $nearestDeliveryDate->diff($preferredDeliveryDate)->format('%R%a');

        // Subtract an additional day if we cannot ship today (Sunday or holiday)
        if (date('w', strtotime($shippingDate)) == 0 ||
            in_array(
                date('Y-m-d', strtotime($shippingDate)),
                static::getHolidaysForYear(date('Y', strtotime($shippingDate)))
            )
        ) {
            $daysRemaining--;
        }

        return $daysRemaining;
    }

    /**
     * Raw `myparcel_delivery_option`
     * This function checks if pickup has been chosen
     *
     * @param array $option
     *
     * @return bool
     */
    protected static function isPickup($option)
    {
        return in_array(
            mypa_dot($option)->get('data.time.0.type', MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE),
            array(MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL, MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL_EXPRESS)
        );
    }

    /**
     * Get an array with all Dutch holidays for the given year
     *
     * @param string $year
     *
     * @return array
     *
     * @throws Exception
     *
     * @since 2.2.0
     *
     * Credits to @tvlooy (https://gist.github.com/tvlooy/1894247)
     */
    public static function getHolidaysForYear($year)
    {
        if (!extension_loaded('calendar')) {
            return array();
        }

        // Avoid holidays
        // Fixed
        $nieuwjaar = new DateTime($year.'-01-01');
        $eersteKerstDag = new DateTime($year.'-12-25');
        $tweedeKerstDag = new DateTime($year.'-12-26');
        $koningsdag = new DateTime($year.'-04-27');
        // Dynamic
        $pasen = new DateTime();
        $pasen->setTimestamp(easter_date($year)); // thanks PHP!
        $paasMaandag = clone $pasen;
        $paasMaandag->add(new DateInterVal('P1D'));
        $hemelvaart = clone $pasen;
        $hemelvaart->add(new DateInterVal('P39D'));
        $pinksteren = clone $hemelvaart;
        $pinksteren->add(new DateInterVal('P10D'));
        $pinksterMaandag = clone $pinksteren;
        $pinksterMaandag->add(new DateInterVal('P1D'));

        $holidays = array(
            $nieuwjaar->format('Y-m-d'),
            $pasen->format('Y-m-d'),
            $paasMaandag->format('Y-m-d'),
            $koningsdag->format('Y-m-d'),
            $hemelvaart->format('Y-m-d'),
            $pinksteren->format('Y-m-d'),
            $pinksterMaandag->format('Y-m-d'),
            $eersteKerstDag->format('Y-m-d'),
            $tweedeKerstDag->format('Y-m-d'),
        );

        return $holidays;
    }

    /**
     * @param Dot|array $shipments
     *
     * @return MyParcelCollection
     *
     * @throws PrestaShopDatabaseException
     * @throws Exception
     *
     * @since 2.3.0
     */
    public static function consignmentCollectionFromConceptData($shipments)
    {
        if ($shipments instanceof Dot) {
            $shipments = $shipments->jsonSerialize();
        }
        $shopIdentifier = MyParcel::getShopIdentifier();

        $consignmentCollection = new MyParcelCollection();
        foreach ($shipments as $conceptData) {
            $order = new Order($conceptData['idOrder']);
            $conceptData = mypa_dot($conceptData);
            $consignment = new MyParcelConsignmentRepository();
            $consignment
                ->setApiKey(Configuration::get(MyParcel::API_KEY, null, $order->id_shop_group, $order->id_shop))
                ->setReferenceId("PRESTASHOP_{$shopIdentifier}_{$conceptData->get('idOrder')}")
                ->setStreet($conceptData->get('concept.street'))
                ->setCity($conceptData->get('concept.city'))
                ->setCountry($conceptData->get('concept.cc'))
                ->setPackageType($conceptData->get('concept.package_type'))
                ->setLabelDescription($conceptData->get('concept.label_description'))
            ;
            $optionalFields = array(
                'concept.person'        => 'Person',
                'concept.company'       => 'Company',
                'concept.postal_code'   => 'PostalCode',
                'concept.email'         => 'Email',
                'concept.phone'         => 'Phone',
            );
            foreach ($optionalFields as $path => $function) {
                if (!$conceptData->isEmpty($path)) {
                    $consignment->{"set{$function}"}($conceptData->get($path));
                }
            }
            if ($conceptData->get('concept.cc') === 'NL') {
                $consignment
                    ->setOnlyRecipient($conceptData->get('concept.only_recipient', false))
                    ->setSignature($conceptData->get('concept.signature', false))
                    ->setReturn($conceptData->get('concept.return', false))
                    ->setLargeFormat($conceptData->get('concept.large_format', false))
                ;
                $consignment->setNumber($conceptData->get('concept.number'));
                if (!$conceptData->isEmpty('concept.number_suffix')) {
                    $consignment->setNumberSuffix($conceptData->get('concept.number_suffix'));
                }
                if ($conceptData->get('concept.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_DIGITAL_STAMP) {
                    $consignment->setPhysicalProperties(array('weight' => (int) $conceptData->get('concept.weight')));
                }
                if ($conceptData->get('concept.insurance')) {
                    $consignment->setInsurance((int) $conceptData->get('concept.insurance') / 100);
                    $consignment->setSignature(true);
                    $consignment->setOnlyRecipient(true);
                }
                if (!$conceptData->isEmpty('data')
                    && !$conceptData->isEmpty('concept.delivery_type')
                    && $conceptData->get('concept.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL
                ) {
                    $consignment->setDeliveryDateFromCheckout(mypa_json_encode($conceptData->get('data')));
                    $consignment->setDeliveryType($conceptData->get('concept.delivery_type'));
                    if (in_array($conceptData->get('concept.delivery_type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL, MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL_EXPRESS))) {
                        $consignment->setPickupAddressFromCheckout(mypa_json_encode($conceptData->get('data')));
                        $consignment->setSignature(true);
                        $consignment->setReturn(false);
                    } elseif (in_array($conceptData->get('concept.delivery_type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))) {
                        $consignment->setOnlyRecipient(true);
                    }
                }
                if ($conceptData->get('concept.cooled_delivery') && in_array($conceptData->get('data.time.0.type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))) {
                    $consignment->setCooledDelivery(true);
                    $consignment->setOnlyRecipient(true);
                    $consignment->setSignature(false);
                    $consignment->setInsurance(0);
                } elseif ($conceptData->get('concept.age_check') && !in_array($conceptData->get('concept.delivery_type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT))) {
                    $consignment->setAgeCheck(true);
                    $consignment->setSignature(true);
                    $consignment->setOnlyRecipient(true);
                }
            } elseif ($conceptData->get('concept.cc') === 'BE') {
                $consignment->setInsurance(500);
                $consignment->setLargeFormat($conceptData->get('concept.large_format'));
                if (
                    !$conceptData->isEmpty('data')
                    && !$conceptData->isEmpty('concept.delivery_type')
                    && $conceptData->get('concept.package_type') === MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL
                ) {
                    $consignment->setDeliveryDateFromCheckout(mypa_json_encode($conceptData->get('data')));
                    $consignment->setDeliveryType($conceptData->get('concept.delivery_type'));
                    if (in_array($conceptData->get('concept.delivery_type'), array(MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL, MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL_EXPRESS))) {
                        $consignment->setPickupAddressFromCheckout(mypa_json_encode($conceptData->get('data')));
                    }
                }
            } elseif (in_array($conceptData->get('concept.cc'), array_keys(array_filter(MyParcel::getCountries(), function ($country) {
                return $country['region'] === 'EU';
            })))) {
                $consignment->setInsurance(500);
                if (in_array($conceptData->get('concept.cc'), MyParcel::getLargeFormatCountries())) {
                    $consignment->setLargeFormat($conceptData->get('concept.large_format'));
                }
            } else {
                $consignment->setInsurance(200);
                $consignment->setContents($conceptData->get('concept.customs.contents') ?: 1);
                $consignment->setInvoice($conceptData->get('concept.customs.invoice') ?: 1);
                foreach ($conceptData->get('concept.customs.items', array()) as $rawItem) {
                    $item = new MyParcelCustomsItem();
                    $item->setAmount($rawItem['amount']);
                    $item->setClassification($rawItem['classification']);
                    $item->setCountry($rawItem['country']);
                    $item->setDescription(Tools::substr($rawItem['description'], 0, 50));
                    $item->setItemValue($rawItem['item_value']['amount']);
                    $item->setWeight($rawItem['weight']);
                    $consignment->addItem($item);
                }
            }
            $consignmentCollection->addConsignment($consignment);
        }

        return $consignmentCollection;
    }

    /**
     * Check and move special delivery option
     *
     * @param Dot $conceptData
     *
     * @return void
     *
     * @throws Exception
     *
     * @since 2.3.0
     */
    public static function checkSpecialDeliveryOption(Dot &$conceptData)
    {
        // Fix `delivery_date`s in the past
        $checkDate = null;
        if (!$conceptData->isEmpty('concept.options.delivery_date')) {
            $checkDate = date('Y-m-d', strtotime($conceptData->get('concept.options.delivery_date')));
        } elseif (!$conceptData->isEmpty('data.date')) {
            $checkDate = date('Y-m-d', strtotime($conceptData->get('data.date')));
        }
        if ($checkDate && $checkDate <= date('Y-m-d')) {
            // Copy a deep clone of data to oldData
            $conceptData->set('previous', $conceptData->get('data'));
            $newDeliveryDate = date('Y-m-d', strtotime(static::getDeliveryDay(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))));
            $conceptData->set('concept.delivery_date', $newDeliveryDate);
            // Reset date in data if set
            if ($conceptData->has('data.date')) {
                $conceptData->set('data.date', $newDeliveryDate);
            }
            // Correct delivery type if necessary
            if (in_array(date('D', strtotime($newDeliveryDate)), array('Mon', 'Sat'))) {
                if (in_array($conceptData->get('concept.delivery_type'), array(1, 3))) {
                    $conceptData->set('concept.delivery_type', 2);
                    if ($conceptData->has('data.time.0')) {
                        $conceptData->set('data.time.0.start', '08:00:00');
                        $conceptData->set('data.time.0.end', '21:00:00');
                        $conceptData->set('data.time.0.price_comment', 'standard');
                    }
                } elseif (in_array($conceptData->get('concept.delivery_type'), array(5))) {
                    $conceptData->set('concept.delivery_type', 4);
                    if ($conceptData->has('data.time.0')) {
                        $conceptData->set('data.time.0.start', '08:00:00');
                        $conceptData->set('data.time.0.end', '21:00:00');
                        $conceptData->set('data.time.0.price_comment', 'retail');
                    }
                }
                // Set the new type in data
                if ($conceptData->has('data.time.0.type')) {
                    $conceptData->set('data.time.0.type', $conceptData->get('concept.delivery_type'));
                }
            }
        }
        // Check cooled delivery
        if (!in_array(
            $conceptData->get('data.time.0.type'),
            array(MyParcelConsignmentRepository::DELIVERY_TYPE_MORNING, MyParcelConsignmentRepository::DELIVERY_TYPE_NIGHT)
        )) {
            $conceptData->set('cooled_delivery', 0);
        }
    }

    /**
     * Calculated weight in grams
     *
     * @param int $weight Weight in grams
     *
     * @return int $weight in grams
     *
     * @since 2.3.3
     */
    public static function getDigitalStampWeightForApi($weight)
    {
        if ($weight >= 0 && $weight <= 20) {
            return 10;
        } elseif ($weight > 20 && $weight <= 50) {
            return 35;
        } elseif ($weight > 50 && $weight <= 100) {
            return 75;
        } elseif ($weight > 100 && $weight <= 350) {
            return 225;
        } else {
            return 1175;
        }
    }
}

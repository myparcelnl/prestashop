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
        'fields' => array(
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
            'country_iso'              => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'CHAR(2)',
            ),
            'company'                  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(255)',
            ),
            'name'                     => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'TEXT',
            ),
            'postcode'                 => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(16)',
            ),
            'house_number'             => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(16)',
            ),
            'house_number_add'         => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(16)',
            ),
            'street1'                  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'TEXT',
            ),
            'street2'                  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'TEXT',
            ),
            'email'                    => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(255)',
            ),
            'phone'                    => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(255)',
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
     * Uppercase 2-letter Country ISO
     *
     * @var string $country_iso
     */
    public $country_iso;
    /**
     * Company
     *
     * @var string $company
     */
    public $company;
    /**
     * Full name as should appear on the shipping label
     *
     * @var string $name
     */
    public $name;
    /**
     * Postcode
     *
     * @var string $postcode
     */
    public $postcode;
    /**
     * House number (numerical)
     *
     * @var string $house_number
     */
    public $house_number;
    /**
     * House number addition
     *
     * @var string $house_number_add
     */
    public $house_number_add;
    /**
     * Addres line 1
     *
     * @var string $street1
     */
    public $street1;
    /**
     * Address line 2
     *
     * @var string $street2
     */
    public $street2;
    /**
     * Email
     *
     * @var string $email
     */
    public $email;
    /**
     * Fixed or mobile phone number
     *
     * @var string $phone
     */
    public $phone;
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

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getrow($sql, $cache);
        } catch (PrestaShopException $e) {
            return false;
        }

        if (empty($result)) {
            return false;
        }

        $option = new self();
        $option->hydrate($result);

        return $option;
    }

    /**
     * Get Delivery Option info by Cart
     *
     * @param int|Cart $cart  Cart ID or object
     * @param bool     $cache Enable DB cache
     *
     * @return false|object Delivery from DB
     *
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
        $sql->select('`myparcel_delivery_option`');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql, $cache);
        } catch (PrestaShopException $e) {
            return false;
        }

        if ($result) {
            return json_decode($result);
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

        try {
            if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
                return (bool) Db::getInstance()->update(
                    bqSQL(static::$definition['table']),
                    array(
                        'myparcel_delivery_option' => $deliveryOption,
                        'date_delivery'            => date('Y-m-d H:i:s', strtotime($preferredDeliveryDay)),
                        'pickup'                   => bqSQL($preferredPickup),
                    ),
                    '`id_cart` = '.(int) $idCart
                );
            } else {
                return (bool) Db::getInstance()->insert(
                    bqSQL(static::$definition['table']),
                    array(
                        'myparcel_delivery_option' => $deliveryOption,
                        'id_cart'                  => (int) $idCart,
                        'date_delivery'            => date('Y-m-d H:i:s', strtotime($preferredDeliveryDay)),
                        'pickup'                   => bqSQL($preferredPickup),
                    )
                );
            }
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * Remove the delivery option info
     *
     * @param int $idCart
     *
     * @return bool
     *
     * @since 2.0.0
     */
    public static function removeDeliveryOption($idCart)
    {
        try {
            return Db::getInstance()->update(
                bqSQL(static::$definition['table']),
                array(
                    'myparcel_delivery_option' => null,
                ),
                '`id_cart` = '.(int) $idCart,
                1,
                true
            );
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * Get by Order IDs
     *
     * @param array $range Range of Order IDs
     *
     * @return array Array with `MyParcelDeliveryoption`s
     *
     * @since 2.0.0
     */
    public static function getByOrderIds($range)
    {
        if (empty($range)) {
            return array();
        }

        foreach ($range as &$item) {
            $item = (int) $item;
        }

        $sql = new DbQuery();
        $sql->select('o.`id_order`, mdo.`myparcel_delivery_option`, a.*');
        $sql->from(bqSQL(static::$definition['table']), 'mdo');
        $sql->innerJoin('orders', 'o', 'mdo.`id_cart` = o.`id_cart`');
        $sql->innerJoin('address', 'a', 'o.`id_address_delivery` = a.`id_address`');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');

        try {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $results = array();
        }

        $deliveryOptions = array();
        foreach ($results as $result) {
            $deliveryOption = json_decode($result['myparcel_delivery_option'], true);
            $deliveryOption['id_order'] = (string) $result['id_order'];

            if (empty($deliveryOption['concept']) || !static::validateDeliveryOption($deliveryOption, true)) {
                $order = new Order($result['id_order']);
                $address = new Address($order->id_address_delivery);
                $cart = new Cart($order->id_cart);
                $deliveryOption['concept'] = static::createConcept(
                    $order,
                    static::getByOrder($order),
                    $address,
                    static::checkMailboxPackage($cart)
                );
            }

            if ($deliveryOption['concept']) {
                $deliveryOptions[] = $deliveryOption;
            }

            // Remove ID from range array
            if (($key = array_search($result['id_order'], $range)) !== false) {
                unset($range[$key]);
            }
        }

        if (!empty($range)) {
            $deliveryOptions = array_merge($deliveryOptions, static::getConceptsByOrderIds($range));
        }

        return $deliveryOptions;
    }

    /**
     * @param Order                       $order
     * @param bool|MyParcelDeliveryOption $deliveryOption
     * @param Address                     $address
     * @param bool                        $mailboxPackage
     *
     * @return null|array
     *
     * @since 2.0.0
     */
    public static function createConcept($order, $deliveryOption = null, $address = null, $mailboxPackage = null)
    {
        if (!$address) {
            $address = new Address($order->id_address_delivery);
        }
        if (is_null($mailboxPackage)) {
            if ($deliveryOption instanceof MyParcelDeliveryOption) {
                $mailboxPackage = $deliveryOption->concept->options->package_type == 2;
            } else {
                $mailboxPackage = static::checkMailboxPackage(new Cart($order->id_cart));
            }
        }


        try {
            $countryIso = Tools::strtolower(Country::getIsoById($address->id_country));
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return null;
        }

        try {
            if (isset($deliveryOption->type) && $deliveryOption->type === 'pickup'
                && in_array($countryIso, array('nl', 'be'))
            ) {
                return static::createPickupConcept($address, $deliveryOption, $order);
            } elseif (in_array($countryIso, array('nl'))) {
                return static::createNationalConcept($address, $deliveryOption, $order, $mailboxPackage);
            } else {
                return static::createInternationalConcept($address, $order);
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Check if mailbox package carrier
     *
     * @param Cart $cart
     *
     * @return bool Indicates whether the associated order can be sent with a mailbox package
     */
    public static function checkMailboxPackage($cart)
    {
        $carrier = new Carrier($cart->id_carrier);
        $mcds = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!Validate::isLoadedObject($mcds)) {
            $mcds = MyParcelCarrierDeliverySetting::createDefault($carrier->id_reference);
            try {
                $mcds->save();
            } catch (Exception $e) {
            }
        }

        return (bool) $mcds->mailbox_package;
    }

    /**
     * Create concept for national shipments
     *
     * @param Address                $address
     * @param MyParcelDeliveryOption $deliveryOption
     * @param Order|null             $order
     *
     * @return array
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    public static function createPickupConcept($address, $deliveryOption, $order = null)
    {
        $email = '';
        if ($order) {
            $customer = new Customer($order->id_customer);

            if (Validate::isLoadedObject($customer)) {
                $email = $customer->email;
            }
        }

        try {
            $configuration = Configuration::getMultiple(
                array(
                    MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE,
                    MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE,
                    MyParcel::DEFAULT_CONCEPT_RETURN,
                    MyParcel::DEFAULT_CONCEPT_INSURED,
                    MyParcel::DEFAULT_CONCEPT_INSURED_TYPE,
                    MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT,
                    MyParcel::LINK_EMAIL,
                    MyParcel::LINK_PHONE,
                )
            );
        } catch (PrestaShopException $e) {
        }
        if (isset($deliveryOption->type) && $deliveryOption->type === 'pickup') {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = MyParcel::TYPE_PARCEL;
        }

        preg_match(MyParcel::SPLIT_STREET_REGEX, MyParcelTools::getAddressLine($address), $matches);
        $street = isset($matches['street']) ? $matches['street'] : '';
        $houseNumber = isset($matches['street_suffix']) ? $matches['street_suffix'] : '';

        $countryIso = Tools::strtolower(Country::getIsoById($address->id_country));
        if ($countryIso === 'nl' && $configuration[MyParcel::DEFAULT_CONCEPT_INSURED]) {
            switch ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED_TYPE]) {
                case MyParcel::INSURED_TYPE_50:
                    $insuranceAmount = 5000;
                    break;
                case MyParcel::INSURED_TYPE_250:
                    $insuranceAmount = 25000;
                    break;
                case MyParcel::INSURED_TYPE_500:
                    $insuranceAmount = 50000;
                    break;
                case MyParcel::INSURED_TYPE_500_PLUS:
                    $insuranceAmount = (int) $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT] * 100;
                    break;
                default:
                    $insuranceAmount = 0;
                    break;
            }
        } else {
            // Set the concept to 0, final export will set the amount acc. to the country
            $insuranceAmount = 0;
        }

        if (isset($deliveryOption->extraOptions->recipientOnly)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] =
                !empty($deliveryOption->extraOptions->recipientOnly);
        }
        if (isset($deliveryOption->extraOptions->signed)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = !empty($deliveryOption->extraOptions->signed);
        }

        $options = array(
            'package_type'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] ?: 1,
            'delivery_type'     => (int) $deliveryOption->data->time[0]->type,
            'delivery_date'     => (string) date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date)),
            'only_recipient'    => (int) $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY],
            'signature'         => (int) $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED],
            'insurance'         => array(
                'amount'   => $insuranceAmount,
                'currency' => 'EUR',
            ),
            'large_format'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE],
            'label_description' => static::getLabelConcept($order),
        );

        if (isset($deliveryOption->data->date) && $deliveryOption->data->date) {
            $options['delivery_date'] = date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date));
        }
        if (isset($deliveryOption->data->type) && $deliveryOption->data->type) {
            $options['delivery_type'] = (int) $deliveryOption->data->type;
        }

        return array(
            'recipient' => array(
                'cc'                     => Tools::strtoupper(Country::getIsoById($address->id_country)),
                'street'                 => (string) $street,
                'street_additional_info' => (string) MyParcelTools::getAdditionalAddressLine($address),
                'number'                 => (string) $houseNumber,
                'postal_code'            => (string) $address->postcode,
                'city'                   => (string) $address->city,
                'region'                 => (string) $address->id_state ? State::getNameById($address->id_state) : '',
                'company'                => (string) $address->company,
                'person'                 => (string) $address->firstname.' '.$address->lastname,
                'phone'                  => (string) $configuration[MyParcel::LINK_PHONE] ? ($address->phone_mobile
                    ? $address->phone_mobile : $address->phone)
                    : '',
                'email'                  => (string) ($configuration[MyParcel::LINK_EMAIL]) ? $email : '',
            ),
            'options'   => $options,
            'pickup'    => array(
                'postal_code'       => (string) $deliveryOption->data->postal_code,
                'street'            => (string) $deliveryOption->data->street,
                'number'            => (string) $deliveryOption->data->number,
                'city'              => (string) $deliveryOption->data->city,
                'location_name'     => (string) $deliveryOption->data->location,
                'location_code'     => (string) $deliveryOption->data->location_code,
                'retail_network_id' => (string) $deliveryOption->data->retail_network_id,
            ),
            'carrier'   => 1,
        );
    }

    /**
     * Generate label text for concept
     *
     * @param Order $order
     *
     * @return bool|mixed|string
     */
    public static function getLabelConcept($order)
    {
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        try {
            $label = Configuration::get(MyParcel::LABEL_DESCRIPTION);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return '';
        }
        $label = str_replace('{order.id}', (int) $order->id, $label);
        $label = str_replace('{order.reference}', pSQL($order->reference), $label);

        return $label;
    }

    /**
     * Create concept for national shipments
     *
     * @param Address       $address
     * @param stdClass|null $deliveryOption
     * @param Order|null    $order
     * @param bool          $mailboxPackage
     *
     * @return null|array
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    public static function createNationalConcept(
        $address,
        $deliveryOption = null,
        $order = null,
        $mailboxPackage = false
    ) {
        $email = '';
        if ($order) {
            $customer = new Customer($order->id_customer);

            if (Validate::isLoadedObject($customer)) {
                $email = $customer->email;
            }
        }

        try {
            $configuration = Configuration::getMultiple(
                array(
                    MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE,
                    MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE,
                    MyParcel::DEFAULT_CONCEPT_RETURN,
                    MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY,
                    MyParcel::DEFAULT_CONCEPT_SIGNED,
                    MyParcel::DEFAULT_CONCEPT_INSURED,
                    MyParcel::DEFAULT_CONCEPT_INSURED_TYPE,
                    MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT,
                    MyParcel::LINK_EMAIL,
                    MyParcel::LINK_PHONE,
                )
            );
        } catch (PrestaShopException $e) {
            $configuration = array(
                MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE => MyParcel::TYPE_PARCEL,
                MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE => false,
                MyParcel::DEFAULT_CONCEPT_RETURN => false,
                MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY => false,
                MyParcel::DEFAULT_CONCEPT_SIGNED => false,
                MyParcel::DEFAULT_CONCEPT_INSURED => false,
                MyParcel::DEFAULT_CONCEPT_INSURED_TYPE => 1,
                MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT => 0,
                MyParcel::LINK_EMAIL => true,
                MyParcel::LINK_PHONE => true,
            );
        }
        if (isset($deliveryOption->type) && in_array($deliveryOption->type, array('delivery', 'pickup'))) {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = MyParcel::TYPE_PARCEL;
        }
        if ($mailboxPackage) {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = MyParcel::TYPE_MAILBOX_PACKAGE;
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = false;
            $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE] = false;
            $configuration[MyParcel::DEFAULT_CONCEPT_RETURN] = false;
            $configuration[MyParcel::DEFAULT_CONCEPT_INSURED] = false;
            $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT] = 0;
        }

        preg_match(MyParcel::SPLIT_STREET_REGEX, MyParcelTools::getAddressLine($address), $matches);
        $street = isset($matches['street']) ? $matches['street'] : '';
        $houseNumber = isset($matches['street_suffix']) ? $matches['street_suffix'] : '';

        if ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED]) {
            switch ($configuration[MyParcel::DEFAULT_CONCEPT_INSURED_TYPE]) {
                case MyParcel::INSURED_TYPE_50:
                    $insuranceAmount = 5000;
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

        if (!empty($deliveryOption->extraOptions->recipientOnly)
        || (isset($deliveryOption->data->price_comment) && ($deliveryOption->data->price_comment === 'morning' || in_array($deliveryOption->data->price_comment, array('night', 'avond', 'evening'))))
        ) {
            $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = true;
        }
        if (isset($deliveryOption->extraOptions->signed)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = !empty($deliveryOption->extraOptions->signed);
        }

        $options = array(
            'package_type'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] ?: 1,
            'only_recipient'    => (int) $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY],
            'signature'         => (int) $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED],
            'insurance'         => array(
                'amount'   => $insuranceAmount,
                'currency' => 'EUR',
            ),
            'large_format'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE],
            'label_description' => static::getLabelConcept($order),
        );

        if (!empty($deliveryOption->data->date)) {
            $options['delivery_date'] = date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date));
        }
        if (!empty($deliveryOption->data->time[0]->type)) {
            $options['delivery_type'] = (int) $deliveryOption->data->time[0]->type;
        }
        if ($configuration[MyParcel::DEFAULT_CONCEPT_RETURN]) {
            $options['return'] = 1;
        }

        return array(
            'recipient' => array(
                'cc'                     => Tools::strtoupper(Country::getIsoById($address->id_country)),
                'street'                 => (string) $street,
                'number'                 => (string) $houseNumber,
                'street_additional_info' => (string) MyParcelTools::getAdditionalAddressLine($address),
                'postal_code'            => (string) $address->postcode,
                'city'                   => (string) $address->city,
                'region'                 => (string) $address->id_state ? State::getNameById($address->id_state) : '',
                'company'                => (string) $address->company,
                'person'                 => (string) $address->firstname.' '.$address->lastname,
                'phone'                  => (string) $configuration[MyParcel::LINK_PHONE]
                    ? ($address->phone_mobile ? $address->phone_mobile : $address->phone)
                    : '',
                'email'                  => (string) ($configuration[MyParcel::LINK_EMAIL]) ? $email : '',
            ),
            'options'   => $options,
            'carrier'   => 1,
        );
    }

    /**
     * Create concept for international shipments
     *
     * @param Address    $address
     * @param Order|null $order
     *
     * @return array
     * @throws PrestaShopException
     */
    public static function createInternationalConcept($address, $order = null)
    {
        $email = '';
        if ($order) {
            $customer = new Customer($order->id_customer);

            if (Validate::isLoadedObject($customer)) {
                $email = $customer->email;
            }
        }

        $countryIso = Tools::strtoupper(Country::getIsoById($address->id_country));
        if ($countryIso === 'NL' && Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED)) {
            switch (Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED_TYPE)) {
                case MyParcel::INSURED_TYPE_50:
                    $insuranceAmount = 5000;
                    break;
                case MyParcel::INSURED_TYPE_250:
                    $insuranceAmount = 25000;
                    break;
                case MyParcel::INSURED_TYPE_500:
                    $insuranceAmount = 50000;
                    break;
                case MyParcel::INSURED_TYPE_500_PLUS:
                    $insuranceAmount = (int) Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT) * 100;
                    break;
                default:
                    $insuranceAmount = 0;
                    break;
            }
        } else {
            // Set the concept to 0, final export will set the amount acc. to the country
            $insuranceAmount = 0;
        }

        if (in_array($countryIso, array('NL', 'BE'))) {
            preg_match(MyParcel::SPLIT_STREET_REGEX, MyParcelTools::getAddressLine($address), $matches);
            $street = isset($matches['street']) ? $matches['street'] : '';
            $houseNumber = isset($matches['street_suffix']) ? $matches['street_suffix'] : '';
            $additional = MyParcelTools::getAdditionalAddressLine($address);
        } else {
            $street = MyParcelTools::getAddressLine($address);
            $houseNumber = '';
            $additional = MyParcelTools::getAdditionalAddressLine($address);
        }

        return array(
            'recipient'           => array(
                'cc'                     => (string) Tools::strtoupper(Country::getIsoById($address->id_country)),
                'street'                 => (string) $street,
                'number'                 => (string) $houseNumber,
                'street_additional_info' => (string) $additional,
                'postal_code'            => (string) $address->postcode,
                'city'                   => (string) $address->city,
                'region'                 => (string) $address->id_state ? State::getNameById($address->id_state) : '',
                'company'                => (string) $address->company,
                'person'                 => (string) $address->firstname.' '.$address->lastname,
                'phone'                  => (string) Configuration::get(MyParcel::LINK_PHONE)
                    ? ($address->phone ? $address->phone : $address->phone_mobile)
                    : '',
                'email'                  => (string) (Configuration::get(MyParcel::LINK_EMAIL)) ? $email : '',
            ),
            'options'             => array(
                'package_type'      => 1,
                'label_description' => static::getLabelConcept($order),
                'large_format'      => (int) Configuration::get(MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE),
                'insurance'         => array(
                    'amount'   => $insuranceAmount,
                    'currency' => 'EUR',
                ),
            ),
            'customs_declaration' => array(
                'contents' => 1,
                'invoice'  => MyParcelTools::getInvoiceSuggestion($order),
                'weight'   => (int) MyParcelTools::getWeightSuggestion($order),
                'items'    => array(),
            ),
            'physical_properties' => array(
                'weight' => (int) MyParcelTools::getWeightSuggestion($order),
            ),
            'carrier'             => 1,
        );
    }

    /**
     * Get Delivery Option info by Order
     *
     * @param int|Order $order Order ID or object
     *
     * @return string Delivery from DB
     *
     * @since 2.0.0
     */
    public static function getByOrder($order)
    {
        if ($order instanceof Order) {
            $idOrder = $order->id;
        } else {
            $idOrder = $order;
        }

        $sql = new DbQuery();
        $sql->select('`myparcel_delivery_option`');
        $sql->from(bqSQL(static::$definition['table']), 'mdo');
        $sql->innerJoin('orders', 'o', 'o.`id_cart` = mdo.`id_cart`');
        $sql->where('o.`id_order` = '.(int) $idOrder);

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        } catch (PrestaShopException $e) {
            return new stdClass();
        }

        if ($result) {
            $concept = json_decode($result, true);
            static::validateDeliveryOption($concept, true);

            return json_encode($concept, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $concepts = static::getConceptsByOrderIds(array($idOrder));
        if (is_array($concepts)) {
            $concept = $concepts[0];
            static::validateDeliveryOption($concept, true);

            return json_encode($concept, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return new stdClass();
    }

    /**
     * Get concepts by Order IDs
     *
     * @param array $range Range of Order IDs
     *
     * @return array Concepts
     *
     * @since 2.0.0
     */
    public static function getConceptsByOrderIds($range)
    {
        $sql = new DbQuery();
        $sql->select('o.`id_order`');
        $sql->from('orders', 'o');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');

        try {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $results = array();
        }

        $concepts = array();

        foreach ($results as $result) {
            $concept = array();
            $order = new Order($result['id_order']);
            $concept['concept'] = static::createConcept($order);
            $concept['id_order'] = (string) $order->id;

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

        $concept = json_decode($concept, true);

        $idCart = Cart::getCartIdByOrderId($idOrder);

        $sql = new DbQuery();
        $sql->select('`id_cart`');
        $sql->select(bqSQL(static::$definition['table']));
        $sql->from(bqSQL(static::$definition['table']), 'mdo');
        $sql->where('mdo.`id_cart` = '.(int) $idCart);

        try {
            if ($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                $deliveryOption = json_decode($result[static::$definition['table']], true);
                if (!empty($deliveryOption['concept']) && static::validateDeliveryOption($deliveryOption)) {
                    $deliveryOption['concept'] = $concept;
                    if (isset($deliveryOption['concept']['options']['delivery_type'])
                        && in_array($deliveryOption['concept']['options']['delivery_type'], array(4, 5))
                    ) {
                        $deliveryOption['type'] = 'pickup';
                    } else {
                        $deliveryOption['type'] = 'delivery';
                    }
                    try {
                        return Db::getInstance()->update(
                            bqSQL(static::$definition['table']),
                            array(
                                bqSQL(static::$definition['table']) => json_encode(
                                    $deliveryOption,
                                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                ),
                            ),
                            '`id_cart` = '.(int) $idCart
                        );
                    } catch (PrestaShopException $e) {
                        return false;
                    }
                } else {
                    $deliveryOption = array('concept' => $concept);
                    try {
                        return Db::getInstance()->update(
                            bqSQL(static::$definition['table']),
                            array(
                                bqSQL(static::$definition['table']) => json_encode(
                                    $deliveryOption,
                                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                                ),
                            ),
                            '`id_cart` = '.(int) $idCart
                        );
                    } catch (PrestaShopException $e) {
                        return false;
                    }
                }
            }
        } catch (PrestaShopException $e) {
            return false;
        }

        $deliveryOption = json_encode(
            array(
                'concept' => $concept,
            ),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        try {
            return Db::getInstance()->insert(
                bqSQL(static::$definition['table']),
                array(
                    bqSQL(static::$definition['table']) => $deliveryOption,
                    'id_cart'                         => (int) $idCart,
                )
            );
        } catch (PrestaShopException $e) {
            return false;
        }
    }

    /**
     * Calculates the next delivery date
     *
     * If a time is passed, this function will only add up the days, keeping the exact time intact
     *
     * @param string $date Shipping date (format: `Y-m-d H:i:s`)
     *
     * @return string (format: `Y-m-d H:i:s`)
     */
    public static function getDeliveryDay($date)
    {
        $deliveryDate = new DateTime($date);

        $holidays = static::getHolidaysForYear(date('Y', strtotime($date)));

        do {
            try {
                $deliveryDate->add(new DateInterval('P1D'));
            } catch (Exception $e) {
            }
        } while (in_array($deliveryDate->format('Y-m-d'), $holidays) || $deliveryDate->format('w') == 0);

        return $deliveryDate->format('Y-m-d H:i:s');
    }

    /**
     * Get preferred delivery day from delivery option
     *
     * @param array $option
     *
     * @return string
     */
    public static function getPreferredDeliveryDay($option)
    {
        if (isset($option['data']['date'])) {
            $deliveryDate = $option['data']['date'];

            if (isset($option['data']['time'][0]['start'])) {
                $deliveryDate .= " {$option['data']['time'][0]['start']}";
            } else {
                $deliveryDate .= ' 15:00:00';
            }
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
     */
    public static function getPreferredPickup($option)
    {
        if (isset($option['data']['location_code'])) {
            return "{$option['data']['location']}, {$option['data']['street']} {$option['data']['number']}".
            ", {$option['data']['city']}";
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
        return isset($option['type']) && $option['type'] === 'pickup';
    }

    /**
     * Get an array with all Dutch holidays for the given year
     *
     * @param string $year
     *
     * @return array
     *
     * Credits to @tvlooy (https://gist.github.com/tvlooy/1894247)
     */
    protected static function getHolidaysForYear($year)
    {
        // Avoid holidays
        // Fixed
        $nieuwjaar = new DateTime($year.'-01-01');
        $eersteKerstDag = new DateTime($year.'-12-25');
        $tweedeKerstDag = new DateTime($year.'-12-25');
        $koningsdag = new DateTime($year.'-04-27');
        // Dynamic
        $pasen = new DateTime();
        $pasen->setTimestamp(easter_date($year)); // thanks PHP!
        $paasMaandag = clone $pasen;
        try {
            $paasMaandag->add(new DateInterVal('P1D'));
        } catch (Exception $e) {
        }
        $hemelvaart = clone $pasen;
        try {
            $hemelvaart->add(new DateInterVal('P39D'));
        } catch (Exception $e) {
        }
        $pinksteren = clone $hemelvaart;
        try {
            $pinksteren->add(new DateInterVal('P10D'));
        } catch (Exception $e) {
        }
        $pinksterMaandag = clone $pinksteren;
        try {
            $pinksterMaandag->add(new DateInterVal('P1D'));
        } catch (Exception $e) {
        }

        $holidays = array(
            $nieuwjaar->format('Y-m-d'),
            $pasen->format('Y-m-d'),
            $koningsdag->format('Y-m-d'),
            $paasMaandag->format('Y-m-d'),
            $hemelvaart->format('Y-m-d'),
            $pinksteren->format('Y-m-d'),
            $pinksterMaandag->format('Y-m-d'),
            $eersteKerstDag->format('Y-m-d'),
            $tweedeKerstDag->format('Y-m-d'),
        );

        return $holidays;
    }

    /**
     * Delivery option validator
     *
     * @param array $deliveryOption
     * @param bool  $autofix        Try to restore the delivery option if possible
     *
     * @return bool
     */
    protected static function validateDeliveryOption(&$deliveryOption, $autofix = false)
    {
        // Skip concepts without basic info
        if (!isset($deliveryOption['concept']['recipient'])
            || !isset($deliveryOption['concept']['recipient']['cc'])
            || !isset($deliveryOption['concept']['recipient']['city'])
            || !isset($deliveryOption['concept']['recipient']['street'])
            || !isset($deliveryOption['concept']['recipient']['person'])) {
            return false;
        }

        // Skip concepts in NL,BE,DE w/o postcode info
        if (in_array(Tools::strtolower($deliveryOption['concept']['recipient']['cc']), array('nl', 'be', 'de'))) {
            if (!isset($deliveryOption['concept']['recipient']['number'])
                || !isset($deliveryOption['concept']['recipient']['postal_code'])) {
                return false;
            }
        }

        // Fix missing `delivery_type`
        if (isset($deliveryOption['concept']['options']['delivery_date'])
            && $deliveryOption['concept']['options']['delivery_date']) {
            if (!isset($deliveryOption['concept']['options']['delivery_type'])
                || !$deliveryOption['concept']['options']['delivery_type']
            ) {
                if ($autofix) {
                    if (isset($deliveryOption['data']['type']) && $deliveryOption['data']['type'] === 'delivery') {
                        $deliveryOption['concept']['options']['delivery_type'] = 2;
                    } elseif (isset($deliveryOption['data']['type']) && $deliveryOption['data']['type'] === 'pickup') {
                        $deliveryOption['concept']['options']['delivery_type'] = 4;
                    } else {
                        $deliveryOption['concept']['options']['delivery_type'] = 2;
                    }
                    if (isset($deliveryOption['data']['time'][0]['type'])) {
                        $deliveryOption['data']['time'][0]['type'] = $deliveryOption['concept']['options']['delivery_type'];
                        switch ($deliveryOption['data']['time'][0]['type']) {
                            case 2:
                                $deliveryOption['data']['time'][0]['price_comment'] = 'standard';
                                break;
                            case 4:
                                $deliveryOption['data']['time'][0]['price_comment'] = 'retail';
                                break;
                        }
                    }
                } else {
                    return false;
                }
            }
        }

        // Fix `delivery_date`s in the past
        $checkDate = null;
        if (!empty($deliveryOption['concept']['options']['delivery_date'])) {
            $checkDate = date('Y-m-d', strtotime($deliveryOption['concept']['options']['delivery_date']));
        } elseif (!empty($deliveryOption['data']['date'])) {
            $checkDate = date('Y-m-d', strtotime($deliveryOption['data']['date']));
        }

        if ($checkDate && $checkDate <= date('Y-m-d')) {
            // Restore the original delivery type when available (in order to survive Saturdays, and Mondays in particular, brrr)
            if (isset($deliveryOption['old_data']['time'][0]['type'])) {
                $deliveryOption['concept']['options']['delivery_type'] = $deliveryOption['old_data']['time'][0]['type'];
            }

            // Copy a deep clone of data to old_data
            $deliveryOption['old_data'] = $deliveryOption['data'];

            $deliveryOption['concept']['options']['delivery_date'] = $newDeliveryDate = date('Y-m-d', strtotime(static::getDeliveryDay(date('Y-m-d H:i:s'))));
            if (in_array($deliveryOption['concept']['options']['delivery_type'], array(1, 3, 5))) {
                $deliveryOption['concept']['options']['delivery_date'] = $newDeliveryDate;
            }

            // Reset date in data if set
            if (isset($deliveryOption['data']['date'])) {
                $deliveryOption['data']['date'] = $newDeliveryDate;
            }

            // Correct delivery type if necessary
            if (in_array(date('D', strtotime($newDeliveryDate)), array('Mon', 'Sat'))) {
                if (in_array($deliveryOption['concept']['options']['delivery_type'], array(1, 3))) {
                    $deliveryOption['concept']['options']['delivery_type'] = 2;
                    if (isset($deliveryOption['data']['time'][0])) {
                        $deliveryOption['data']['time'][0]['start'] = '08:00:00';
                        $deliveryOption['data']['time'][0]['end'] = '21:00:00';
                        $deliveryOption['data']['time'][0]['price_comment'] = 'standard';
                    }
                    $deliveryOption['extraOptions']['removedSpecialOption'] = true;

                } elseif (in_array($deliveryOption['concept']['options']['delivery_type'], array(5))) {
                    $deliveryOption['concept']['options']['delivery_type'] = 4;
                    if (isset($deliveryOption['data']['time'][0])) {
                        $deliveryOption['data']['time'][0]['start'] = '08:00:00';
                        $deliveryOption['data']['time'][0]['end'] = '21:00:00';
                        $deliveryOption['data']['time'][0]['price_comment'] = 'retail';
                    }
                    $deliveryOption['extraOptions']['removedSpecialOption'] = true;
                }
                // Set the new type in data
                if (isset($deliveryOption['data']['time'][0]['type'])) {
                    $deliveryOption['data']['time'][0]['type'] = $deliveryOption['concept']['options']['delivery_type'];
                }
            }

            $deliveryOption['extraOptions']['moved'] = true;
        }

        return true;
    }
}

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
            'id_cart'                  => array('type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true,  'default' => '0', 'db_type' => 'INT(11) UNSIGNED'),
            'myparcel_delivery_option' => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => true,                    'db_type' => 'TEXT'),
            'country_iso'              => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'CHAR(2)'),
            'company'                  => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(255)'),
            'name'                     => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'TEXT'),
            'postcode'                 => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(16)'),
            'house_number'             => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(16)'),
            'house_number_add'         => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(16)'),
            'street1'                  => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'TEXT'),
            'street2'                  => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'TEXT'),
            'email'                    => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(255)'),
            'phone'                    => array('type' => self::TYPE_STRING, 'validate' => 'isString',      'required' => false,                   'db_type' => 'VARCHAR(255)'),
        ),
    );
    /** @var int $id_cart */
    public $id_cart;
    /** @var string $myparcel_delivery_option */
    public $myparcel_delivery_option;
    /** @var string $country_iso */
    public $country_iso;
    /** @var string $company */
    public $company;
    /** @var string $name */
    public $name;
    /** @var string $postcode */
    public $postcode;
    /** @var string $house_number */
    public $house_number;
    /** @var string $house_number_add */
    public $house_number_add;
    /** @var string $street1 */
    public $street1;
    /** @var string $street2 */
    public $street2;
    /** @var string $email */
    public $email;
    /** @var string $phone */
    public $phone;
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
        $sql->from(bqSQL(self::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getrow($sql);

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
        $sql->from(bqSQL(self::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql, $cache);

        if ($result) {
            return Tools::jsonDecode($result);
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
        $sql = new DbQuery();
        $sql->select('`id_cart`');
        $sql->from(bqSQL(self::$definition['table']));
        $sql->where('`id_cart` = '.(int) $idCart);

        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            return (bool) Db::getInstance()->update(
                bqSQL(self::$definition['table']),
                array(
                    'myparcel_delivery_option' => $deliveryOption,
                ),
                '`id_cart` = '.(int) $idCart
            );
        } else {
            return (bool) Db::getInstance()->insert(
                bqSQL(self::$definition['table']),
                array(
                    'myparcel_delivery_option' => $deliveryOption,
                    'id_cart'                  => (int) $idCart,
                )
            );
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
        $sql->from(bqSQL(self::$definition['table']), 'mdo');
        $sql->innerJoin('orders', 'o', 'mdo.`id_cart` = o.`id_cart`');
        $sql->innerJoin('address', 'a', 'o.`id_address_delivery` = a.`id_address`');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $deliveryOptions = array();
        foreach ($results as $result) {
            $deliveryOption = (object) array(
                'concept'  => Tools::jsonDecode($result['myparcel_delivery_option']),
                'id_order' => (string) $result['id_order'],
            );

            if (!$deliveryOption->concept || !self::validateDeliveryOption($deliveryOption, true)) {
                $order = new Order($result['id_order']);
                $address = new Address($order->id_address_delivery);
                $cart = new Cart($order->id_cart);
                $deliveryOption->concept = (object) array('concept' => self::createConcept($order, self::getByOrder($order), $address, self::checkMailboxPackage($cart)));
            }

            if ($deliveryOption->concept) {
                $deliveryOptions[] = $deliveryOption;
            }

            // Remove ID from range array
            if (($key = array_search($result['id_order'], $range)) !== false) {
                unset($range[$key]);
            }
        }

        if (!empty($range)) {
            $deliveryOptions = array_merge($deliveryOptions, self::getConceptsByOrderIds($range));
        }

        return $deliveryOptions;
    }

    /**
     * @param Order                       $order
     * @param bool|MyParcelDeliveryOption $deliveryOption
     * @param Address                     $address
     * @param bool                        $mailboxPackage
     *
     * @return null|object
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
                $mailboxPackage = self::checkMailboxPackage(new Cart($order->id_cart));
            }
        }


        $countryIso = Tools::strtolower(Country::getIsoById($address->id_country));

        if (isset($deliveryOption->type) && $deliveryOption->type === 'pickup' && in_array($countryIso, array('nl', 'be'))) {
            return self::createPickupConcept($address, $deliveryOption, $order);
        } elseif ($countryIso === 'nl') {
            return self::createNationalConcept($address, $deliveryOption, $order, $mailboxPackage);
        } else {
            return self::createInternationalConcept($address, $order);
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
     * @return object
     *
     * @since 2.0.0
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
        if (isset($deliveryOption->type) && $deliveryOption->type === 'pickup') {
            $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] = MyParcel::TYPE_PARCEL;
        }

        preg_match('/(^.*?)(\d+)(.*?$)/', trim($address->address1.' '.$address->address2), $matches);
        $street = (isset($matches[1]) ? $matches[1] : '');
        $houseNumber = (isset($matches[2]) ? $matches[2] : '');
        if (isset($matches[3])) {
            $houseNumber .= $matches[3];
        }

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
                    $insuranceAmount = (int) $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT] * 100;
                    break;
                default:
                    $insuranceAmount = 0;
                    break;
            }
        } else {
            $insuranceAmount = 0;
        }

        if (isset($deliveryOption->extraOptions->recipientOnly)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = $deliveryOption->extraOptions->recipientOnly === 'true';
        }
        if (isset($deliveryOption->extraOptions->signed)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = $deliveryOption->extraOptions->signed === 'true';
        }

        $options = array(
            'package_type'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] ?: 1,
            'delivery_type'     => (int) $deliveryOption->data->time[0]->type,
            'delivery_date'     => (string) date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date)),
            'only_recipient'    => (int) $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY],
            'signature'         => (int) $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED],
            'insurance'         => (object) array(
                'amount'   => $insuranceAmount,
                'currency' => 'EUR',
            ),
            'large_format'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE],
            'label_description' => self::getLabelConcept($order),
        );

        if (isset($deliveryOption->data->date) && $deliveryOption->data->date) {
            $options['delivery_date'] = date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date));
        }
        if (isset($deliveryOption->data->type) && $deliveryOption->data->type) {
            $options['delivery_type'] = (int) $deliveryOption->data->type;
        }

        return (object) array(
            'recipient' => (object) array(
                'cc'          => Tools::strtoupper(Country::getIsoById($address->id_country)),
                'city'        => $address->city,
                'street'      => $street,
                'number'      => $houseNumber,
                'postal_code' => $address->postcode,
                'company'     => $address->company,
                'person'      => $address->firstname.' '.$address->lastname,
                'phone'       => $configuration[MyParcel::LINK_PHONE] ? ($address->phone_mobile ? $address->phone_mobile : $address->phone) : '',
                'email'       => ($configuration[MyParcel::LINK_EMAIL]) ? $email : '',
            ),
            'options'   => (object) $options,
            'pickup' => (object) array(
                'postal_code'       => (string) $deliveryOption->data->postal_code,
                'street'            => (string) $deliveryOption->data->street,
                'city'              => (string) $deliveryOption->data->city,
                'number'            => (string) $deliveryOption->data->number,
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

        $label = Configuration::get(MyParcel::LABEL_DESCRIPTION);
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
     * @return null|object
     *
     * @since 2.0.0
     */
    public static function createNationalConcept($address, $deliveryOption = null, $order = null, $mailboxPackage)
    {
        $email = '';
        if ($order) {
            $customer = new Customer($order->id_customer);

            if (Validate::isLoadedObject($customer)) {
                $email = $customer->email;
            }
        }

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
        if (isset($deliveryOption->type) && $deliveryOption->type === 'delivery') {
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

        preg_match('/(^.*?)(\d+)(.*?$)/', trim($address->address1.' '.$address->address2), $matches);
        $street = (isset($matches[1]) ? $matches[1] : '');
        $houseNumber = (isset($matches[2]) ? $matches[2] : '');
        if (isset($matches[3])) {
            $houseNumber .= $matches[3];
        }

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
                    $insuranceAmount = (int) $configuration[MyParcel::DEFAULT_CONCEPT_INSURED_AMOUNT] * 100;
                    break;
                default:
                    $insuranceAmount = 0;
                    break;
            }
        } else {
            $insuranceAmount = 0;
        }

        if (isset($deliveryOption->type)) {
            switch ($deliveryOption->type) {
                default:
                    break;
            }
        }
        if (isset($deliveryOption->extraOptions->recipientOnly) && $deliveryOption->extraOptions->recipientOnly === 'true'
        || (isset($deliveryOption->data->price_comment) && ($deliveryOption->data->price_comment === 'morning' || in_array($deliveryOption->data->price_comment, array('night', 'avond', 'evening'))))) {
            $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY] = true;
        }
        if (isset($deliveryOption->extraOptions->signed)) {
            $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED] = $deliveryOption->extraOptions->signed === 'true';
        }

        $options = array(
            'package_type'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_PARCEL_TYPE] ?: 1,
            'only_recipient'    => (int) $configuration[MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY],
            'signature'         => (int) $configuration[MyParcel::DEFAULT_CONCEPT_SIGNED],
            'insurance'         => (object) array(
                'amount'   => $insuranceAmount,
                'currency' => 'EUR',
            ),
            'large_format'      => (int) $configuration[MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE],
            'label_description' => self::getLabelConcept($order),
        );

        if (isset($deliveryOption->data->date) && $deliveryOption->data->date) {
            $options['delivery_date'] = date('Y-m-d 00:00:00', strtotime($deliveryOption->data->date));
        }
        if (isset($deliveryOption->data->type) && $deliveryOption->data->type) {
            $options['delivery_type'] = (int) $deliveryOption->data->type;
        }
        if ($configuration[MyParcel::DEFAULT_CONCEPT_RETURN]) {
            $options['return'] = 1;
        }

        return (object) array(
            'recipient' => (object) array(
                'cc'          => Tools::strtoupper(Country::getIsoById($address->id_country)),
                'city'        => $address->city,
                'street'      => $street,
                'number'      => $houseNumber,
                'postal_code' => $address->postcode,
                'company'     => $address->company,
                'person'      => $address->firstname.' '.$address->lastname,
                'phone'       => $configuration[MyParcel::LINK_PHONE] ? ($address->phone_mobile ? $address->phone_mobile : $address->phone) : '',
                'email'       => ($configuration[MyParcel::LINK_EMAIL]) ? $email : '',
            ),
            'options'   => (object) $options,
            'carrier'   => 1,
        );
    }

    /**
     * Create concept for international shipments
     *
     * @param Address    $address
     * @param Order|null $order
     *
     * @return object
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

        if (Configuration::get(MyParcel::DEFAULT_CONCEPT_INSURED)) {
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
            $insuranceAmount = 0;
        }

        return (object) array(
            'recipient'           => (object) array(
                'cc'          => Tools::strtoupper(Country::getIsoById($address->id_country)),
                'city'        => $address->city,
                'street'      => trim($address->address1.' '.$address->address2),
                'postal_code' => $address->postcode,
                'company'     => $address->company,
                'person'      => $address->firstname.' '.$address->lastname,
                'phone'       => Configuration::get(MyParcel::LINK_PHONE) ? ($address->phone ? $address->phone : $address->phone_mobile) : '',
                'email'       => (Configuration::get(MyParcel::LINK_EMAIL)) ? $email : '',
            ),
            'options'             => (object) array(
                'package_type'      => 1,
                'label_description' => self::getLabelConcept($order),
                'large_format'      => (int) Configuration::get(MyParcel::DEFAULT_CONCEPT_LARGE_PACKAGE),
                'insurance'         => (object) array(
                    'amount'   => $insuranceAmount,
                    'currency' => 'EUR',
                ),
            ),
            'customs_declaration' => (object) array(
                'contents' => 1,
                'invoice'  => '',
                'weight'   => 0,
                'items'    => array(),
            ),
            'physical_properties' => (object) array(
                'weight' => 0,
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
        $sql->from(bqSQL(self::$definition['table']), 'mdo');
        $sql->innerJoin('orders', 'o', 'o.`id_cart` = mdo.`id_cart`');
        $sql->where('o.`id_order` = '.(int) $idOrder);

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

        if ($result) {
            return $result;
        }

        $concepts = self::getConceptsByOrderIds(array($idOrder));
        if (is_array($concepts)) {
            return Tools::jsonEncode($concepts[0]);
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
        $sql->select('o.`id_order`, a.*');
        $sql->from('orders', 'o');
        $sql->innerJoin('address', 'a', 'o.`id_address_delivery` = a.`id_address`');
        $sql->where('o.`id_order` IN ('.implode(',', $range).')');

        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $concepts = array();

        foreach ($results as $result) {
            $concept = array();

            $address = new Address();
            $address->firstname = $result['firstname'];
            $address->lastname = $result['lastname'];
            $address->postcode = $result['postcode'];
            $address->address1 = $result['address1'];
            $address->address2 = $result['address2'];
            $address->city = $result['city'];
            $address->phone = $result['phone'];
            $address->phone_mobile = $result['phone_mobile'];
            $address->id_country = $result['id_country'];

            $concept['concept'] = (object) array(
                'concept' => self::createConcept(new Order($result['id_order'])),
            );
            $concept['id_order'] = (string) $result['id_order'];

            $concepts[] = $concept;
        }

        return $concepts;
    }

    /**
     * Save concept
     *
     * @param Order  $order
     * @param string $concept
     *
     * @return bool
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

        $idCart = Cart::getCartIdByOrderId($idOrder);

        $sql = new DbQuery();
        $sql->select(bqSQL(self::$definition['table']));
        $sql->from(bqSQL(self::$definition['table']), 'mdo');
        $sql->where('mdo.`id_cart` = '.(int) $idCart);

        if ($result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql)) {
            $deliveryOption = Tools::jsonDecode($result);
            $deliveryOption->concept = Tools::jsonDecode($concept);
            $deliveryOption = Tools::jsonEncode($deliveryOption);

            return Db::getInstance()->update(
                bqSQL(self::$definition['table']),
                array(
                    bqSQL(self::$definition['table']) => $deliveryOption,
                ),
                '`id_cart` = '.(int) $idCart
            );
        }

        $deliveryOption = (object) array(
            'concept' => Tools::jsonDecode($concept),
        );
        $deliveryOption = Tools::jsonEncode($deliveryOption);

        return Db::getInstance()->insert(
            bqSQL(self::$definition['table']),
            array(
                bqSQL(self::$definition['table']) => $deliveryOption,
                'id_cart'                         => (int) $idCart,
            )
        );
    }

    /**
     * Delivery option validator
     *
     * @param object $deliveryOption
     * @param bool   $autofix        Try to restore the delivery option if possible
     *
     * @return bool
     */
    protected static function validateDeliveryOption($deliveryOption, $autofix = false)
    {
        if (!isset($deliveryOption->concept->concept->recipient)
            || !isset($deliveryOption->concept->concept->recipient->cc)
            || !isset($deliveryOption->concept->concept->recipient->city)
            || !isset($deliveryOption->concept->concept->recipient->street)
            || !isset($deliveryOption->concept->concept->recipient->number)
            || !isset($deliveryOption->concept->concept->recipient->person)) {
            return false;
        }

        if ($deliveryOption->concept->concept->recipient->cc === 'NL') {
            if (!isset($deliveryOption->concept->concept->recipient->number)
                || !isset($deliveryOption->concept->concept->recipient->postal_code)) {
                return false;
            }
        }

        if (isset($deliveryOption->concept->concept->options->delivery_date) && $deliveryOption->concept->concept->options->delivery_date) {
            if (!isset($deliveryOption->concept->concept->options->delivery_type) || !$deliveryOption->concept->concept->options->delivery_type) {
                if ($autofix) {
                    if (isset($deliveryOption->concept->data->type) && $deliveryOption->concept->data->type === 'delivery') {
                        $deliveryOption->concept->concept->options->delivery_type = 2;
                    } elseif (isset($deliveryOption->concept->data->type) && $deliveryOption->concept->data->type === 'pickup') {
                        $deliveryOption->concept->concept->options->delivery_type = 4;
                    } else {
                        $deliveryOption->concept->concept->options->delivery_type = 2;
                    }
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}

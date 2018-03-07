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
 * Class MyParcelTools
 *
 * @since 2.1.0
 */
class MyParcelTools
{
    /**
     * Prints the preferred delivery date on a HelperList
     *
     * @param int   $id
     * @param array $tr
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     * @codingStandardsIgnoreStart
     */
    public static function printOrderGridPreference($id, $tr)
    {
        // @codingStandardsIgnoreEnd
        $option = json_decode($tr['myparcel_delivery_option'], true);

        if (in_array($tr['myparcel_country_iso'], array('NL', 'BE'))) {
            $shippingDaysRemaining = (int) MyParcelDeliveryOption::getShippingDaysRemaining(
                date('Y-m-d 00:00:00'),
                $tr['myparcel_date_delivery']
            );
        } else {
            $shippingDaysRemaining = 0;
        }

        if ($tr['myparcel_country_iso'] !== 'NL' || $tr['myparcel_date_delivery'] <= '1970-01-02 00:00:00') {
            $badgeType = 'info';
        } elseif ($shippingDaysRemaining < 0) {
            $badgeType = 'danger';
        } elseif ($shippingDaysRemaining > 0) {
            $badgeType = 'warning';
        } else {
            $badgeType = 'success';
        }

        $deliveryDate = date('Y-m-d', strtotime($tr['myparcel_date_delivery']));
        if (isset($option['data']['date'])
            && isset($option['data']['time'][0]['type'])
            && $option['data']['time'][0]['type'] < 4
        ) {
            $start = Tools::substr(
                $option['data']['time'][0]['start'],
                0,
                Tools::strlen($option['data']['time'][0]['start']) - 3
            );
            $end = Tools::substr(
                $option['data']['time'][0]['end'],
                0,
                Tools::strlen($option['data']['time'][0]['end']) - 3
            );

            $deliveryDate .= " {$start}-{$end}";
        } else {
            $deliveryDate .= ' '.date('H:i', strtotime($tr['myparcel_date_delivery']));
        }

        Context::getContext()->smarty->assign(array(
            'tr'           => $tr,
            'deliveryData' => isset($option['data']) ? $option['data'] : null,
            'deliveryDate' => $deliveryDate,
            'badgeType'    => $badgeType,
            'shippingDaysRemaining' => $shippingDaysRemaining,
        ));

        $location = 'views/templates/admin/ordergrid/icon-delivery-date.tpl';
        $module = Module::getInstanceByName('myparcel');
        $reflection = new ReflectionClass('MyParcel');
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $module->display(
            $moduleOverridden ? _PS_MODULE_DIR_.'myparcel/myparcel.php' : $reflection->getFileName(),
            $location
        );
    }

    /**
     * Prints the track & trace progress bar on a HelperList
     *
     * @param int   $id
     * @param array $tr
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     * @codingStandardsIgnoreStart
     */
    public static function printMyParcelTrackTrace($id, $tr)
    {
        // @codingStandardsIgnoreEnd
        Context::getContext()->smarty->assign(array(
            'tr'           => $tr,
        ));

        $location = 'views/templates/admin/ordergrid/icon-tracktrace.tpl';
        $module = Module::getInstanceByName('myparcel');
        $reflection = new ReflectionClass('MyParcel');
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $module->display(
            $moduleOverridden ? _PS_MODULE_DIR_.'myparcel/myparcel.php' : $reflection->getFileName(),
            $location
        );
    }

    /**
     * Prints the MyParcel concept icon on a HelperList
     *
     * @param int   $id
     * @param array $tr
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     * @codingStandardsIgnoreStart
     */
    public static function printMyParcelIcon($id, $tr)
    {
        // @codingStandardsIgnoreEnd
        Context::getContext()->smarty->assign(array(
            'tr'           => $tr,
        ));

        $location = 'views/templates/admin/ordergrid/icon-concept.tpl';
        $module = Module::getInstanceByName('myparcel');
        $reflection = new ReflectionClass('MyParcel');
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $module->display(
            $moduleOverridden ? _PS_MODULE_DIR_.'myparcel/myparcel.php' : $reflection->getFileName(),
            $location
        );
    }

    /**
     * Prints the cut off badges on a HelperList
     *
     * @param int   $id
     * @param array $tr
     *
     * @return string
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     * @codingStandardsIgnoreStart
     */
    public static function printCutOffItems($id, $tr)
    {
        // @codingStandardsIgnoreEnd
        Context::getContext()->smarty->assign(array(
            'tr'           => $tr,
        ));

        $location = 'views/templates/admin/cutofflistitem.tpl';
        $module = Module::getInstanceByName('myparcel');
        $reflection = new ReflectionClass('MyParcel');
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $module->display(
            $moduleOverridden ? _PS_MODULE_DIR_.'myparcel/myparcel.php' : $reflection->getFileName(),
            $location
        );
    }

    /**
     * @param int $idCountry
     *
     * This function tries to grab the string that begins with address1 and ends with whatever
     *
     * The MyParcel module requires your address format for NL, BE & DE to be one that can be literally printed
     * onto an envelope, meaning that every streetname is followed by a housenumber (+ extension).
     * This means that if you are using `address2` as the housenumber field it needs to be on the same line
     * as `address1`. The same goes for any custom field you might have added to the `Address` class.
     *
     * Depending on the amount of properties found, the module assumes the following:
     * - `address1` only: `address1` contains the streetname, housenumer + extension
     * - `address1` + one custom field: `address1` has the streetname, the custom field houseno. + extension
     * - `address2` + two custom fields: `address1` streetname, first custom field houseno., second the extension
     * Any subsequent field is ignored by the module and can result in missing addresses on shipping labels
     *
     * If you want to ship to any country other than NL, BE or DE, make sure `address1` contains
     * the streetname, housenumber and optionally the housenumber extension.
     *
     * @return array 0 = always `address1`, 1 and 2 could resp. be the field for housenumber + extension
     */
    public static function getAddressLineFields($idCountry)
    {
        preg_match(MyParcel::ADDRESS_FORMAT_REGEX, AddressFormat::getAddressCountryFormat($idCountry), $fields);

        return array_pad(array_splice($fields, 1), 3, null);
    }

    /**
     * Get address line 1
     *
     * @param Address $address
     *
     * @return string
     */
    public static function getAddressLine(Address $address)
    {
        $addressLine = '';
        foreach (static::getAddressLineFields($address->id_country) as $field) {
            if ($field && !empty($address->{$field})) {
                $addressLine .= ' '.$address->{$field};
            }
        }

        return trim($addressLine);
    }

    /**
     * Get address line 2
     *
     * @param Address $address
     *
     * @return string
     */
    public static function getAdditionalAddressLine(Address $address)
    {
        $fields = static::getAddressLineFields($address->id_country);
        if (!in_array('address2', $fields)) {
            return $address->address2;
        }

        return '';
    }

    /**
     * Get Customer Address by Customer ID and pickup location code
     *
     * @param int    $idCustomer
     * @param string $locationCode
     *
     * @return Address
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getCustomerAddress($idCustomer, $locationCode)
    {
        $address = new Address();
        $sql = new DbQuery();
        $sql->select('a.*');
        $sql->from('address', 'a');
        $sql->where('a.`id_customer` = '.(int) $idCustomer);
        $sql->where('a.`alias` = \'myparcel-'.pSQL($locationCode).'\'');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!empty($result)) {
            $address->hydrate($result);
        }

        return $address;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public static function getInvoiceSuggestion(Order $order)
    {
        try {
            $invoice = $order->getInvoicesCollection()->getFirst();
            if ($invoice instanceof OrderInvoice) {
                return $invoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
            }
        } catch (PrestaShopException $e) {
        }

        return $order->reference;
    }

    /**
     * @param Order $order
     *
     * @return int
     */
    public static function getWeightSuggestion(Order $order)
    {
        try {
            $weight = ceil($order->getTotalWeight());
        } catch (PrestaShopException $e) {
            $weight = 1;
        }
        if ($weight < 1) {
            $weight = 1;
        }

        return $weight;
    }
}

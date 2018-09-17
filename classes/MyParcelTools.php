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
if (!function_exists('smarty_modifier_escape')) {
    if (file_exists(dirname(__FILE__).'/../../../vendor/smarty/smarty/libs/plugins/modifier.escape.php')) {
        require_once dirname(__FILE__).'/../../../vendor/smarty/smarty/libs/plugins/modifier.escape.php';
    } elseif (file_exists(dirname(__FILE__).'/../../../tools/smarty/plugins/modifier.escape.php')) {
        require_once dirname(__FILE__).'/../../../tools/smarty/plugins/modifier.escape.php';
    }
}

/**
 * Class MyParcelTools
 *
 * @since 2.1.0
 */
class MyParcelTools
{
    /** @var string $thisModule */
    public static $thisModule = 'myparcel';
    /** @var string $thisModuleTable */
    public static $thisModuleTable = 'myparcel';
    /** @var string $thisModuleClass */
    public static $thisModuleClass = 'MyParcel';
    /** @var array $supportedModules */
    public static $supportedModules = array('myparcel', 'myparcelbpost', 'postnl');

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
        $thisModule = static::$thisModule;
        $thisModuleClass = static::$thisModuleClass;
        $thisModuleTable = static::$thisModuleTable;
        $supportedModules = static::$supportedModules;

        $output = '';
        $supportedCarrierModules = array_filter(Hook::getHookModuleExecList('actionAdminOrdersListingFieldsModifier'), function ($item) use ($supportedModules) {
            return in_array($item['module'], $supportedModules);
        });
        $lastSupportedCarrierModule = end($supportedCarrierModules);
        reset($supportedCarrierModules); // Reset array pointer
        if (!empty($supportedCarrierModules) && $lastSupportedCarrierModule['module'] === static::$thisModule) {
            foreach ($supportedCarrierModules as $supportedCarrierModule) {
                if ($supportedCarrierModule['module'] === static::$thisModule) {
                    continue;
                }

                /** @var MyParcel $module */
                $module = Module::getInstanceByName($supportedCarrierModule['module']);
                if (!Validate::isLoadedObject($module)) {
                    continue;
                }

                $columns = $module->getColumns();
                $result = call_user_func($columns['delivery_date'], $id, $tr);
                if ($result !== '--') {
                    $output .= $result;
                }
            }
        }

        if ($tr["{$thisModuleTable}_date_delivery"] <= '1970-01-02 00:00:00') {
            return $output ?: '--';
        }

        $option = @json_decode($tr["{$thisModuleTable}_delivery_option"], true);

        if (in_array($tr["{$thisModuleTable}_country_iso"], array('NL', 'BE'))) {
            $shippingDaysRemaining = (int) MyParcelDeliveryOption::getShippingDaysRemaining(
                date('Y-m-d 00:00:00'),
                $tr["{$thisModuleTable}_date_delivery"]
            );
        } else {
            $shippingDaysRemaining = 0;
        }

        if ($tr["{$thisModuleTable}_country_iso"] !== 'NL') {
            $badgeType = 'info';
        } elseif ($shippingDaysRemaining < 0) {
            $badgeType = 'danger';
        } elseif ($shippingDaysRemaining > 0) {
            $badgeType = 'warning';
        } else {
            $badgeType = 'success';
        }

        $deliveryDate = date('Y-m-d', strtotime($tr["{$thisModuleTable}_date_delivery"]));
        if (isset($option['data']['date'])
            && isset($option['data']['time'][0]['type'])
            && $option['data']['time'][0]['type'] < 4
        ) {
            $startParts = array_pad(explode(':', Tools::substr(
                $option['data']['time'][0]['start'],
                0,
                Tools::strlen($option['data']['time'][0]['start'])
            )), 2, '00');
            $start = "{$startParts[0]}:{$startParts[1]}";
            $endParts = array_pad(explode(':', Tools::substr(
                $option['data']['time'][0]['end'],
                0,
                Tools::strlen($option['data']['time'][0]['end'])
            )), 2, '00');
            $end = "{$endParts[0]}:{$endParts[1]}";

            $deliveryDate .= " {$start}-{$end}";
        } else {
            $deliveryDate .= ' '.date('H:i', strtotime($tr["{$thisModuleTable}_date_delivery"]));
        }

        Context::getContext()->smarty->assign(array(
            'tr'                    => $tr,
            'deliveryData'          => isset($option['data']) ? $option['data'] : null,
            'deliveryDate'          => $deliveryDate,
            'badgeType'             => $badgeType,
            'shippingDaysRemaining' => $shippingDaysRemaining,
        ));

        $location = 'views/templates/admin/ordergrid/icon-delivery-date.tpl';
        $module = Module::getInstanceByName($thisModule);
        $reflection = new ReflectionClass($thisModuleClass);
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        if ($output) {
            return $output;
        }

        return $module->display(
            $moduleOverridden ? _PS_MODULE_DIR_."{$thisModule}/{$thisModule}.php" : $reflection->getFileName(),
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
        $thisModule = static::$thisModule;
        $thisModuleClass = static::$thisModuleClass;
        $supportedModules = static::$supportedModules;

        $output = '';
        $supportedCarrierModules = array_filter(Hook::getHookModuleExecList('actionAdminOrdersListingFieldsModifier'), function ($item) use ($supportedModules) {
            return in_array($item['module'], $supportedModules);
        });

        $lastSupportedCarrierModule = end($supportedCarrierModules);
        reset($supportedCarrierModules); // Reset array pointer
        if (!empty($supportedCarrierModules) && $lastSupportedCarrierModule['module'] === $thisModule) {
            foreach ($supportedCarrierModules as $supportedCarrierModule) {
                if ($supportedCarrierModule['module'] === $thisModule) {
                    continue;
                }

                /** @var MyParcel $module */
                $module = Module::getInstanceByName($supportedCarrierModule['module']);
                if (!Validate::isLoadedObject($module)) {
                    continue;
                }

                $columns = $module->getColumns();
                $result = call_user_func($columns['status'], $id, $tr);
                if ($result !== '--') {
                    $output .= $result;
                }
            }
        }

        Context::getContext()->smarty->assign(array(
            'tr' => $tr,
        ));

        $location = 'views/templates/admin/ordergrid/icon-tracktrace.tpl';
        $module = Module::getInstanceByName($thisModule);
        $reflection = new ReflectionClass($thisModuleClass);
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $output.$module->display(
                $moduleOverridden ? _PS_MODULE_DIR_."{$thisModule}/{$thisModule}.php" : $reflection->getFileName(),
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
        $thisModule = static::$thisModule;
        $thisModuleClass = static::$thisModuleClass;
        $supportedModules = static::$supportedModules;

        $output = '';

        $supportedCarrierModules = array_filter(Hook::getHookModuleExecList('actionAdminOrdersListingFieldsModifier'), function ($item) use ($supportedModules) {
            $module = Module::getInstanceByName($item['module']);
            if (!Validate::isLoadedObject($module)) {
                return false;
            }

            return in_array($item['module'], $supportedModules)
                && version_compare($module->version, '2.2.0', '>=');
        });
        $lastSupportedCarrierModule = end($supportedCarrierModules);
        reset($supportedCarrierModules); // Reset array pointer
        if (!empty($supportedCarrierModules) && $lastSupportedCarrierModule['module'] === $thisModule) {
            foreach ($supportedCarrierModules as $supportedCarrierModule) {
                if ($supportedCarrierModule['module'] === $thisModule) {
                    continue;
                }

                /** @var MyParcel $module */
                $module = Module::getInstanceByName($supportedCarrierModule['module']);
                if (!Validate::isLoadedObject($module)) {
                    continue;
                }

                $columns = $module->getColumns();
                $result = call_user_func($columns['concept'], $id, $tr);
                if ($result) {
                    $output .= $result;
                }
            }
        }

        Context::getContext()->smarty->assign(array(
            'tr' => $tr,
        ));

        $location = 'views/templates/admin/ordergrid/icon-concept.tpl';
        $module = Module::getInstanceByName($thisModule);
        $reflection = new ReflectionClass($thisModuleClass);
        $moduleOverridden = !file_exists(dirname($reflection->getFileName()).'/'.$location);

        return $output.$module->display(
                $moduleOverridden ? _PS_MODULE_DIR_."{$thisModule}/{$thisModule}.php" : $reflection->getFileName(),
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
     * Show all carrier names (replace 0 with shop name)
     *
     * @param string $name
     *
     * @return string
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function printCarrierName($name)
    {
        if (!$name && method_exists('Carrier', 'getCarrierNameFromShopName')) {
            return Carrier::getCarrierNameFromShopName();
        }

        return $name;
    }

    /**
     * Print (base64 encoded) log item
     *
     * @param string $content
     *
     * @return string
     *
     * @since 2.2.0
     */
    public static function printLogMessage($content)
    {
        if (base64_encode(base64_decode($content)) === $content) {
            return '<pre><code>'.smarty_modifier_escape(base64_decode($content), 'htmlall', 'UTF-8').'</code></pre>';
        }

        return $content;
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
            $weight = 1000;
        }
        if ($weight < 1000) {
            $weight = 1000;
        }

        return $weight;
    }

    /**
     * @return array
     *
     * @since 2.2.0
     */
    public static function getSupportedCountries()
    {
        return array(
            'data' => array(
                'countries' =>
                    array(
                        0 =>
                            array(
                                'NL' =>
                                    array(
                                        'label'  => 'Nederland',
                                        'region' => 'NL',
                                    ),
                                'AT' =>
                                    array(
                                        'label'  => 'Oostenrijk',
                                        'region' => 'EU',
                                    ),
                                'BE' =>
                                    array(
                                        'label'  => 'België',
                                        'region' => 'EU',
                                    ),
                                'BG' =>
                                    array(
                                        'label'  => 'Bulgarije',
                                        'region' => 'EU',
                                    ),
                                'CZ' =>
                                    array(
                                        'label'  => 'Tsjechië',
                                        'region' => 'EU',
                                    ),
                                'DE' =>
                                    array(
                                        'label'  => 'Duitsland',
                                        'region' => 'EU',
                                    ),
                                'DK' =>
                                    array(
                                        'label'  => 'Denemarken',
                                        'region' => 'EU',
                                    ),
                                'EE' =>
                                    array(
                                        'label'  => 'Estland',
                                        'region' => 'EU',
                                    ),
                                'ES' =>
                                    array(
                                        'label'  => 'Spanje',
                                        'region' => 'EU',
                                    ),
                                'FI' =>
                                    array(
                                        'label'  => 'Finland',
                                        'region' => 'EU',
                                    ),
                                'FR' =>
                                    array(
                                        'label'  => 'Frankrijk',
                                        'region' => 'EU',
                                    ),
                                'GB' =>
                                    array(
                                        'label'  => 'Verenigd Koninkrijk',
                                        'region' => 'EU',
                                    ),
                                'GR' =>
                                    array(
                                        'label'  => 'Griekenland',
                                        'region' => 'EU',
                                    ),
                                'HU' =>
                                    array(
                                        'label'  => 'Hongarije',
                                        'region' => 'EU',
                                    ),
                                'IE' =>
                                    array(
                                        'label'  => 'Ierland',
                                        'region' => 'EU',
                                    ),
                                'IT' =>
                                    array(
                                        'label'  => 'Italië',
                                        'region' => 'EU',
                                    ),
                                'LT' =>
                                    array(
                                        'label'  => 'Litouwen',
                                        'region' => 'EU',
                                    ),
                                'LU' =>
                                    array(
                                        'label'  => 'Luxemburg',
                                        'region' => 'EU',
                                    ),
                                'LV' =>
                                    array(
                                        'label'  => 'Letland',
                                        'region' => 'EU',
                                    ),
                                'MC' =>
                                    array(
                                        'label'  => 'Monaco',
                                        'region' => 'EU',
                                    ),
                                'PL' =>
                                    array(
                                        'label'  => 'Polen',
                                        'region' => 'EU',
                                    ),
                                'PT' =>
                                    array(
                                        'label'  => 'Portugal',
                                        'region' => 'EU',
                                    ),
                                'RO' =>
                                    array(
                                        'label'  => 'Roemenië',
                                        'region' => 'EU',
                                    ),
                                'SE' =>
                                    array(
                                        'label'  => 'Zweden',
                                        'region' => 'EU',
                                    ),
                                'SI' =>
                                    array(
                                        'label'  => 'Slovenië',
                                        'region' => 'EU',
                                    ),
                                'SK' =>
                                    array(
                                        'label'  => 'Slowakije',
                                        'region' => 'EU',
                                    ),
                                'AD' =>
                                    array(
                                        'label'  => 'Andora',
                                        'region' => 'EU',
                                    ),
                                'AL' =>
                                    array(
                                        'label'  => 'Albanië',
                                        'region' => 'EU',
                                    ),
                                'BA' =>
                                    array(
                                        'label'  => 'Bosnië-Herzegovina',
                                        'region' => 'EU',
                                    ),
                                'BY' =>
                                    array(
                                        'label'  => 'Wit-Rusland',
                                        'region' => 'EU',
                                    ),
                                'CH' =>
                                    array(
                                        'label'  => 'Zwitserland',
                                        'region' => 'EU',
                                    ),
                                'FO' =>
                                    array(
                                        'label'  => 'Faeröer Eilanden',
                                        'region' => 'EU',
                                    ),
                                'GG' =>
                                    array(
                                        'label'  => 'Guernsey',
                                        'region' => 'EU',
                                    ),
                                'GI' =>
                                    array(
                                        'label'  => 'Gibraltar',
                                        'region' => 'EU',
                                    ),
                                'GL' =>
                                    array(
                                        'label'  => 'Groenland',
                                        'region' => 'EU',
                                    ),
                                'HR' =>
                                    array(
                                        'label'  => 'Kroatië',
                                        'region' => 'EU',
                                    ),
                                'IC' =>
                                    array(
                                        'label'  => 'Canarische Eilanden',
                                        'region' => 'EU',
                                    ),
                                'IS' =>
                                    array(
                                        'label'  => 'IJsland',
                                        'region' => 'EU',
                                    ),
                                'JE' =>
                                    array(
                                        'label'  => 'Jersey',
                                        'region' => 'EU',
                                    ),
                                'LI' =>
                                    array(
                                        'label'  => 'Liechtenstein',
                                        'region' => 'EU',
                                    ),
                                'MD' =>
                                    array(
                                        'label'  => 'Moldavië',
                                        'region' => 'EU',
                                    ),
                                'ME' =>
                                    array(
                                        'label'  => 'Montenegro',
                                        'region' => 'EU',
                                    ),
                                'MK' =>
                                    array(
                                        'label'  => 'Macedonië',
                                        'region' => 'EU',
                                    ),
                                'NO' =>
                                    array(
                                        'label'  => 'Noorwegen',
                                        'region' => 'EU',
                                    ),
                                'RS' =>
                                    array(
                                        'label'  => 'Servië',
                                        'region' => 'EU',
                                    ),
                                'SM' =>
                                    array(
                                        'label'  => 'San Marino',
                                        'region' => 'EU',
                                    ),
                                'TR' =>
                                    array(
                                        'label'  => 'Turkije',
                                        'region' => 'EU',
                                    ),
                                'UA' =>
                                    array(
                                        'label'  => 'Oekraïne',
                                        'region' => 'EU',
                                    ),
                                'VA' =>
                                    array(
                                        'label'  => 'Vaticaanstad',
                                        'region' => 'EU',
                                    ),
                                'AE' =>
                                    array(
                                        'label'  => 'Ver. Arabische Emiraten',
                                        'region' => 'CD',
                                    ),
                                'AF' =>
                                    array(
                                        'label'  => 'Afghanistan',
                                        'region' => 'CD',
                                    ),
                                'AG' =>
                                    array(
                                        'label'  => 'Antigua en Barbuda',
                                        'region' => 'CD',
                                    ),
                                'AM' =>
                                    array(
                                        'label'  => 'Armenië',
                                        'region' => 'CD',
                                    ),
                                'AN' =>
                                    array(
                                        'label'  => 'Nederlandse Antillen',
                                        'region' => 'CD',
                                    ),
                                'AO' =>
                                    array(
                                        'label'  => 'Angola',
                                        'region' => 'CD',
                                    ),
                                'AQ' =>
                                    array(
                                        'label'  => 'Antarctica',
                                        'region' => 'CD',
                                    ),
                                'AR' =>
                                    array(
                                        'label'  => 'Argentinië',
                                        'region' => 'CD',
                                    ),
                                'AU' =>
                                    array(
                                        'label'  => 'Australië',
                                        'region' => 'CD',
                                    ),
                                'AW' =>
                                    array(
                                        'label'  => 'Aruba',
                                        'region' => 'CD',
                                    ),
                                'AZ' =>
                                    array(
                                        'label'  => 'Azerbeidzjan',
                                        'region' => 'CD',
                                    ),
                                'BB' =>
                                    array(
                                        'label'  => 'Barbados',
                                        'region' => 'CD',
                                    ),
                                'BD' =>
                                    array(
                                        'label'  => 'Bangladesh',
                                        'region' => 'CD',
                                    ),
                                'BF' =>
                                    array(
                                        'label'  => 'Burkina Faso',
                                        'region' => 'CD',
                                    ),
                                'BH' =>
                                    array(
                                        'label'  => 'Bahrein',
                                        'region' => 'CD',
                                    ),
                                'BI' =>
                                    array(
                                        'label'  => 'Burundi',
                                        'region' => 'CD',
                                    ),
                                'BJ' =>
                                    array(
                                        'label'  => 'Benin',
                                        'region' => 'CD',
                                    ),
                                'BM' =>
                                    array(
                                        'label'  => 'Bermuda',
                                        'region' => 'CD',
                                    ),
                                'BN' =>
                                    array(
                                        'label'  => 'Brunei Darussalam',
                                        'region' => 'CD',
                                    ),
                                'BO' =>
                                    array(
                                        'label'  => 'Bolivia',
                                        'region' => 'CD',
                                    ),
                                'BQ' =>
                                    array(
                                        'label'  => 'Bonaire, Sint Eustatius en Saba',
                                        'region' => 'CD',
                                    ),
                                'BR' =>
                                    array(
                                        'label'  => 'Brazilië',
                                        'region' => 'CD',
                                    ),
                                'BS' =>
                                    array(
                                        'label'  => 'Bahama’s',
                                        'region' => 'CD',
                                    ),
                                'BT' =>
                                    array(
                                        'label'  => 'Bhutan',
                                        'region' => 'CD',
                                    ),
                                'BW' =>
                                    array(
                                        'label'  => 'Botswana',
                                        'region' => 'CD',
                                    ),
                                'BZ' =>
                                    array(
                                        'label'  => 'Belize',
                                        'region' => 'CD',
                                    ),
                                'CA' =>
                                    array(
                                        'label'  => 'Canada',
                                        'region' => 'CD',
                                    ),
                                'CD' =>
                                    array(
                                        'label'  => 'Congo-Kinshasa',
                                        'region' => 'CD',
                                    ),
                                'CF' =>
                                    array(
                                        'label'  => 'Centraal-Afrikaanse Rep.',
                                        'region' => 'CD',
                                    ),
                                'CG' =>
                                    array(
                                        'label'  => 'Congo-Brazzaville',
                                        'region' => 'CD',
                                    ),
                                'CI' =>
                                    array(
                                        'label'  => 'Ivoorkust',
                                        'region' => 'CD',
                                    ),
                                'CL' =>
                                    array(
                                        'label'  => 'Chili',
                                        'region' => 'CD',
                                    ),
                                'CM' =>
                                    array(
                                        'label'  => 'Kameroen',
                                        'region' => 'CD',
                                    ),
                                'CN' =>
                                    array(
                                        'label'  => 'China',
                                        'region' => 'CD',
                                    ),
                                'CO' =>
                                    array(
                                        'label'  => 'Colombia',
                                        'region' => 'CD',
                                    ),
                                'CR' =>
                                    array(
                                        'label'  => 'Costa Rica',
                                        'region' => 'CD',
                                    ),
                                'CU' =>
                                    array(
                                        'label'  => 'Cuba',
                                        'region' => 'CD',
                                    ),
                                'CV' =>
                                    array(
                                        'label'  => 'Kaapverdië',
                                        'region' => 'CD',
                                    ),
                                'CW' =>
                                    array(
                                        'label'  => 'Curaçao',
                                        'region' => 'CD',
                                    ),
                                'CY' =>
                                    array(
                                        'label'  => 'Cyprus',
                                        'region' => 'CD',
                                    ),
                                'DJ' =>
                                    array(
                                        'label'  => 'Djibouti',
                                        'region' => 'CD',
                                    ),
                                'DM' =>
                                    array(
                                        'label'  => 'Dominica',
                                        'region' => 'CD',
                                    ),
                                'DO' =>
                                    array(
                                        'label'  => 'Dominicaanse Republiek',
                                        'region' => 'CD',
                                    ),
                                'DZ' =>
                                    array(
                                        'label'  => 'Algerije',
                                        'region' => 'CD',
                                    ),
                                'EC' =>
                                    array(
                                        'label'  => 'Ecuador',
                                        'region' => 'CD',
                                    ),
                                'EG' =>
                                    array(
                                        'label'  => 'Egypte',
                                        'region' => 'CD',
                                    ),
                                'ER' =>
                                    array(
                                        'label'  => 'Eritrea',
                                        'region' => 'CD',
                                    ),
                                'ET' =>
                                    array(
                                        'label'  => 'Ethiopië',
                                        'region' => 'CD',
                                    ),
                                'FJ' =>
                                    array(
                                        'label'  => 'Fiji',
                                        'region' => 'CD',
                                    ),
                                'FK' =>
                                    array(
                                        'label'  => 'Falklandeilanden',
                                        'region' => 'CD',
                                    ),
                                'GA' =>
                                    array(
                                        'label'  => 'Gabon',
                                        'region' => 'CD',
                                    ),
                                'GD' =>
                                    array(
                                        'label'  => 'Grenada',
                                        'region' => 'CD',
                                    ),
                                'GE' =>
                                    array(
                                        'label'  => 'Georgië',
                                        'region' => 'CD',
                                    ),
                                'GF' =>
                                    array(
                                        'label'  => 'Frans Guyana',
                                        'region' => 'CD',
                                    ),
                                'GH' =>
                                    array(
                                        'label'  => 'Ghana',
                                        'region' => 'CD',
                                    ),
                                'GM' =>
                                    array(
                                        'label'  => 'Gambia',
                                        'region' => 'CD',
                                    ),
                                'GN' =>
                                    array(
                                        'label'  => 'Guinee',
                                        'region' => 'CD',
                                    ),
                                'GP' =>
                                    array(
                                        'label'  => 'Guadeloupe',
                                        'region' => 'CD',
                                    ),
                                'GQ' =>
                                    array(
                                        'label'  => 'Equatoriaal-Guinea',
                                        'region' => 'CD',
                                    ),
                                'GT' =>
                                    array(
                                        'label'  => 'Guatemala',
                                        'region' => 'CD',
                                    ),
                                'GW' =>
                                    array(
                                        'label'  => 'Guinee-Bissau',
                                        'region' => 'CD',
                                    ),
                                'GY' =>
                                    array(
                                        'label'  => 'Guyana',
                                        'region' => 'CD',
                                    ),
                                'HK' =>
                                    array(
                                        'label'  => 'Hongkong',
                                        'region' => 'CD',
                                    ),
                                'HN' =>
                                    array(
                                        'label'  => 'Honduras',
                                        'region' => 'CD',
                                    ),
                                'HT' =>
                                    array(
                                        'label'  => 'Haïti',
                                        'region' => 'CD',
                                    ),
                                'ID' =>
                                    array(
                                        'label'  => 'Indonesië',
                                        'region' => 'CD',
                                    ),
                                'IL' =>
                                    array(
                                        'label'  => 'Israël',
                                        'region' => 'CD',
                                    ),
                                'IM' =>
                                    array(
                                        'label'  => 'Isle of Man',
                                        'region' => 'CD',
                                    ),
                                'IN' =>
                                    array(
                                        'label'  => 'India',
                                        'region' => 'CD',
                                    ),
                                'IQ' =>
                                    array(
                                        'label'  => 'Irak',
                                        'region' => 'CD',
                                    ),
                                'IR' =>
                                    array(
                                        'label'  => 'Iran',
                                        'region' => 'CD',
                                    ),
                                'JM' =>
                                    array(
                                        'label'  => 'Jamaica',
                                        'region' => 'CD',
                                    ),
                                'JO' =>
                                    array(
                                        'label'  => 'Jordanië',
                                        'region' => 'CD',
                                    ),
                                'JP' =>
                                    array(
                                        'label'  => 'Japan',
                                        'region' => 'CD',
                                    ),
                                'KE' =>
                                    array(
                                        'label'  => 'Kenya',
                                        'region' => 'CD',
                                    ),
                                'KG' =>
                                    array(
                                        'label'  => 'Kirgizië',
                                        'region' => 'CD',
                                    ),
                                'KH' =>
                                    array(
                                        'label'  => 'Cambodja',
                                        'region' => 'CD',
                                    ),
                                'KI' =>
                                    array(
                                        'label'  => 'Kiribati',
                                        'region' => 'CD',
                                    ),
                                'KM' =>
                                    array(
                                        'label'  => 'Comoren',
                                        'region' => 'CD',
                                    ),
                                'KN' =>
                                    array(
                                        'label'  => 'Saint Kitts en Nevis',
                                        'region' => 'CD',
                                    ),
                                'KP' =>
                                    array(
                                        'label'  => 'Noord-Korea',
                                        'region' => 'CD',
                                    ),
                                'KR' =>
                                    array(
                                        'label'  => 'Zuid-Korea',
                                        'region' => 'CD',
                                    ),
                                'KW' =>
                                    array(
                                        'label'  => 'Koeweit',
                                        'region' => 'CD',
                                    ),
                                'KY' =>
                                    array(
                                        'label'  => 'Caymaneilanden',
                                        'region' => 'CD',
                                    ),
                                'KZ' =>
                                    array(
                                        'label'  => 'Kazachstan',
                                        'region' => 'CD',
                                    ),
                                'LA' =>
                                    array(
                                        'label'  => 'Laos',
                                        'region' => 'CD',
                                    ),
                                'LB' =>
                                    array(
                                        'label'  => 'Libanon',
                                        'region' => 'CD',
                                    ),
                                'LC' =>
                                    array(
                                        'label'  => 'Saint Lucia',
                                        'region' => 'CD',
                                    ),
                                'LK' =>
                                    array(
                                        'label'  => 'Sri Lanka',
                                        'region' => 'CD',
                                    ),
                                'LR' =>
                                    array(
                                        'label'  => 'Liberia',
                                        'region' => 'CD',
                                    ),
                                'LS' =>
                                    array(
                                        'label'  => 'Lesotho',
                                        'region' => 'CD',
                                    ),
                                'LY' =>
                                    array(
                                        'label'  => 'Libië',
                                        'region' => 'CD',
                                    ),
                                'MA' =>
                                    array(
                                        'label'  => 'Marokko',
                                        'region' => 'CD',
                                    ),
                                'MG' =>
                                    array(
                                        'label'  => 'Madagaskar',
                                        'region' => 'CD',
                                    ),
                                'ML' =>
                                    array(
                                        'label'  => 'Mali',
                                        'region' => 'CD',
                                    ),
                                'MM' =>
                                    array(
                                        'label'  => 'Myanmar',
                                        'region' => 'CD',
                                    ),
                                'MN' =>
                                    array(
                                        'label'  => 'Mongolië',
                                        'region' => 'CD',
                                    ),
                                'MO' =>
                                    array(
                                        'label'  => 'Macao',
                                        'region' => 'CD',
                                    ),
                                'MQ' =>
                                    array(
                                        'label'  => 'Martinique',
                                        'region' => 'CD',
                                    ),
                                'MR' =>
                                    array(
                                        'label'  => 'Mauretanië',
                                        'region' => 'CD',
                                    ),
                                'MS' =>
                                    array(
                                        'label'  => 'Montserrat',
                                        'region' => 'CD',
                                    ),
                                'MT' =>
                                    array(
                                        'label'  => 'Malta',
                                        'region' => 'CD',
                                    ),
                                'MU' =>
                                    array(
                                        'label'  => 'Mauritius',
                                        'region' => 'CD',
                                    ),
                                'MV' =>
                                    array(
                                        'label'  => 'Maldiven',
                                        'region' => 'CD',
                                    ),
                                'MW' =>
                                    array(
                                        'label'  => 'Malawi',
                                        'region' => 'CD',
                                    ),
                                'MX' =>
                                    array(
                                        'label'  => 'Mexico',
                                        'region' => 'CD',
                                    ),
                                'MY' =>
                                    array(
                                        'label'  => 'Maleisië',
                                        'region' => 'CD',
                                    ),
                                'MZ' =>
                                    array(
                                        'label'  => 'Mozambique',
                                        'region' => 'CD',
                                    ),
                                'NA' =>
                                    array(
                                        'label'  => 'Namibië',
                                        'region' => 'CD',
                                    ),
                                'NC' =>
                                    array(
                                        'label'  => 'Nieuw-Caledonië',
                                        'region' => 'CD',
                                    ),
                                'NE' =>
                                    array(
                                        'label'  => 'Niger',
                                        'region' => 'CD',
                                    ),
                                'NG' =>
                                    array(
                                        'label'  => 'Nigeria',
                                        'region' => 'CD',
                                    ),
                                'NI' =>
                                    array(
                                        'label'  => 'Nicaragua',
                                        'region' => 'CD',
                                    ),
                                'NP' =>
                                    array(
                                        'label'  => 'Nepal',
                                        'region' => 'CD',
                                    ),
                                'NR' =>
                                    array(
                                        'label'  => 'Nauru',
                                        'region' => 'CD',
                                    ),
                                'NZ' =>
                                    array(
                                        'label'  => 'Nieuw-Zeeland',
                                        'region' => 'CD',
                                    ),
                                'OM' =>
                                    array(
                                        'label'  => 'Oman',
                                        'region' => 'CD',
                                    ),
                                'PA' =>
                                    array(
                                        'label'  => 'Panama',
                                        'region' => 'CD',
                                    ),
                                'PE' =>
                                    array(
                                        'label'  => 'Peru',
                                        'region' => 'CD',
                                    ),
                                'PF' =>
                                    array(
                                        'label'  => 'Frans Polynesië',
                                        'region' => 'CD',
                                    ),
                                'PG' =>
                                    array(
                                        'label'  => 'Papoea-Nieuw-Guinea',
                                        'region' => 'CD',
                                    ),
                                'PH' =>
                                    array(
                                        'label'  => 'Filipijnen',
                                        'region' => 'CD',
                                    ),
                                'PK' =>
                                    array(
                                        'label'  => 'Pakistan',
                                        'region' => 'CD',
                                    ),
                                'PM' =>
                                    array(
                                        'label'  => 'Saint-Pierre en Miquelon',
                                        'region' => 'CD',
                                    ),
                                'PN' =>
                                    array(
                                        'label'  => 'Pitcairneilanden',
                                        'region' => 'CD',
                                    ),
                                'PR' =>
                                    array(
                                        'label'  => 'Puerto Rico',
                                        'region' => 'CD',
                                    ),
                                'PY' =>
                                    array(
                                        'label'  => 'Paraguay',
                                        'region' => 'CD',
                                    ),
                                'QA' =>
                                    array(
                                        'label'  => 'Qatar',
                                        'region' => 'CD',
                                    ),
                                'RE' =>
                                    array(
                                        'label'  => 'Reunion',
                                        'region' => 'CD',
                                    ),
                                'RU' =>
                                    array(
                                        'label'  => 'Rusland',
                                        'region' => 'CD',
                                    ),
                                'RW' =>
                                    array(
                                        'label'  => 'Rwanda',
                                        'region' => 'CD',
                                    ),
                                'SA' =>
                                    array(
                                        'label'  => 'Saoedi-Arabië',
                                        'region' => 'CD',
                                    ),
                                'SC' =>
                                    array(
                                        'label'  => 'Seychellen',
                                        'region' => 'CD',
                                    ),
                                'SD' =>
                                    array(
                                        'label'  => 'Sudan',
                                        'region' => 'CD',
                                    ),
                                'SG' =>
                                    array(
                                        'label'  => 'Singapore',
                                        'region' => 'CD',
                                    ),
                                'SL' =>
                                    array(
                                        'label'  => 'Sierra Leone',
                                        'region' => 'CD',
                                    ),
                                'SN' =>
                                    array(
                                        'label'  => 'Senegal',
                                        'region' => 'CD',
                                    ),
                                'SO' =>
                                    array(
                                        'label'  => 'Somalië',
                                        'region' => 'CD',
                                    ),
                                'SR' =>
                                    array(
                                        'label'  => 'Suriname',
                                        'region' => 'CD',
                                    ),
                                'ST' =>
                                    array(
                                        'label'  => 'Sao Tomé en Principe',
                                        'region' => 'CD',
                                    ),
                                'SV' =>
                                    array(
                                        'label'  => 'El Salvador',
                                        'region' => 'CD',
                                    ),
                                'SX' =>
                                    array(
                                        'label'  => 'Sint Maarten',
                                        'region' => 'CD',
                                    ),
                                'SY' =>
                                    array(
                                        'label'  => 'Syrië',
                                        'region' => 'CD',
                                    ),
                                'SZ' =>
                                    array(
                                        'label'  => 'Swaziland',
                                        'region' => 'CD',
                                    ),
                                'TC' =>
                                    array(
                                        'label'  => 'Turks en Caicoseilanden',
                                        'region' => 'CD',
                                    ),
                                'TD' =>
                                    array(
                                        'label'  => 'Tsjaad',
                                        'region' => 'CD',
                                    ),
                                'TG' =>
                                    array(
                                        'label'  => 'Togo',
                                        'region' => 'CD',
                                    ),
                                'TH' =>
                                    array(
                                        'label'  => 'Thailand',
                                        'region' => 'CD',
                                    ),
                                'TJ' =>
                                    array(
                                        'label'  => 'Tadzjikistan',
                                        'region' => 'CD',
                                    ),
                                'TL' =>
                                    array(
                                        'label'  => 'Oost Timor',
                                        'region' => 'CD',
                                    ),
                                'TM' =>
                                    array(
                                        'label'  => 'Turkmenistan',
                                        'region' => 'CD',
                                    ),
                                'TN' =>
                                    array(
                                        'label'  => 'Tunesië',
                                        'region' => 'CD',
                                    ),
                                'TO' =>
                                    array(
                                        'label'  => 'Tonga',
                                        'region' => 'CD',
                                    ),
                                'TT' =>
                                    array(
                                        'label'  => 'Trinidad en Tobago',
                                        'region' => 'CD',
                                    ),
                                'TV' =>
                                    array(
                                        'label'  => 'Tuvalu',
                                        'region' => 'CD',
                                    ),
                                'TW' =>
                                    array(
                                        'label'  => 'Taiwan',
                                        'region' => 'CD',
                                    ),
                                'TZ' =>
                                    array(
                                        'label'  => 'Tanzania',
                                        'region' => 'CD',
                                    ),
                                'UG' =>
                                    array(
                                        'label'  => 'Uganda',
                                        'region' => 'CD',
                                    ),
                                'US' =>
                                    array(
                                        'label'  => 'Verenigde Staten',
                                        'region' => 'CD',
                                    ),
                                'UY' =>
                                    array(
                                        'label'  => 'Uruguay',
                                        'region' => 'CD',
                                    ),
                                'UZ' =>
                                    array(
                                        'label'  => 'Oezbekistan',
                                        'region' => 'CD',
                                    ),
                                'VC' =>
                                    array(
                                        'label'  => 'Saint Vincent & Gren.',
                                        'region' => 'CD',
                                    ),
                                'VE' =>
                                    array(
                                        'label'  => 'Venezuela',
                                        'region' => 'CD',
                                    ),
                                'VG' =>
                                    array(
                                        'label'  => 'Britse Maagdeneilanden',
                                        'region' => 'CD',
                                    ),
                                'VI' =>
                                    array(
                                        'label'  => 'Amerikaanse Maagdeneil.',
                                        'region' => 'CD',
                                    ),
                                'VN' =>
                                    array(
                                        'label'  => 'Vietnam',
                                        'region' => 'CD',
                                    ),
                                'VU' =>
                                    array(
                                        'label'  => 'Vanuatu',
                                        'region' => 'CD',
                                    ),
                                'WS' =>
                                    array(
                                        'label'  => 'Samoa',
                                        'region' => 'CD',
                                    ),
                                'XK' =>
                                    array(
                                        'label'  => 'Kosovo',
                                        'region' => 'CD',
                                    ),
                                'YE' =>
                                    array(
                                        'label'  => 'Jemen',
                                        'region' => 'CD',
                                    ),
                                'ZA' =>
                                    array(
                                        'label'  => 'Zuid-Afrika',
                                        'region' => 'CD',
                                    ),
                                'ZM' =>
                                    array(
                                        'label'  => 'Zambia',
                                        'region' => 'CD',
                                    ),
                                'ZW' =>
                                    array(
                                        'label'  => 'Zimbabwe',
                                        'region' => 'CD',
                                    ),
                            ),
                    ),
            ),
        );
    }

    /**
     * Get supported countries when offline
     *
     * @since 2.1.1
     * @deprecated 2.2.0
     *
     * @return array
     */
    public static function getSupportedCountriesOffline()
    {
        return static::getSupportedCountries();
    }

    /**
     * Get EU countries
     *
     * @since 2.2.0
     *
     * @return array
     */
    public static function getEUCountries()
    {
        return array(
            array('alpha2Code' => 'AX',),
            array('alpha2Code' => 'AT',),
            array('alpha2Code' => 'BE',),
            array('alpha2Code' => 'BG',),
            array('alpha2Code' => 'HR',),
            array('alpha2Code' => 'CY',),
            array('alpha2Code' => 'CZ',),
            array('alpha2Code' => 'DK',),
            array('alpha2Code' => 'EE',),
            array('alpha2Code' => 'FO',),
            array('alpha2Code' => 'FI',),
            array('alpha2Code' => 'FR',),
            array('alpha2Code' => 'GF',),
            array('alpha2Code' => 'DE',),
            array('alpha2Code' => 'GI',),
            array('alpha2Code' => 'GR',),
            array('alpha2Code' => 'HU',),
            array('alpha2Code' => 'IE',),
            array('alpha2Code' => 'IM',),
            array('alpha2Code' => 'IT',),
            array('alpha2Code' => 'LV',),
            array('alpha2Code' => 'LT',),
            array('alpha2Code' => 'LU',),
            array('alpha2Code' => 'MT',),
            array('alpha2Code' => 'PL',),
            array('alpha2Code' => 'PT',),
            array('alpha2Code' => 'RO',),
            array('alpha2Code' => 'SK',),
            array('alpha2Code' => 'SI',),
            array('alpha2Code' => 'ES',),
            array('alpha2Code' => 'SE',),
            array('alpha2Code' => 'GB',),
        );
    }

    /**
     * @return array
     *
     * @since 2.1.1
     * @deprecated 2.2.0
     */
    public static function getEuCountriesOffline()
    {
        return static::getEUCountries();
    }

    /**
     * @param string   $class
     * @param int|null $idLang
     *
     * @return false|null|string
     *
     * @throws PrestaShopException
     * @since 2.2.0
     */
    public static function getTabName($class, $idLang = null)
    {
        if ($class == null) {
            return '';
        }
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $sql = new DbQuery();
        $sql->select('tl.`name`');
        $sql->from('tab', 't');
        $sql->innerJoin('tab_lang', 'tl', 'tl.`id_tab` = t.`id_tab`');
        $sql->where('t.`class_name` = \''.pSQL($class).'\'');
        $sql->where('tl.`id_lang` = '.(int) $idLang);


        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Post process tags in (translated) strings
     *
     * @param string $string
     * @param array  $tags
     *
     * @return string
     */
    public static function ppTags($string, $tags = array())
    {
        // If tags were explicitely provided, we want to use them *after* the translation string is escaped.
        if (!empty($tags)) {
            foreach ($tags as $index => $tag) {
                // Make positions start at 1 so that it behaves similar to the %1$d etc. sprintf positional params
                $position = $index + 1;
                // extract tag name
                $match = array();
                if (preg_match('/^\s*<\s*(\w+)/', $tag, $match)) {
                    $opener = $tag;
                    $closer = '</'.$match[1].'>';

                    $string = str_replace('['.$position.']', $opener, $string);
                    $string = str_replace('[/'.$position.']', $closer, $string);
                    $string = str_replace('['.$position.'/]', $opener.$closer, $string);
                }
            }
        }

        return $string;
    }

    /**
     * Get default insurance amount
     *
     * @param bool $return Get the insurance amount for a related return label
     *
     * @return int
     *
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function getInsuranceAmount($return = false)
    {
        if (Configuration::get(constant('MyParcel::DEFAULT_'.($return ? 'RETURN_' : '').'CONCEPT_INSURED'))) {
            switch (Configuration::get(constant('MyParcel::DEFAULT_'.($return ? 'RETURN_' : '').'CONCEPT_INSURED_TYPE'))) {
                case MyParcel::INSURED_TYPE_50:
                    return 5000;
                case MyParcel::INSURED_TYPE_250:
                    return 25000;
                case MyParcel::INSURED_TYPE_500:
                    return 50000;
                default:
                    return (int) Configuration::get(constant('MyParcel::DEFAULT_'.($return ? 'RETURN_' : '').'CONCEPT_INSURED_AMOUNT'));
            }
        }

        return 0;
    }
}

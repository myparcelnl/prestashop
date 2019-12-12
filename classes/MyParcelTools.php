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

use MyParcelModule\MyParcelHttpClient;
use MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository;

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
        if (base64_encode(base64_decode($content)) === $content
            && mb_strlen($content) >= 8
        ) {
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
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function getAddressLineFields($idCountry)
    {
        $country = new Country($idCountry);
        $iso = Tools::strtoupper($country->iso_code);
        if ($line = Configuration::get(MyParcel::ADDRESS_FIELD_OVERRIDE.$iso)) {
            return explode(' ', $line);
        }

        preg_match(MyParcel::ADDRESS_FORMAT_REGEX, AddressFormat::getAddressCountryFormat($idCountry), $fields);

        return array_pad(array_splice($fields, 1), 3, null);
    }

    /**
     * Get address line 1
     *
     * @param Address $address
     *
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public static function getAddressLine(Address $address)
    {
        $country = new Country($address->id_country);
        $iso = Tools::strtoupper($country->iso_code);
        $addressLine = '';
        if ($line = Configuration::get(MyParcel::ADDRESS_FIELD_OVERRIDE.$iso)) {
            $fields = explode(' ', $line);
        } else {
            $fields = static::getAddressLineFields($address->id_country);
        }

        if (strtoupper($country->iso_code) === 'BE' && $fields[2]) {
            $addressLine = "{$address->{$fields[0]}} {$address->{$fields[1]}} bus {$address->{$fields[2]}}";
        } else {
            foreach ($fields as $field) {
                if ($field && $address->{$field}) {
                    $addressLine .= ' '.$address->{$field};
                }
            }
        }

        return trim($addressLine);
    }

    /**
     * Get parsed and split address
     *
     * @param Address $address
     *
     * @return array('street' => string, 'number' => string, 'number_suffix' => string)
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getParsedAddress(Address $address)
    {
        $country = new Country($address->id_country);
        $iso = Tools::strtoupper($country->iso_code);
        if (!in_array($iso, array('NL', 'BE'))) {
            return array(
                'street'        => static::getAddressLine($address),
                'number'        => '',
                'number_suffix' => '',
            );
        }
        if ($iso === 'NL') {
            $regex = MyParcelConsignmentRepository::SPLIT_STREET_REGEX;
            $addressLine = static::getAddressLine($address);
            preg_match($regex, $addressLine, $matches);
            if (!isset($matches['number']) || !$matches['number']) {
                preg_match($regex, "{$address->address1} {$address->address2}", $matches);
            }

            return array(
                'street'        => !empty($matches['street']) ? $matches['street'] : $addressLine,
                'number'        => !empty($matches['street']) && isset($matches['number']) ? $matches['number'] : '',
                'number_suffix' => Tools::substr(!empty($matches['street']) && isset($matches['number_suffix']) ? $matches['number_suffix'] : '', 0, 6),
            );
        } else {
            $results = array();
            $counts = array();
            $addressLine = static::getAddressLine($address);
            $defaultAddressLine = $address->address1;
            foreach (array($addressLine, $defaultAddressLine) as $target) {
                preg_match(MyParcelConsignmentRepository::SPLIT_STREET_REGEX_BE, $target, $matches);
                $result = array(
                    'street'        => isset($matches['street']) ? $matches['street'] : '',
                    'street_suffix' => isset($matches['street_suffix']) ? $matches['street_suffix'] : '',
                    'number'        => isset($matches['number']) ? $matches['number'] : '',
                    'box_separator' => isset($matches['box_separator']) ? $matches['box_separator'] : '',
                    'box_number'    => isset($matches['box_number']) ? $matches['box_number'] : '',
                );
                $counts[] = array_sum(array_map(function ($item) { return !empty($item); }, array_values($result)));
                $results[] = $result;
                unset($matches);
            }
            $highestIndex = array_keys($counts, max($counts));
            $highestIndex = $highestIndex[0];

            return array(
                'street'        => $results[$highestIndex]['street'],
                'number'        => $results[$highestIndex]['number'],
                'number_suffix' => $results[$highestIndex]['box_number'],
            );
        }
    }

    /**
     * Get address line 2
     *
     * @param Address $address
     *
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
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
     *
     * @throws PrestaShopException
     */
    public static function getInvoiceSuggestion(Order $order)
    {
        $invoice = $order->getInvoicesCollection()->getFirst();
        if ($invoice instanceof OrderInvoice) {
            return $invoice->getInvoiceNumberFormatted(Context::getContext()->language->id);
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
        $weight = ceil($order->getTotalWeight()) * (MyParcel::getWeightUnit() === 'kg' ? 1000 : 1);
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
    public static function getSupportedCountriesOffline()
    {
        return @json_decode('{"data":{"countries":[{"NL":{"label":"Nederland","region":"NL"},"AT":{"label":"Oostenrijk","region":"EU"},"BE":{"label":"Belgi\u00eb","region":"EU"},"BG":{"label":"Bulgarije","region":"EU"},"CZ":{"label":"Tsjechi\u00eb","region":"EU"},"DE":{"label":"Duitsland","region":"EU"},"DK":{"label":"Denemarken","region":"EU"},"EE":{"label":"Estland","region":"EU"},"ES":{"label":"Spanje","region":"EU"},"FI":{"label":"Finland","region":"EU"},"FR":{"label":"Frankrijk","region":"EU"},"GB":{"label":"Verenigd Koninkrijk","region":"EU"},"GR":{"label":"Griekenland","region":"EU"},"HU":{"label":"Hongarije","region":"EU"},"IE":{"label":"Ierland","region":"EU"},"IT":{"label":"Itali\u00eb","region":"EU"},"LT":{"label":"Litouwen","region":"EU"},"LU":{"label":"Luxemburg","region":"EU"},"LV":{"label":"Letland","region":"EU"},"MC":{"label":"Monaco","region":"EU"},"PL":{"label":"Polen","region":"EU"},"PT":{"label":"Portugal","region":"EU"},"RO":{"label":"Roemeni\u00eb","region":"EU"},"SE":{"label":"Zweden","region":"EU"},"SI":{"label":"Sloveni\u00eb","region":"EU"},"SK":{"label":"Slowakije","region":"EU"},"AD":{"label":"Andorra","region":"CD"},"AL":{"label":"Albani\u00eb","region":"CD"},"BA":{"label":"Bosni\u00eb-Herzegovina","region":"CD"},"BY":{"label":"Wit-Rusland","region":"CD"},"CH":{"label":"Zwitserland","region":"CD"},"FO":{"label":"Faer\u00f6er Eilanden","region":"CD"},"GG":{"label":"Guernsey","region":"CD"},"GI":{"label":"Gibraltar","region":"CD"},"GL":{"label":"Groenland","region":"CD"},"HR":{"label":"Kroati\u00eb","region":"CD"},"IC":{"label":"Canarische Eilanden","region":"CD"},"IS":{"label":"IJsland","region":"CD"},"JE":{"label":"Jersey","region":"CD"},"LI":{"label":"Liechtenstein","region":"CD"},"MD":{"label":"Moldavi\u00eb","region":"CD"},"ME":{"label":"Montenegro","region":"CD"},"MK":{"label":"Macedoni\u00eb","region":"CD"},"NO":{"label":"Noorwegen","region":"CD"},"RS":{"label":"Servi\u00eb","region":"CD"},"SM":{"label":"San Marino","region":"CD"},"TR":{"label":"Turkije","region":"CD"},"UA":{"label":"Oekra\u00efne","region":"CD"},"VA":{"label":"Vaticaanstad","region":"CD"},"AE":{"label":"Ver. Arabische Emiraten","region":"CD"},"AF":{"label":"Afghanistan","region":"CD"},"AG":{"label":"Antigua en Barbuda","region":"CD"},"AM":{"label":"Armeni\u00eb","region":"CD"},"AN":{"label":"Nederlandse Antillen","region":"CD"},"AO":{"label":"Angola","region":"CD"},"AQ":{"label":"Antarctica","region":"CD"},"AR":{"label":"Argentini\u00eb","region":"CD"},"AU":{"label":"Australi\u00eb","region":"CD"},"AW":{"label":"Aruba","region":"CD"},"AZ":{"label":"Azerbeidzjan","region":"CD"},"BB":{"label":"Barbados","region":"CD"},"BD":{"label":"Bangladesh","region":"CD"},"BF":{"label":"Burkina Faso","region":"CD"},"BH":{"label":"Bahrein","region":"CD"},"BI":{"label":"Burundi","region":"CD"},"BJ":{"label":"Benin","region":"CD"},"BM":{"label":"Bermuda","region":"CD"},"BN":{"label":"Brunei Darussalam","region":"CD"},"BO":{"label":"Bolivia","region":"CD"},"BQ":{"label":"Bonaire, Sint Eustatius en Saba","region":"CD"},"BR":{"label":"Brazili\u00eb","region":"CD"},"BS":{"label":"Bahama\u2019s","region":"CD"},"BT":{"label":"Bhutan","region":"CD"},"BW":{"label":"Botswana","region":"CD"},"BZ":{"label":"Belize","region":"CD"},"CA":{"label":"Canada","region":"CD"},"CD":{"label":"Congo-Kinshasa","region":"CD"},"CF":{"label":"Centraal-Afrikaanse Rep.","region":"CD"},"CG":{"label":"Congo-Brazzaville","region":"CD"},"CI":{"label":"Ivoorkust","region":"CD"},"CL":{"label":"Chili","region":"CD"},"CM":{"label":"Kameroen","region":"CD"},"CN":{"label":"China","region":"CD"},"CO":{"label":"Colombia","region":"CD"},"CR":{"label":"Costa Rica","region":"CD"},"CU":{"label":"Cuba","region":"CD"},"CV":{"label":"Kaapverdi\u00eb","region":"CD"},"CW":{"label":"Cura\u00e7ao","region":"CD"},"CY":{"label":"Cyprus","region":"CD"},"DJ":{"label":"Djibouti","region":"CD"},"DM":{"label":"Dominica","region":"CD"},"DO":{"label":"Dominicaanse Republiek","region":"CD"},"DZ":{"label":"Algerije","region":"CD"},"EC":{"label":"Ecuador","region":"CD"},"EG":{"label":"Egypte","region":"CD"},"ER":{"label":"Eritrea","region":"CD"},"ET":{"label":"Ethiopi\u00eb","region":"CD"},"FJ":{"label":"Fiji","region":"CD"},"FK":{"label":"Falklandeilanden","region":"CD"},"GA":{"label":"Gabon","region":"CD"},"GD":{"label":"Grenada","region":"CD"},"GE":{"label":"Georgi\u00eb","region":"CD"},"GF":{"label":"Frans Guyana","region":"CD"},"GH":{"label":"Ghana","region":"CD"},"GM":{"label":"Gambia","region":"CD"},"GN":{"label":"Guinee","region":"CD"},"GP":{"label":"Guadeloupe","region":"CD"},"GQ":{"label":"Equatoriaal-Guinea","region":"CD"},"GT":{"label":"Guatemala","region":"CD"},"GW":{"label":"Guinee-Bissau","region":"CD"},"GY":{"label":"Guyana","region":"CD"},"HK":{"label":"Hongkong","region":"CD"},"HN":{"label":"Honduras","region":"CD"},"HT":{"label":"Ha\u00efti","region":"CD"},"ID":{"label":"Indonesi\u00eb","region":"CD"},"IL":{"label":"Isra\u00ebl","region":"CD"},"IM":{"label":"Isle of Man","region":"CD"},"IN":{"label":"India","region":"CD"},"IQ":{"label":"Irak","region":"CD"},"IR":{"label":"Iran","region":"CD"},"JM":{"label":"Jamaica","region":"CD"},"JO":{"label":"Jordani\u00eb","region":"CD"},"JP":{"label":"Japan","region":"CD"},"KE":{"label":"Kenya","region":"CD"},"KG":{"label":"Kirgizi\u00eb","region":"CD"},"KH":{"label":"Cambodja","region":"CD"},"KI":{"label":"Kiribati","region":"CD"},"KM":{"label":"Comoren","region":"CD"},"KN":{"label":"Saint Kitts en Nevis","region":"CD"},"KP":{"label":"Noord-Korea","region":"CD"},"KR":{"label":"Zuid-Korea","region":"CD"},"KW":{"label":"Koeweit","region":"CD"},"KY":{"label":"Caymaneilanden","region":"CD"},"KZ":{"label":"Kazachstan","region":"CD"},"LA":{"label":"Laos","region":"CD"},"LB":{"label":"Libanon","region":"CD"},"LC":{"label":"Saint Lucia","region":"CD"},"LK":{"label":"Sri Lanka","region":"CD"},"LR":{"label":"Liberia","region":"CD"},"LS":{"label":"Lesotho","region":"CD"},"LY":{"label":"Libi\u00eb","region":"CD"},"MA":{"label":"Marokko","region":"CD"},"MG":{"label":"Madagaskar","region":"CD"},"ML":{"label":"Mali","region":"CD"},"MM":{"label":"Myanmar","region":"CD"},"MN":{"label":"Mongoli\u00eb","region":"CD"},"MO":{"label":"Macao","region":"CD"},"MQ":{"label":"Martinique","region":"CD"},"MR":{"label":"Mauretani\u00eb","region":"CD"},"MS":{"label":"Montserrat","region":"CD"},"MT":{"label":"Malta","region":"CD"},"MU":{"label":"Mauritius","region":"CD"},"MV":{"label":"Maldiven","region":"CD"},"MW":{"label":"Malawi","region":"CD"},"MX":{"label":"Mexico","region":"CD"},"MY":{"label":"Maleisi\u00eb","region":"CD"},"MZ":{"label":"Mozambique","region":"CD"},"NA":{"label":"Namibi\u00eb","region":"CD"},"NC":{"label":"Nieuw-Caledoni\u00eb","region":"CD"},"NE":{"label":"Niger","region":"CD"},"NG":{"label":"Nigeria","region":"CD"},"NI":{"label":"Nicaragua","region":"CD"},"NP":{"label":"Nepal","region":"CD"},"NR":{"label":"Nauru","region":"CD"},"NZ":{"label":"Nieuw-Zeeland","region":"CD"},"OM":{"label":"Oman","region":"CD"},"PA":{"label":"Panama","region":"CD"},"PE":{"label":"Peru","region":"CD"},"PF":{"label":"Frans Polynesi\u00eb","region":"CD"},"PG":{"label":"Papoea-Nieuw-Guinea","region":"CD"},"PH":{"label":"Filipijnen","region":"CD"},"PK":{"label":"Pakistan","region":"CD"},"PM":{"label":"Saint-Pierre en Miquelon","region":"CD"},"PN":{"label":"Pitcairneilanden","region":"CD"},"PR":{"label":"Puerto Rico","region":"CD"},"PY":{"label":"Paraguay","region":"CD"},"QA":{"label":"Qatar","region":"CD"},"RE":{"label":"Reunion","region":"CD"},"RU":{"label":"Rusland","region":"CD"},"RW":{"label":"Rwanda","region":"CD"},"SA":{"label":"Saoedi-Arabi\u00eb","region":"CD"},"SC":{"label":"Seychellen","region":"CD"},"SD":{"label":"Sudan","region":"CD"},"SG":{"label":"Singapore","region":"CD"},"SL":{"label":"Sierra Leone","region":"CD"},"SN":{"label":"Senegal","region":"CD"},"SO":{"label":"Somali\u00eb","region":"CD"},"SR":{"label":"Suriname","region":"CD"},"ST":{"label":"Sao Tom\u00e9 en Principe","region":"CD"},"SV":{"label":"El Salvador","region":"CD"},"SX":{"label":"Sint Maarten","region":"CD"},"SY":{"label":"Syri\u00eb","region":"CD"},"SZ":{"label":"Swaziland","region":"CD"},"TC":{"label":"Turks en Caicoseilanden","region":"CD"},"TD":{"label":"Tsjaad","region":"CD"},"TG":{"label":"Togo","region":"CD"},"TH":{"label":"Thailand","region":"CD"},"TJ":{"label":"Tadzjikistan","region":"CD"},"TL":{"label":"Oost Timor","region":"CD"},"TM":{"label":"Turkmenistan","region":"CD"},"TN":{"label":"Tunesi\u00eb","region":"CD"},"TO":{"label":"Tonga","region":"CD"},"TT":{"label":"Trinidad en Tobago","region":"CD"},"TV":{"label":"Tuvalu","region":"CD"},"TW":{"label":"Taiwan","region":"CD"},"TZ":{"label":"Tanzania","region":"CD"},"UG":{"label":"Uganda","region":"CD"},"US":{"label":"Verenigde Staten","region":"CD"},"UY":{"label":"Uruguay","region":"CD"},"UZ":{"label":"Oezbekistan","region":"CD"},"VC":{"label":"Saint Vincent \u0026 Gren.","region":"CD"},"VE":{"label":"Venezuela","region":"CD"},"VG":{"label":"Britse Maagdeneilanden","region":"CD"},"VI":{"label":"Amerikaanse Maagdeneil.","region":"CD"},"VN":{"label":"Vietnam","region":"CD"},"VU":{"label":"Vanuatu","region":"CD"},"WS":{"label":"Samoa","region":"CD"},"XK":{"label":"Kosovo","region":"CD"},"YE":{"label":"Jemen","region":"CD"},"ZA":{"label":"Zuid-Afrika","region":"CD"},"ZM":{"label":"Zambia","region":"CD"},"ZW":{"label":"Zimbabwe","region":"CD"}}]}}', true);
    }

    /**
     * Get supported countries and retrieve when necessary
     *
     * @return array
     *
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getSupportedCountries()
    {
        // WTF Prestashop 1.6.1.23?
        $sql = new DbQuery();
        $sql->select('`value`');
        $sql->from(bqSQL(Configuration::$definition['table']));
        $sql->where('`name` = \''.pSQL(MyParcel::SUPPORTED_COUNTRIES).'\' AND `id_shop` IS NULL');
        $countries = @json_decode(Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql), true);
        if (is_array($countries) && !empty($countries)) {
            return $countries;
        }

        return static::getSupportedCountriesOffline();
    }

    /**
     * Retrieve supported countries from the MyParcel API
     *
     * @return bool|mixed|string Raw json or false if not found
     *
     * @throws PrestaShopException
     * @throws ErrorException
     * @throws ErrorException
     *
     * @since 2.3.0
     *
     */
    public static function retrieveSupportedCountries()
    {
        $curl = new MyParcelHttpClient();
        $countries = $curl->get(MyParcel::SUPPORTED_COUNTRIES_URL);
        if (!$countries || !isset($countries['data']['countries'][0])) {
            $countries = json_encode(MyParcelTools::getSupportedCountriesOffline(), JSON_UNESCAPED_UNICODE);
        } else {
            if (isset($countries['data']['countries'][0]['GB']['region'])) {
                $countries['data']['countries'][0]['UK'] = $countries['data']['countries'][0]['GB'];
            }
            Configuration::updateValue(MyParcel::SUPPORTED_COUNTRIES, mypa_json_encode($countries));
        }
        return $countries;
    }

    /**
     * Get EU countries
     *
     * @return array
     *
     * @throws PrestaShopException
     *
     * @since 2.2.0
     *
     */
    public static function getEUCountries()
    {
        $countries = static::getSupportedCountries();
        if (!isset($countries['data']['countries'][0])) {
            return array();
        }
        $euCountries = array();
        foreach ($countries['data']['countries'][0] as $iso => $country) {
            if (in_array($country['region'], array('NL', 'BE', 'EU'))) {
                $euCountries[] = array('alpha2Code' => $iso);
            }
        }

        return $euCountries;
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
                case MyParcel::INSURED_TYPE_100:
                    return 10000;
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

    /**
     * Delete folder recursively
     *
     * @param string $dir Directory
     *
     * @since 2.3.0
     */
    public static function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false
            && strpos(realpath($dir), realpath(_PS_DOWNLOAD_DIR_)) === false
        ) {
            return;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir.'/'.$object) === 'dir') {
                        static::recursiveDeleteOnDisk($dir.'/'.$object);
                    } else {
                        @unlink($dir.'/'.$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Determine whether the postcode is Walloon
     *
     * Can be used to put the right translation of the box separator on a Belgian label
     *
     * @param string $postcode
     *
     * @return bool
     *
     * @see https://en.wikipedia.org/wiki/List_of_postal_codes_in_Belgium
     */
    public static function isWallonia($postcode)
    {
        $postcode = trim($postcode);
        if ($postcode >= '1300' && $postcode <= '1499') {
            // Walloon Brabant
            return true;
        } elseif ($postcode >= '4000' && $postcode <= '4999') {
            // Liège
            return true;
        } elseif ($postcode >= '5000' && $postcode <= '5999') {
            // Namur
            return true;
        } elseif ($postcode >= '6000' && $postcode <= '6599') {
            // Hainaut
            return true;
        } elseif ($postcode >= '6600' && $postcode <= '6999') {
            // Luxembourg
            return true;
        } elseif ($postcode >= '7000' && $postcode <= '7999') {
            // Hainaut (continued)
            return true;
        }

        return false;
    }

    /**
     * Detect multi-key env (uses multiple MyParcel API keys)
     *
     * @throws PrestaShopException
     */
    public static function isMultiKeyEnvironment()
    {
        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(DISTINCT `value`) FROM `'._DB_PREFIX_.'configuration` WHERE `name` = "'.pSQL(MyParcel::API_KEY).'" AND `value` IS NOT NULL AND `value` != ""') > 1;
    }
}

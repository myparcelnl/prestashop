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

// JSON constants for PHP 5.3
if (!defined('JSON_UNESCAPED_SLASHES')) {
    define('JSON_UNESCAPED_SLASHES', 64);
}

if (!defined('JSON_UNESCAPED_UNICODE')) {
    define('JSON_UNESCAPED_UNICODE', 256);
}

require_once dirname(__FILE__).'/classes/autoload.php';

/**
 * Class MyParcel
 *
 * @since 1.0.0
 */
class MyParcel extends Module
{
    const MENU_MAIN_SETTINGS = 0;
    const MENU_DEFAULT_SETTINGS = 1;
    const MENU_DEFAULT_DELIVERY_OPTIONS = 2;

    const POSTNL_DEFAULT_CARRIER = 'MYPARCEL_DEFAULT_CARRIER';
    const POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER = 'MYPARCEL_DEFAULT_MAILPACK';
    const MYPARCEL_BASE_URL = 'https://www.myparcel.nl/';
    const SUPPORTED_COUNTRIES_URL = 'https://backoffice.myparcel.nl/api/system_country_codes';

    const API_KEY = 'MYPARCEL_API_KEY';

    const LINK_EMAIL = 'MYPARCEL_LINK_EMAIL';
    const LINK_PHONE = 'MYPARCEL_LINK_PHONE';
    const USE_PICKUP_ADDRESS = 'MYPARCEL_USE_PICKUP_ADDRESS';

    const LABEL_DESCRIPTION = 'MYPARCEL_LABEL_DESCRIPTION';
    const PAPER_SELECTION = 'MYPARCEL_PAPER_SELECTION';

    const CHECKOUT_LIVE = 'MYPARCEL_LIVE_CHECKOUT';
    const CHECKOUT_FG_COLOR1 = 'MYPARCEL_CHECKOUT_FG_COLOR1';
    const CHECKOUT_FG_COLOR2 = 'MYPARCEL_CHECKOUT_FG_COLOR2';
    const CHECKOUT_BG_COLOR1 = 'MYPARCEL_CHECKOUT_BG_COLOR1';
    const CHECKOUT_BG_COLOR2 = 'MYPARCEL_CHECKOUT_BG_COLOR2';
    const CHECKOUT_BG_COLOR3 = 'MYPARCEL_CHECKOUT_BG_COLOR3';
    const CHECKOUT_HL_COLOR = 'MYPARCEL_CHECKOUT_HL_COLOR';
    const CHECKOUT_FONT = 'MYPARCEL_CHECKOUT_FONT';
    const CHECKOUT_FONT_SIZE = 'MYPARCEL_CHECKOUT_FSIZE';

    const ENUM_NONE = 0;
    const ENUM_SAMEDAY = 1;
    const ENUM_DELIVERY = 2;
    const ENUM_DELIVERY_SELF_DELAY = 3;

    const DEFAULT_CONCEPT_PARCEL_TYPE = 'MYPARCEL_DEFCON_PT';
    const DEFAULT_CONCEPT_LARGE_PACKAGE = 'MYPARCEL_DEFCON_LP';
    const DEFAULT_CONCEPT_HOME_DELIVERY_ONLY = 'MYPARCEL_DEFCON_HDO';
    const DEFAULT_CONCEPT_RETURN = 'MYPARCEL_DEFCON_RETURN';
    const DEFAULT_CONCEPT_SIGNED = 'MYPARCEL_DEFCON_S';
    const DEFAULT_CONCEPT_INSURED = 'MYPARCEL_DEFCON_I';
    const DEFAULT_CONCEPT_INSURED_TYPE = 'MYPARCEL_DEFCON_I_TYPE';
    const DEFAULT_CONCEPT_INSURED_AMOUNT = 'MYPARCEL_DEFCON_I_AMOUNT';
    const SUPPORTED_COUNTRIES = 'MYPARCEL_SUPPORTED';

    const INSURED_TYPE_50 = 1;
    const INSURED_TYPE_250 = 2;
    const INSURED_TYPE_500 = 3;
    const INSURED_TYPE_500_PLUS = 4;
    const TYPE_PARCEL = 1;
    const TYPE_MAILBOX_PACKAGE = 2;
    const TYPE_UNSTAMPED = 3;
    const TYPE_POST_OFFICE = 4;

    const WEBHOOK_CHECK_INTERVAL = 86400;
    const WEBHOOK_LAST_CHECK = 'MYPARCEL_WEBHOOK_UPD';
    const WEBHOOK_ID = 'MYPARCEL_WEBHOOK_ID'; //daily check

    const UPDATE_ORDER_STATUSES = 'MYPARCEL_UPDATE_OS';
    const CONFIG_TOUR = 'config';
    const CONNECTION_ATTEMPTS = 3;
    const LOG_API = 'MYPARCEL_LOG_API';

    const PRINTED_STATUS = 'MYPARCEL_PRINTED_STATUS';
    const SHIPPED_STATUS = 'MYPARCEL_SHIPPED_STATUS';
    const RECEIVED_STATUS = 'MYPARCEL_RECEIVED_STATUS';
    const NOTIFICATIONS = 'MYPARCEL_NOTIFS';
    const NOTIFICATION_MOMENT = 'MYPARCEL_NOTIF_MOMENT';
    const MOMENT_SCANNED = 0;
    const MOMENT_PRINTED = 1;

    const FONT_SMALL = 1;
    const FONT_MEDIUM = 2;
    const FONT_LARGE = 3;

    // @codingStandardsIgnoreStart
    /**
     * Split street RegEx
     *
     * @author Reindert Vetter <reindert@myparcel.nl>
     * @author Richard Perdaan <richard@myparcel.nl>
     */
    const SPLIT_STREET_REGEX = '~(?P<street>.*?)\s?(?P<street_suffix>(?P<number>[\d]+)-?(?P<number_suffix>[a-zA-Z/\s]{0,5}$|[0-9/]{0,5}$|\s[a-zA-Z]{1}[0-9]{0,3}$))$~';
    /**
     * Address format regex
     *
     * This is a RegEx that can be used to grab the address fields from the AddressFormat object
     */
    const ADDRESS_FORMAT_REGEX = '~^(address1)(?: +([a-zA-Z0-9_]+))?(?: +([a-zA-Z0-9_]+))?~m';
    // @codingStandardsIgnoreEnd

    // @codingStandardsIgnoreStart
    /** @var array $cachedCarriers */
    protected static $cachedCarriers = array();
    /** @var int $id_carrier */
    public $id_carrier;
    /** @var array $hooks */
    public $hooks = array(
        'displayCarrierList',
        'displayHeader',
        'displayBackOfficeHeader',
        'adminOrder',
        'orderDetail',
        'actionValidateOrder',
        'actionAdminOrdersListingFieldsModifier'
    );
    /** @var array $statuses */
    protected $statuses = array();
    /** @var int $menu */
    protected $menu = self::MENU_MAIN_SETTINGS;
    /** @var string $baseUrl */
    protected $baseUrl;
    /** @var string $moduleUrlWithoutToken */
    protected $moduleUrlWithoutToken;
    // @codingStandardsIgnoreEnd

    /**
     * MyParcel constructor.
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    public function __construct()
    {
        $this->name = 'myparcel';
        $this->tab = 'shipping_logistics';
        $this->version = '2.1.0';
        $this->author = 'MyParcel';
        $this->module_key = 'c9bb3b85a9726a7eda0de2b54b34918d';
        $this->bootstrap = true;
        $this->controllers = array('myparcelcheckout', 'myparcelcheckoutdemo', 'deliveryoptions', 'hook');

        parent::__construct();

        if (!empty(Context::getContext()->employee->id)) {
            $this->moduleUrlWithoutToken =
                Context::getContext()->link->getAdminLink('AdminModules', false)
                .'&'
                .http_build_query(
                    array(
                        'configure'   => $this->name,
                        'tab_module'  => $this->tab,
                        'module_name' => $this->name,
                    )
                );

            $this->checkWebhooks();
        }

        $this->displayName = $this->l('MyParcel');
        $this->description = $this->l('Assistance with the parcel service through MyParcel.nl');
    }

    /**
     * Check webhooks + update info
     *
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function checkWebhooks()
    {
        $lastCheck = (int) Configuration::get(static::WEBHOOK_LAST_CHECK);
        $webHookId = trim(Configuration::get(static::WEBHOOK_ID));

        if ((time() > ($lastCheck + static::WEBHOOK_CHECK_INTERVAL)) || empty($webHookId)) {
            // Time to update webhooks
            $ch = curl_init('https://api.myparcel.nl/webhook_subscriptions/'.(string) $webHookId);
            // @codingStandardsIgnoreStart
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: basic ".base64_encode(Configuration::get(static::API_KEY)),
                trim(static::getUserAgent())
            ));
            // @codingStandardsIgnoreEnd
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            $sslEnabled = (bool) Configuration::get('PS_SSL_ENABLED');
            $webhookUrl = Context::getContext()->link->getModuleLink(
                $this->name,
                'hook',
                array(),
                $sslEnabled,
                (int) Configuration::get('PS_LANG_DEFAULT')
            );
            $found = false;
            $idWebhook = (int) Configuration::get(static::WEBHOOK_ID);
            $data = json_decode($response, true);
            if ($data) {
                if (isset($data['data']['webhook_subscriptions']) && is_array($data['data']['webhook_subscriptions'])) {
                    foreach ($data['data']['webhook_subscriptions'] as $subscription) {
                        if ((int) $subscription['id'] !== $idWebhook) {
                            continue;
                        } elseif ($subscription['url'] == $webhookUrl) {
                            $found = true;

                            break;
                        }
                    }
                }
            }

            if (!$found) {
                // @codingStandardsIgnoreStart
                $apiKey = base64_encode(Configuration::get(static::API_KEY));
                // @codingStandardsIgnoreEnd
                $ch = curl_init('https://api.myparcel.nl/webhook_subscriptions');
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Authorization: basic $apiKey",
                        'Content-Type: application/json; charset=utf-8',
                        trim(static::getUserAgent()),
                    )
                );
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                $postData = json_encode(array(
                    'data' => array(
                        'webhook_subscriptions' => array(
                            array(
                                'hook' => 'shipment_status_change',
                                'url'  => $webhookUrl,
                            ),
                        ),
                    ),
                ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                $response = curl_exec($ch);
                curl_close($ch);

                if ($response) {
                    $data = json_decode($response, true);
                    if (isset($data['data']['ids'][0]['id'])) {
                        Configuration::updateValue(static::WEBHOOK_ID, (int) $data['data']['ids'][0]['id']);
                    }
                }
            }

            Configuration::updateValue(static::WEBHOOK_LAST_CHECK, time());

            static::retrieveSupportedCountries();
        }
    }

    /**
     * Retrieve suported countries from MyParcel API
     *
     * @return bool|mixed|string Raw json or false if not found
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected static function retrieveSupportedCountries()
    {
        // Time to update country list
        $ch = curl_init(static::SUPPORTED_COUNTRIES_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $countries = curl_exec($ch);
        curl_close($ch);

        if ($countries) {
            Configuration::updateValue(static::SUPPORTED_COUNTRIES, $countries);
        }

        return $countries;
    }

    /**
     * Add error message
     *
     * @param string $message Message
     * @param bool   $private Only display on module's configuration page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function addError($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->errors[] = '<a href="'.$this->baseUrl.'">'
                    .$this->displayName.': '.$message.'</a>';
            }
        } else {
            // Do not add an error in this case
            // It will halt execution of the ModuleAdminController
            $this->context->controller->errors[] = $message;
        }
    }

    /**
     * Delete folder recursively
     *
     * @param string $dir Directory
     */
    protected function recursiveDeleteOnDisk($dir)
    {
        if (strpos(realpath($dir), realpath(_PS_MODULE_DIR_)) === false) {
            return;
        }
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir.'/'.$object) == 'dir') {
                        $this->recursiveDeleteOnDisk($dir.'/'.$object);
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
     * Check if shipment is sent on date
     *
     * @param int    $idMyParcelCarrierDeliverySetting MyParcel Delivery Option ID
     * @param string $date                             Date in European format d-m-Y
     *
     * @return bool Whether the store dispatches on this date
     *
     * @since 2.0.0
     */
    public static function getShipmentAvailableOnDay($idMyParcelCarrierDeliverySetting, $date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $postnlDeliveryOption = new MyParcelCarrierDeliverySetting($idMyParcelCarrierDeliverySetting);
        if (Validate::isLoadedObject($postnlDeliveryOption)) {
            $dayOfWeek = date('w', strtotime($date));

            $cutoffExceptions = $postnlDeliveryOption->cutoff_exceptions;

            if (!empty($cutoffExceptions)) {
                if (is_array($cutoffExceptions) && array_key_exists($date, $cutoffExceptions)) {
                    if (array_key_exists('cutoff', $cutoffExceptions[$date])) {
                        return true;
                    } elseif (array_key_exists('nodispatch', $cutoffExceptions[$date])
                        && $cutoffExceptions[$date]['nodispatch']
                    ) {
                        return false;
                    }
                }
            }

            switch ($dayOfWeek) {
                case 0:
                    return $postnlDeliveryOption->sunday_enabled;
                case 1:
                    return $postnlDeliveryOption->monday_enabled;
                case 2:
                    return $postnlDeliveryOption->tuesday_enabled;
                case 3:
                    return $postnlDeliveryOption->wednesday_enabled;
                case 4:
                    return $postnlDeliveryOption->thursday_enabled;
                case 5:
                    return $postnlDeliveryOption->friday_enabled;
                case 6:
                    return $postnlDeliveryOption->saturday_enabled;
            }
        }

        return false;
    }

    /**
     * Get Cut Off time on day
     *
     * @param int    $idMyParcelCarrierDeliverySetting MyParcelCarrierDeliverySetting ID
     * @param string $date                             Custom date
     *
     * @return bool|string Cut off time or false if no shipment on that day
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @since 2.0.0
     */
    public static function getCutOffTime($idMyParcelCarrierDeliverySetting, $date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        $myparcelCarrierDeliverySetting = new MyParcelCarrierDeliverySetting($idMyParcelCarrierDeliverySetting);
        if (Validate::isLoadedObject($myparcelCarrierDeliverySetting)) {
            $dayOfWeek = date('w', strtotime($date));

            $cutoffExceptions = $myparcelCarrierDeliverySetting->cutoff_exceptions;

            if (!empty($cutoffExceptions)) {
                if (is_array($cutoffExceptions) && array_key_exists($date, $cutoffExceptions)) {
                    if (array_key_exists('cutoff', $cutoffExceptions[$date])) {
                        return $cutoffExceptions[$date]['cutoff'];
                    } else {
                        return false;
                    }
                }
            }

            switch ($dayOfWeek) {
                case 0:
                    $cutoff = $myparcelCarrierDeliverySetting->sunday_cutoff;
                    break;
                case 1:
                    $cutoff = $myparcelCarrierDeliverySetting->monday_cutoff;
                    break;
                case 2:
                    $cutoff = $myparcelCarrierDeliverySetting->tuesday_cutoff;
                    break;
                case 3:
                    $cutoff = $myparcelCarrierDeliverySetting->wednesday_cutoff;
                    break;
                case 4:
                    $cutoff = $myparcelCarrierDeliverySetting->thursday_cutoff;
                    break;
                case 5:
                    $cutoff = $myparcelCarrierDeliverySetting->friday_cutoff;
                    break;
                case 6:
                    $cutoff = $myparcelCarrierDeliverySetting->saturday_cutoff;
                    break;
            }

            if (empty($cutoff)) {
                return false;
            } else {
                return $cutoff;
            }
        }

        return false;
    }

    /**
     * Installs the module
     *
     * @return bool Indicates whether the module has been successfully installed
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function install()
    {
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            $this->addError(
                $this->l('Unable to install the MyParcel module. Please enable PHP 5.3.3 or higher.'),
                false
            );

            return false;
        }

        if (!function_exists('curl_init')) {
            $this->addError(
                $this->l('Unable to install the MyParcel module. Please enable the PHP cURL extension.'),
                false
            );

            return false;
        }

        if (!parent::install()) {
            return false;
        }

        if (!$this->installSql()) {
            parent::uninstall();

            return false;
        }

        $this->addCarrier('PostNL', static::POSTNL_DEFAULT_CARRIER);
        $this->addCarrier('PostNL Brievenbuspakje', static::POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER);

        // On 1.7 only the hook `displayBeforeCarrier` works properly
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $index = array_search('displayCarrierList', $this->hooks);
            unset($this->hooks[$index]);
            $this->hooks[] = 'displayBeforeCarrier';
        }
        foreach ($this->hooks as $hook) {
            try {
                $this->registerHook($hook);
            } catch (PrestaShopException $e) {
            }
        }

        Configuration::updateValue(static::CHECKOUT_FG_COLOR1, '#FFFFFF');
        Configuration::updateValue(static::CHECKOUT_FG_COLOR2, '#000000');
        Configuration::updateValue(
            static::CHECKOUT_BG_COLOR1,
            version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? 'transparent' : '#FBFBFB'
        );
        Configuration::updateValue(static::CHECKOUT_BG_COLOR2, '#01BBC5');
        Configuration::updateValue(static::CHECKOUT_BG_COLOR3, '#75D3D8');
        Configuration::updateValue(static::CHECKOUT_HL_COLOR, '#FF8C00');
        Configuration::updateValue(static::CHECKOUT_FONT, 'Exo');
        Configuration::updateValue(static::CHECKOUT_FONT_SIZE, 2);
        Configuration::updateValue(static::LABEL_DESCRIPTION, '{order.reference}');
        Configuration::updateValue(static::PRINTED_STATUS, 0);
        Configuration::updateValue(static::SHIPPED_STATUS, (int) Configuration::get('PS_OS_SHIPPING'));
        Configuration::updateValue(static::RECEIVED_STATUS, (int) Configuration::get('PS_OS_DELIVERED'));
        Configuration::updateValue(static::LINK_EMAIL, true);
        Configuration::updateValue(static::LINK_PHONE, true);
        Configuration::updateValue(static::USE_PICKUP_ADDRESS, true);
        Configuration::updateValue(static::NOTIFICATIONS, true);
        Configuration::updateValue(static::NOTIFICATION_MOMENT, static::MOMENT_SCANNED);
        Configuration::updateValue(static::PAPER_SELECTION, json_encode(array(
            'size' => 'standard',
            'labels' => array(
                1 => true,
                2 => true,
                3 => true,
                4 => true,
            ),
        )), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (method_exists('Tools', 'clearCache')) {
            Tools::clearCache();
        }

        return true;
    }

    /**
     * Install DB tables
     *
     * @return bool Indicates whether the DB tables have been successfully installed
     *
     * @since 1.0.0
     * @throws PrestaShopException
     */
    protected function installSql()
    {
        if (!(MyParcelCarrierDeliverySetting::createDatabase()
            && MyParcelDeliveryOption::createDatabase()
            && MyParcelOrder::createDatabase()
            && MyParcelOrderHistory::createDatabase())
        ) {
            $this->addError(Db::getInstance()->getMsgError(), false);
            $this->uninstallSql();

            return false;
        }
        try {
            Db::getInstance()->execute(
                'ALTER TABLE `'._DB_PREFIX_.bqSQL(MyParcelDeliveryOption::$definition['table'])
                .'` ADD CONSTRAINT `id_cart` UNIQUE (`id_cart`)'
            );
        } catch (Exception $e) {
            $this->addError("MyParcel installation error: {$e->getMessage()}", false);
        }

        return true;
    }

    /**
     * Remove DB tables
     *
     * @return bool Indicates whether the DB tables have been successfully uninstalled
     *
     * @since 1.0.0
     */
    protected function uninstallSql()
    {
        try {
            if (!(MyParcelCarrierDeliverySetting::dropDatabase())) {
                $this->addError(Db::getInstance()->getMsgError());

                return false;
            }
        } catch (PrestaShopException $e) {
            $this->addError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Add a carrier
     *
     * @param string $name Carrier name
     * @param string $key  Carrier ID
     *
     * @return bool|Carrier
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function addCarrier($name, $key = self::POSTNL_DEFAULT_CARRIER)
    {
        $carrier = Carrier::getCarrierByReference(Configuration::get($key));
        if (Validate::isLoadedObject($carrier)) {
            return false; // Already added to DB
        }

        $carrier = new Carrier();

        $carrier->name = $name;
        $carrier->delay = array();
        $carrier->is_module = true;
        $carrier->active = 0;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 1;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_handling = false;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $idLang = (int) $lang['id_lang'];
            $carrier->delay[$idLang] = '-';
        }

        if ($carrier->add()) {
            /*
             * Use the Carrier ID as id_reference! Only the `id` prop has been set at this time and since it is
             * the first time this carrier is used the Carrier ID = `id_reference`
             */
            $this->addGroups($carrier);
            $this->addZones($carrier);
            $this->addPriceRange($carrier);
            Db::getInstance()->update(
                'delivery',
                array(
                    'price' => $key == static::POSTNL_DEFAULT_CARRIER ? (4.99 / 1.21) : (3.50 / 1.21),
                ),
                '`id_carrier` = '.(int) $carrier->id
            );

            $carrier->setTaxRulesGroup((int) TaxRulesGroup::getIdByName('NL Standard Rate (21%)'), true);

            @copy(
                dirname(__FILE__).'/views/img/postnl-thumb.jpg',
                _PS_SHIP_IMG_DIR_.DIRECTORY_SEPARATOR.(int) $carrier->id.'.jpg'
            );

            Configuration::updateGlobalValue($key, (int) $carrier->id);
            $deliverySetting = new MyParcelCarrierDeliverySetting();
            $deliverySetting->id_reference = $carrier->id;

            $deliverySetting->monday_cutoff = '15:30:00';
            $deliverySetting->tuesday_cutoff = '15:30:00';
            $deliverySetting->wednesday_cutoff = '15:30:00';
            $deliverySetting->thursday_cutoff = '15:30:00';
            $deliverySetting->friday_cutoff = '15:30:00';
            $deliverySetting->saturday_cutoff = '15:30:00';
            $deliverySetting->sunday_cutoff = '15:30:00';
            $deliverySetting->timeframe_days = 1;
            $deliverySetting->daytime = true;
            $deliverySetting->morning = false;
            $deliverySetting->morning_pickup = false;
            $deliverySetting->evening = false;
            $deliverySetting->signed = false;
            $deliverySetting->recipient_only = false;
            $deliverySetting->signed_recipient_only = false;
            $deliverySetting->dropoff_delay = 0;
            $deliverySetting->id_shop = $this->context->shop->id;
            $deliverySetting->morning_fee_tax_incl = 0;
            $deliverySetting->morning_pickup_fee_tax_incl = 0;
            $deliverySetting->default_fee_tax_incl = 0;
            $deliverySetting->evening_fee_tax_incl = 0;
            $deliverySetting->signed_fee_tax_incl = 0;
            $deliverySetting->recipient_only_fee_tax_incl = 0;
            $deliverySetting->signed_recipient_only_fee_tax_incl = 0;
            if ($key === static::POSTNL_DEFAULT_CARRIER) {
                $deliverySetting->monday_enabled = true;
                $deliverySetting->tuesday_enabled = true;
                $deliverySetting->wednesday_enabled = true;
                $deliverySetting->thursday_enabled = true;
                $deliverySetting->friday_enabled = true;
                $deliverySetting->saturday_enabled = false;
                $deliverySetting->sunday_enabled = false;

                $deliverySetting->delivery = true;
                $deliverySetting->pickup = true;
                $deliverySetting->mailbox_package = false;
            } else {
                $deliverySetting->monday_enabled = true;
                $deliverySetting->tuesday_enabled = true;
                $deliverySetting->wednesday_enabled = true;
                $deliverySetting->thursday_enabled = true;
                $deliverySetting->friday_enabled = true;
                $deliverySetting->saturday_enabled = false;
                $deliverySetting->sunday_enabled = false;

                $deliverySetting->pickup = false;
                $deliverySetting->delivery = false;
                $deliverySetting->mailbox_package = true;
            }
            try {
                $deliverySetting->add();
            } catch (PrestaShopException $e) {
                Logger::addLog(
                    sprintf(
                        $this->l('MyParcel: unable to save carrier settings for carrier with reference %d'),
                        $carrier->id
                    )
                );
            }

            return $carrier;
        }

        return false;
    }

    /**
     * Uninstalls the module
     *
     * @return bool Indicates whether the module has been successfully uninstalled
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    public function uninstall()
    {
        Configuration::deleteByName(static::API_KEY);

        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
        }

        if (parent::uninstall() === false) {
            return false;
        }

        return true;
    }

    /**
     * Diplay Order detail on Front Office
     *
     * @param array $params Hook parameters
     *
     * @return string HTML
     *
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    public function hookDisplayOrderDetail($params)
    {
        $this->context->smarty->assign(
            array(
                'shipments'   => MyParcelOrderHistory::getShipmentHistoryByOrderId($params['order']->id),
                'languageIso' => Tools::strtoupper($this->context->language->iso_code),
            )
        );

        return $this->display(__FILE__, 'views/templates/front/orderdetail.tpl');
    }

    /**
     * Adds JavaScript files to back office
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!Module::isEnabled($this->name)) {
            return '';
        }
        $html = '';
        if (Tools::getValue('controller') === 'AdminOrders'
            && !Tools::isSubmit('addorder')
            && !Tools::isSubmit('updateorder')
            && !Tools::isSubmit('vieworder')
        ) {
            $countries = array();
            $supportedCountries = static::getSupportedCountries();
            if (isset($supportedCountries['data']['countries'][0])) {
                $countryIsos = array_keys($supportedCountries['data']['countries'][0]);
                foreach (Country::getCountries($this->context->language->id) as &$country) {
                    if (in_array(Tools::strtoupper($country['iso_code']), $countryIsos)) {
                        $countries[Tools::strtoupper($country['iso_code'])] = array(
                            'iso_code' => Tools::strtoupper($country['iso_code']),
                            'name'     => $country['name'],
                            'region'   => $supportedCountries['data']['countries'][0]
                                          [Tools::strtoupper($country['iso_code'])]['region'],
                        );
                    }
                }
            }

            // @codingStandardsIgnoreStart
            $this->context->smarty->assign(
                array(
                    'myParcel'             => 'true',
                    'prestaShopVersion'    => Tools::substr(_PS_VERSION_, 0, 3),
                    'myparcel_process_url' => $this->moduleUrlWithoutToken.'&token='
                        .Tools::getAdminTokenLite('AdminModules').'&ajax=1',
                    'myparcel_module_url'  => __PS_BASE_URI__."modules/{$this->name}/",
                    'apiKey'               => base64_encode(Configuration::get(static::API_KEY)),
                    'jsCountries'          => $countries,
                    'paperSize'            => json_decode(Configuration::get(static::PAPER_SELECTION)),
                )
            );
            // @codingStandardsIgnoreEnd
            $html .= $this->display(__FILE__, 'views/templates/admin/ordergrid/adminvars.tpl');

            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/app/dist/ordergrid-89de0dc04f63df99.bundle.min.js');
            $this->context->controller->addCSS($this->_path.'views/css/forms.css');
        } elseif (Tools::getValue('controller') == 'AdminModules'
            && Tools::getValue('configure') == $this->name
        ) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('datepicker-nl');
            $this->context->controller->addCSS($this->_path.'views/css/forms.css');

            $this->context->smarty->assign(
                array(
                    'current_lang_iso' => Tools::strtolower(Language::getIsoById($this->context->language->id)),
                )
            );

            $html .= $this->display(__FILE__, 'views/templates/hook/initdeliverysettings.tpl');
        }

        return $html;
    }

    /**
     * Get supported counties
     *
     * @return array|bool Supported countries as associative array
     *                    false if not found
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    public static function getSupportedCountries()
    {
        $supportedCountries = json_decode(
            Configuration::get(static::SUPPORTED_COUNTRIES, null, 0, 0),
            true
        );
        if (!$supportedCountries) {
            if ($supportedCountries = static::retrieveSupportedCountries()) {
                $supportedCountries = json_decode($supportedCountries, true);
            }
        }

        return $supportedCountries;
    }

    /**
     * Configuration Page: get content of the form
     *
     * @return string Configuration page HTML
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::getValue('ajax')) {
            $this->ajaxProcess();

            return;
        }

        MyParcelCarrierDeliverySetting::createMissingColumns();
        $this->baseUrl = Context::getContext()->link->getAdminLink('AdminModules', false).'?'
            .http_build_query(array('configure' => $this->name, 'module_name' => $this->name));
        $this->moduleUrlWithoutToken = $this->baseUrl.'&token='.Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign(
            array(
                'menutabs'         => $this->initNavigation(),
                'ajaxUrl'          => $this->moduleUrlWithoutToken,
            )
        );

        foreach ($this->basicCheck() as $error) {
            $this->context->controller->errors[] = $error;
        }

        $output = '';

        $this->postProcess();

        $output .= $this->display(__FILE__, 'views/templates/admin/navbar.tpl');

        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/back.js');

        switch (Tools::getValue('menu')) {
            case static::MENU_DEFAULT_SETTINGS:
                $this->menu = static::MENU_DEFAULT_SETTINGS;
                $output .= $this->display(__FILE__, 'views/templates/admin/insuredconf.tpl');

                return $output.$this->displayDefaultSettingsForm();
            case static::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->menu = static::MENU_DEFAULT_DELIVERY_OPTIONS;

                return $output.$this->displayDeliveryOptionsPage();
            default:
                $this->menu = static::MENU_MAIN_SETTINGS;

                return $output.$this->displayMainSettingsPage();
        }
    }

    /**
     * Get the user agent string to be attached to API calls
     *
     * @return string
     */
    public static function getUserAgent()
    {
        if (defined('_TB_VERSION_')) {
            return 'User-Agent: thirty bees/'._TB_VERSION_."\r\n";
        }

        return 'User-Agent: PrestaShop/'._PS_VERSION_."\r\n";
    }

    /**
     * Main function to process ajax calls
     *
     * @return void
     * @throws PrestaShopException
     */
    protected function ajaxProcess()
    {
        $action = '';
        if (Tools::isSubmit('action')) {
            $action = Tools::getValue('action');
        } else {
            // @codingStandardsIgnoreStart
            $input = file_get_contents('php://input');
            // @codingStandardsIgnoreEnd
            if ($input) {
                $input = json_decode($input);
                if (isset($input->action)) {
                    $action = $input->action;
                }
            }
        }

        switch ($action) {
            case 'OrderInfo':
                $this->processOrderInfo();
                break;
            case 'GetShipment':
                $this->getShipmentInfo();
                break;
            case 'DeleteShipment':
                $this->deleteShipment();
                break;
            case 'CreateLabel':
                $this->createLabel();
                break;
            case 'PrintLabel':
                $this->printLabel();
                break;
            case 'CreateRelatedReturnLabel':
                $this->createRelatedReturnLabel();
                break;
            case 'CreateUnrelatedReturnLabel':
                $this->createUnrelatedReturnLabel();
                break;
            case 'SaveConcept':
                $this->saveConcept();
                break;
            default:
                header('Content-Type: text/plain');
                http_response_code(401);
                die('Unauthorized');
        }
        exit;
    }

    /**
     * @param stdClass $response
     * @param int[]    $idOrders
     * @param array    $concepts
     *
     * @returns array
     *
     * @since 2.1.0
     */
    protected function processNewLabels($response, $idOrders, $concepts)
    {
        $processedLabels = array();

        $i = 0;
        if (isset($response->data->ids) && is_array($response->data->ids)) {
            foreach ($response->data->ids as $idShipment) {
                $idShipment = $idShipment->id;
                $idOrder = (int) $idOrders[$i];

                $myparcelOrder = new MyParcelOrder();
                $myparcelOrder->id_order = $idOrder;
                $myparcelOrder->id_shipment = $idShipment;
                $myparcelOrder->postnl_status = '1';
                $myparcelOrder->retour = false;
                $myparcelOrder->postcode = $concepts[$i]->concept->recipient->postal_code;
                $myparcelOrder->postnl_final = false;
                $myparcelOrder->shipment = json_encode($concepts[$i]->concept, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if (isset($concepts[$i]->concept->pickup)) {
                    $myparcelOrder->type = static::TYPE_POST_OFFICE;
                } elseif (isset($concepts[$i]->concept->option->delivery_type)) {
                    $myparcelOrder->type = $concepts[$i]->concept->option->delivery_type;
                } else {
                    $myparcelOrder->type = static::TYPE_PARCEL;
                }

                $myparcelOrder->add();

                try {
                    $processedLabel = $myparcelOrder->getFields();
                    $processedLabel['shipment'] = $concepts[$i]->concept;
                    $processedLabel[MyParcelOrder::$definition['primary']] = $myparcelOrder->id;
                    $processedLabels[] = $processedLabel;
                } catch (PrestaShopException $e) {
                    $processedLabels[] = array();
                }

                $i++;
            }
        }

        return $processedLabels;
    }

    /**
     * Retrieve order info
     *
     * @since 2.0.0
     */
    protected function processOrderInfo()
    {
        if (!$this->active) {
            header('Content-Type: text/plain');
            http_response_code(404);
            die('MyParcel module has been disabled');
        }

        header('Content-Type: application/json');
        // @codingStandardsIgnoreStart
        $payload = json_decode(file_get_contents('php://input'), true);
        // @codingStandardsIgnoreEnd
        $orderIds = $payload['ids'];

        // Retrieve customer preferences
        die(
            json_encode(
                array(
                    'preAlerted' => MyParcelOrder::getByOrderIds($orderIds),
                    'concepts'   => MyParcelDeliveryOption::getByOrderIds($orderIds),
                ),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
    }

    /**
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    protected function getShipmentInfo()
    {
        // @codingStandardsIgnoreStart
        $apiKey = base64_encode(Configuration::get(static::API_KEY));
        // @codingStandardsIgnoreEnd

        $requestHeaders = array();
        $requestHeaders[] = "Authorization: Basic {$apiKey}";

        // @codingStandardsIgnoreStart
        $requestBody = file_get_contents('php://input');
        // @codingStandardsIgnoreEnd

        if (json_decode($requestBody)) {
            $requestBody = json_decode($requestBody);
            $moduleData = new stdClass();
            if (isset($requestBody->moduleData)) {
                $moduleData = $requestBody->moduleData;
                unset($requestBody->moduleData);
            }
            $requestBody = json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            $moduleData = null;
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders = array_values($requestHeaders);
        $requestHeaders[] = trim(MyParcel::getUserAgent());

        $shipments = implode(';', $moduleData->shipments);
        $ch = curl_init("https://api.myparcel.nl/shipments/{$shipments}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $responseContent = curl_exec($ch);
        curl_close($ch);

        $this->getShipmentApiInterceptor($responseContent);

        // finally, output the content
        header('Content-Type: application/json; charset=utf-8');
        die($responseContent);
    }

    /**
     * Delete shipment
     *
     * @since 2.1.0
     */
    protected function deleteShipment()
    {
        // @codingStandardsIgnoreStart
        $request = json_decode(file_get_contents('php://input'), true);
        // @codingStandardsIgnoreEnd
        if (isset($request['idShipment'])) {
            $idShipment = (int) $request['idShipment'][0];
            die(json_encode(array(
                'success' => MyParcelOrder::deleteShipment($idShipment),
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Intercept Get Shipment API calls
     *
     * @param string $responseContent
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function getShipmentApiInterceptor($responseContent)
    {
        if ($responseContent) {
            $responseContent = json_decode($responseContent);
            if (isset($responseContent->data->shipments) & is_array($responseContent->data->shipments)) {
                foreach ($responseContent->data->shipments as $shipment) {
                    $myparcelOrder = MyParcelOrder::getByShipmentId($shipment->id);
                    if (Validate::isLoadedObject($myparcelOrder)) {
                        if (isset($shipment->barcode) && $shipment->barcode) {
                            MyParcelOrder::updateStatus(
                                $myparcelOrder->id_shipment,
                                $shipment->barcode,
                                $shipment->status,
                                $shipment->modified
                            );
                            if (!$myparcelOrder->tracktrace) {
                                MyParcelOrder::updateOrderTrackingNumber($myparcelOrder->id_order, $shipment->barcode);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws PrestaShopException
     */
    protected function createLabel()
    {
        // @codingStandardsIgnoreStart
        $apiKey = base64_encode(Configuration::get(static::API_KEY));
        // @codingStandardsIgnoreEnd

        $requestHeaders = array();
        $requestHeaders[] = "Authorization: Basic {$apiKey}";
        $requestHeaders[] = 'Accept: application/json; charset=utf-8';
        $requestHeaders[] = 'Content-Type: application/vnd.shipment+json; charset=utf-8';
        $requestHeaders[] = trim(static::getUserAgent());

        // @codingStandardsIgnoreStart
        $requestBody = file_get_contents('php://input');
        // @codingStandardsIgnoreEnd

        $request = json_decode($requestBody);
        $idOrders = $shipments = array();
        if (isset($request->moduleData->shipments) && is_array($request->moduleData->shipments)) {
            foreach ($request->moduleData->shipments as $shipment) {
                $idOrders[] = (int) $shipment->idOrder;
                $shipments[] = $shipment->concept;
            }
        } else {
            die(json_encode(array(
                'success' => false,
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders = array_values($requestHeaders);

        $ch = curl_init('https://api.myparcel.nl/shipments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'data' => array(
                'shipments' => $shipments,
            ),
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $responseContent = curl_exec($ch);
        curl_close($ch);

        if ($response = json_decode($responseContent)) {
            $labelData = $this->processNewLabels($response, $idOrders, $request->moduleData->shipments);
            if (empty($labelData)) {
                die(json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            die(json_encode($labelData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        die(json_encode(array(
            'success' => false,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Print label
     *
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function printLabel()
    {
        // @codingStandardsIgnoreStart
        $apiKey = base64_encode(Configuration::get(static::API_KEY));
        // @codingStandardsIgnoreEnd

        $requestHeaders = array();
        $requestHeaders[] = "Authorization: Basic {$apiKey}";
        $requestHeaders[] = 'Accept: application/json; charset=utf-8';
        $requestHeaders[] = trim(static::getUserAgent());

        // @codingStandardsIgnoreStart
        $requestBody = file_get_contents('php://input');
        // @codingStandardsIgnoreEnd

        $request = json_decode($requestBody, true);
        if (is_array($request) && array_key_exists('idShipments', $request)) {
            $idShipments = $request['idShipments'];
            $shipments = implode(';', $idShipments);
        } else {
            die(json_encode(array(
                'success' => false,
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders = array_values($requestHeaders);

        // @codingStandardsIgnoreStart
        $request = file_get_contents('php://input');
        // @codingStandardsIgnoreEnd
        $request = json_decode($request, true);
        $positions = implode(';', array(1, 2, 3, 4));
        $pageSize = 'A4';
        if (isset($request['paperSize'])) {
            $pageSize = $request['paperSize']['size'] === 'standard' ? 'A4' : 'A6';
            $positions = array();
            foreach ($request['paperSize']['labels'] as $index => $pos) {
                if ($pos) {
                    $positions[] = $index;
                }
            }
            $positions = implode(';', $positions);
        }

        $ch = curl_init("https://api.myparcel.nl/shipment_labels/{$shipments}"
            ."?positions={$positions}&format={$pageSize}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $responseContent = curl_exec($ch);
        curl_close($ch);

        if ($response = json_decode($responseContent, true)) {
            $response['success'] = true;
            foreach ($idShipments as $idShipment) {
                $mpo = MyParcelOrder::getByShipmentId($idShipment);
                if (!Validate::isLoadedObject($mpo)) {
                    $response['success'] = false;
                } else {
                    $response['success'] &= $mpo->printed();
                }
            }

            die(json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        die(json_encode(array(
            'success' => false,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function createRelatedReturnLabel()
    {
        // @codingStandardsIgnoreStart
        $request = json_decode(file_get_contents('php://input'), true);
        // @codingStandardsIgnoreEnd
        if (isset($request['moduleData']['parent'])) {
            $parent = (int) $request['moduleData']['parent'];
        } else {
            die(json_encode(array(
                'success' => false,
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $sql = new DbQuery();
        $sql->select('c.`firstname`, c.`lastname`, c.`email`, mo.`id_shipment`, mo.`postcode`, o.`id_order`');
        $sql->from(bqSQL(MyParcelOrder::$definition['table']), 'mo');
        $sql->innerJoin(bqSQL(Order::$definition['table']), 'o', 'o.`id_order` = mo.`id_order`');
        $sql->innerJoin(bqSQL(Customer::$definition['table']), 'c', 'c.`id_customer` = o.`id_customer`');
        $sql->where('`id_shipment` = '.$parent);
        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        } catch (PrestaShopException $e) {
            $result = false;
        }

        if (!$result) {
            die(json_encode(array(
                'success' => false,
                'error'   => 'No shipments found in db',
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        // @codingStandardsIgnoreStart
        $ch = curl_init('https://api.myparcel.nl/shipments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/vnd.return_shipment+json;charset=utf-8',
            'Authorization: basic '.base64_encode(Configuration::get(static::API_KEY)),
            trim(static::getUserAgent()),
        ));
        // @codingStandardsIgnoreEnd
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'data' => array(
                'return_shipments' => array(
                    array(
                        'parent'  => $parent,
                        'carrier' => 1,
                        'name'    => $result['firstname'].' '.$result['lastname'],
                        'email'   => $result['email'],
                        'options' => array(
                            'package_type'   => 1,
                            'only_recipient' => 0,
                            'signature'      => 0,
                            'return'         => 0,
                            'insurance'      => array(
                                'amount'   => 50,
                                'currency' => 'EUR',
                            ),
                        ),
                    ),
                ),
            ),
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        header('Content-Type: application/json;charset=utf-8');
        if ($response && isset($response['data'])) {
            die(json_encode(
                array(
                    'success' => true,
                ),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
        }

        die(json_encode(array(
            'success' => false,
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return false|string
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function createUnrelatedReturnLabel()
    {
        // @codingStandardsIgnoreStart -- no API connection without base64
        $ch = curl_init('https://api.myparcel.nl/shipments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/vnd.return_shipment+json;charset=utf-8',
            'Authorization: basic '.base64_encode(Configuration::get(static::API_KEY)),
            trim(static::getUserAgent()),
        ));
        // @codingStandardsIgnoreEnd
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        if ($response && isset($response['data']['download_url']['link'])) {
            die(json_encode(
                array(
                    'success' => true,
                    'data'    => array(
                        'url' => $response['data']['download_url']['link'],
                    ),
                ),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
        }

        return die(json_encode(array(
             'success' => false,
        )));
    }

    /**
     * Save concept
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 1.0.0
     */
    protected function saveConcept()
    {
        // @codingStandardsIgnoreStart
        $data = json_decode(file_get_contents('php://input'));
        // @codingStandardsIgnoreEnd

        header('Content-Type: application/json');
        if (isset($data->data->concept)) {
            die(
                json_encode(
                    array(
                        'success' => (bool) MyParcelDeliveryOption::saveConcept(
                            (int) $data->data->idOrder,
                            json_encode($data->data->concept, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                        ),
                    ),
                    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                )
            );
        }

        die(json_encode(array(
            'success' => false,
        )));
    }

    /**
     * Initialize navigation
     *
     * @return array Menu items
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function initNavigation()
    {
        $menu = array(
            'main'            => array(
                'short'  => $this->l('Settings'),
                'desc'   => $this->l('Module settings'),
                'href'   => $this->baseUrl.'&menu='.static::MENU_MAIN_SETTINGS.'&token='
                    .Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-gears',
            ),
            'defaultsettings' => array(
                'short'  => $this->l('Shipping settings'),
                'desc'   => $this->l('Default shipping settings'),
                'href'   => $this->baseUrl.'&menu='.static::MENU_DEFAULT_SETTINGS.'&token='
                    .Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-truck',
            ),
            'deliveryoptions' => array(
                'short'  => $this->l('Delivery options'),
                'desc'   => $this->l('Available delivery options'),
                'href'   => $this->baseUrl.'&menu='.static::MENU_DEFAULT_DELIVERY_OPTIONS.'&token='
                    .Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-truck',
            ),
        );

        switch (Tools::getValue('menu')) {
            case static::MENU_DEFAULT_SETTINGS:
                $this->menu = static::MENU_DEFAULT_SETTINGS;
                $menu['defaultsettings']['active'] = true;
                break;
            case static::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->menu = static::MENU_DEFAULT_DELIVERY_OPTIONS;
                $menu['deliveryoptions']['active'] = true;
                break;
            default:
                $this->menu = static::MENU_MAIN_SETTINGS;
                $menu['main']['active'] = true;
                break;
        }

        return $menu;
    }

    /**
     * Process settings
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function postProcess()
    {
        switch (Tools::getValue('menu')) {
            case static::MENU_DEFAULT_SETTINGS:
                $this->postProcessDefaultSettingsPage();
                break;
            case static::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->postProcessDeliverySettingsPage();
                break;
            default:
                $this->postProcessMainSettingsPage();
                break;
        }
    }

    /**
     * Post process default settings page
     *
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function postProcessDefaultSettingsPage()
    {
        $submitted = false;

        foreach (array_keys($this->getDefaultSettingsFormValues()) as $key) {
            if (Tools::isSubmit($key)) {
                $submitted = true;
                switch ($key) {
                    case static::DEFAULT_CONCEPT_INSURED_AMOUNT:
                        $value = (int) Tools::getValue($key);
                        if ($value < 500) {
                            $value = 500;
                        }
                        Configuration::updateValue($key, $value * 100);
                        break;
                    default:
                        Configuration::updateValue($key, Tools::getValue($key));
                        break;
                }
            }
        }

        if ($submitted && empty($this->context->controller->errors)) {
            $this->addConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * Get shipping configuration form values
     *
     * @return array Configuration values
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function getDefaultSettingsFormValues()
    {
        return array(
            static::LINK_EMAIL                                =>
                Configuration::get(static::LINK_EMAIL),
            static::LINK_PHONE                                =>
                Configuration::get(static::LINK_PHONE),
            static::USE_PICKUP_ADDRESS                        =>
                Configuration::get(static::USE_PICKUP_ADDRESS),
            static::DEFAULT_CONCEPT_PARCEL_TYPE               =>
                Configuration::get(static::DEFAULT_CONCEPT_PARCEL_TYPE),
            static::DEFAULT_CONCEPT_LARGE_PACKAGE             =>
                Configuration::get(static::DEFAULT_CONCEPT_LARGE_PACKAGE),
            static::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY        =>
                Configuration::get(static::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
            static::DEFAULT_CONCEPT_SIGNED                    =>
                Configuration::get(static::DEFAULT_CONCEPT_SIGNED),
            static::DEFAULT_CONCEPT_RETURN                    =>
                Configuration::get(static::DEFAULT_CONCEPT_RETURN),
            static::DEFAULT_CONCEPT_INSURED                   =>
                Configuration::get(static::DEFAULT_CONCEPT_INSURED),
            static::DEFAULT_CONCEPT_INSURED_TYPE              =>
                Configuration::get(static::DEFAULT_CONCEPT_INSURED_TYPE),
            static::DEFAULT_CONCEPT_INSURED_AMOUNT            =>
                (int) Configuration::get(static::DEFAULT_CONCEPT_INSURED_AMOUNT) / 100,
        );
    }

    /**
     * Add confirmation message
     *
     * @param string $message Message
     * @param bool   $private Only display on module's configuration page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function addConfirmation($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->confirmations[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '
                    .$message.'</a>';
            }
        } else {
            $this->context->controller->confirmations[] = $message;
        }
    }

    /**
     * Post process delivery settings page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function postProcessDeliverySettingsPage()
    {
        if (Tools::isSubmit('submit'.MyParcelCarrierDeliverySetting::$definition['primary'])) {
            $this->postProcessDeliverySettingForm();
        } elseif (Tools::isSubmit('delivery'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::toggleDelivery(
                Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])
            )) {
                $this->addConfirmation($this->l('The status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle status'));
            }
        } elseif (Tools::isSubmit('pickup'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::togglePickup(
                Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])
            )) {
                $this->addConfirmation($this->l('The status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle status'));
            }
        } elseif (Tools::isSubmit('mailbox_package'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::toggleMailboxPackage(
                Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])
            )) {
                $this->addConfirmation($this->l('The status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle status'));
            }
        }
    }

    /**
     * Process form
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function postProcessDeliverySettingForm()
    {
        $mss = new MyParcelCarrierDeliverySetting(
            (int) Tools::getValue(
                MyParcelCarrierDeliverySetting::$definition['primary']
            )
        );
        if (!Validate::isLoadedObject($mss)) {
            $this->addError($this->l('Could not process delivery setting'));

            return;
        }

        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW} =
            (int) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW);
        $mss->{MyParcelCarrierDeliverySetting::DROPOFF_DELAY} =
            (int) Tools::getValue(MyParcelCarrierDeliverySetting::DROPOFF_DELAY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::PICKUP} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE);
        $mss->{MyParcelCarrierDeliverySetting::MORNING} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING);
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::EVENING} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::EVENING);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED);
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY);

        if ($mss->{MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW} +
            $mss->{MyParcelCarrierDeliverySetting::DROPOFF_DELAY} > 14
        ) {
            $this->addError(
                $this->l('Total of `Drop off delay` and `Amount of days to show ahead` cannot be more than 14')
            );

            return;
        }

        $mss->{MyParcelCarrierDeliverySetting::MONDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MONDAY_ENABLED);
        $mondayTime = Tools::getValue(MyParcelCarrierDeliverySetting::MONDAY_CUTOFF);
        if ($this->isTime($mondayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::MONDAY_CUTOFF} = pSQL($mondayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::TUESDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::TUESDAY_ENABLED);
        $tuesdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::TUESDAY_CUTOFF);
        if ($this->isTime($tuesdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::TUESDAY_CUTOFF} = pSQL($tuesdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::WEDNESDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::WEDNESDAY_ENABLED);
        $wednesdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::WEDNESDAY_CUTOFF);
        if ($this->isTime($wednesdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::WEDNESDAY_CUTOFF} = pSQL($wednesdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::THURSDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::THURSDAY_ENABLED);
        $thursdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::THURSDAY_CUTOFF);
        if ($this->isTime($thursdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::THURSDAY_CUTOFF} = pSQL($thursdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::FRIDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::FRIDAY_ENABLED);
        $fridayTime = Tools::getValue(MyParcelCarrierDeliverySetting::FRIDAY_CUTOFF);
        if ($this->isTime($fridayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::FRIDAY_CUTOFF} = pSQL($fridayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::SATURDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SATURDAY_ENABLED);
        $saturdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::SATURDAY_CUTOFF);
        if ($this->isTime($saturdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::SATURDAY_CUTOFF} = pSQL($saturdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::SUNDAY_ENABLED} =
            (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SUNDAY_ENABLED);
        $sundayTime = Tools::getValue(MyParcelCarrierDeliverySetting::SUNDAY_CUTOFF);
        if ($this->isTime($sundayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::SUNDAY_CUTOFF} = pSQL($sundayTime);
        }

        if (Tools::isSubmit(MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS)) {
            $mss->{MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS} =
                Tools::getValue(MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS);
        }

        $mss->{MyParcelCarrierDeliverySetting::MORNING_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_FEE);
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE);
        $mss->{MyParcelCarrierDeliverySetting::EVENING_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::EVENING_FEE);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_FEE);
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE} =
            (float) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE);

        static::processCarrierDeliverySettingsRestrictions($mss);
        $mss->save();
    }

    /**
     * Check if time input is correct
     *
     * @param string $input Input
     *
     * @return bool Time format is correct
     *
     * @since 2.0.0
     */
    protected static function isTime($input)
    {
        return preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $input);
    }

    /**
     * @param MyParcelCarrierDeliverySetting $myparcelCarrierDeliverySetting
     *
     * @return void
     *
     * @since 2.0.0
     */
    public static function processCarrierDeliverySettingsRestrictions(&$myparcelCarrierDeliverySetting)
    {
        if ($myparcelCarrierDeliverySetting->mailbox_package) {
            $myparcelCarrierDeliverySetting->delivery = false;
            $myparcelCarrierDeliverySetting->pickup = false;
        } elseif ($myparcelCarrierDeliverySetting->delivery || $myparcelCarrierDeliverySetting->pickup) {
            $myparcelCarrierDeliverySetting->mailbox_package = false;
        }
    }

    /**
     * Post process main settings page
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function postProcessMainSettingsPage()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $validUser = false;
            $validApi = false;

            // api key
            $apiKey = (string) Tools::getValue(static::API_KEY);
            if (!$apiKey
                || empty($apiKey)
                || !Validate::isGenericName($apiKey)
            ) {
                $this->addError($this->l('Invalid Api Key'));
            } else {
                $validApi = true;
                $previousApiKey = Configuration::get(static::API_KEY);
                if ($apiKey !== $previousApiKey) {
                    Configuration::deleteByName(static::WEBHOOK_ID);
                    Configuration::deleteByName(static::WEBHOOK_LAST_CHECK);
                }

                Configuration::updateValue(static::API_KEY, $apiKey);
            }

            if ($validUser && $validApi) {
                $this->addConfirmation($this->l('Settings updated'));
            }

            Configuration::updateValue(static::CHECKOUT_FG_COLOR1, Tools::getValue(static::CHECKOUT_FG_COLOR1));
            Configuration::updateValue(static::CHECKOUT_FG_COLOR2, Tools::getValue(static::CHECKOUT_FG_COLOR2));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR1, Tools::getValue(static::CHECKOUT_BG_COLOR1));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR2, Tools::getValue(static::CHECKOUT_BG_COLOR2));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR3, Tools::getValue(static::CHECKOUT_BG_COLOR3));
            Configuration::updateValue(static::CHECKOUT_HL_COLOR, Tools::getValue(static::CHECKOUT_HL_COLOR));
            Configuration::updateValue(static::CHECKOUT_FONT, Tools::getValue(static::CHECKOUT_FONT));
            Configuration::updateValue(
                static::CHECKOUT_FONT_SIZE,
                (int) Tools::getValue(static::CHECKOUT_FONT_SIZE)
                    ? (int) Tools::getValue(static::CHECKOUT_FONT_SIZE)
                    : 14
            );
            Configuration::updateValue(
                static::UPDATE_ORDER_STATUSES,
                (bool) Tools::getValue(static::UPDATE_ORDER_STATUSES)
            );
            Configuration::updateValue(static::LOG_API, (bool) Tools::getValue(static::LOG_API));
            Configuration::updateValue(static::PRINTED_STATUS, (int) Tools::getValue(static::PRINTED_STATUS));
            Configuration::updateValue(static::SHIPPED_STATUS, (int) Tools::getValue(static::SHIPPED_STATUS));
            Configuration::updateValue(static::RECEIVED_STATUS, (int) Tools::getValue(static::RECEIVED_STATUS));
            Configuration::updateValue(static::NOTIFICATIONS, (bool) Tools::getValue(static::NOTIFICATIONS));
            Configuration::updateValue(static::NOTIFICATION_MOMENT, Tools::getValue(static::NOTIFICATION_MOMENT) ? 1 : 0);
            Configuration::updateValue(static::LABEL_DESCRIPTION, Tools::getValue(static::LABEL_DESCRIPTION));
            Configuration::updateValue(static::PAPER_SELECTION, Tools::getValue(static::PAPER_SELECTION));
        }
    }

    /**
     * Everything necessary to display the whole form.
     *
     * @return string HTML for the bo page
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function displayDefaultSettingsForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'status';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&menu='
            .static::MENU_DEFAULT_SETTINGS;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getDefaultSettingsFormValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        $forms = array(
            $this->getDefaultConceptsForm(),
            $this->getDefaultSettingsForm(),
        );

        return $helper->generateForm($forms);
    }

    /**
     * Create the structure of the config form
     *
     * @return array
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function getDefaultConceptsForm()
    {
        $currency = Currency::getDefaultCurrency();

        return array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Default concept'),
                    'icon'  => 'icon-files-o',
                ),
                'description' => $this->l('These are the default concept settings'),
                'input'       => array(
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Default parcel type'),
                        'name'     => static::DEFAULT_CONCEPT_PARCEL_TYPE,
                        'options'  => array(
                            'query' => array(
                                array(
                                    'id'   => static::TYPE_PARCEL,
                                    'name' => $this->l('Parcel'),
                                ),
                                array(
                                    'id'   => static::TYPE_MAILBOX_PACKAGE,
                                    'name' => $this->l('Brievenbuspakje'),
                                ),
                                array(
                                    'id'   => static::TYPE_UNSTAMPED,
                                    'name' => $this->l('Unstamped'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Extra large parcel'),
                        'name'    => static::DEFAULT_CONCEPT_LARGE_PACKAGE,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Home delivery only'),
                        'name'    => static::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Signature'),
                        'name'    => static::DEFAULT_CONCEPT_SIGNED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Return when not home'),
                        'name'    => static::DEFAULT_CONCEPT_RETURN,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Insured'),
                        'name'    => static::DEFAULT_CONCEPT_INSURED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Amount'),
                        'name'    => static::DEFAULT_CONCEPT_INSURED_TYPE,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => static::INSURED_TYPE_50,
                                    'name' => $this->l('€50'),
                                ),
                                array(
                                    'id'   => static::INSURED_TYPE_250,
                                    'name' => $this->l('€250'),
                                ),
                                array(
                                    'id'   => static::INSURED_TYPE_500,
                                    'name' => $this->l('€500'),
                                ),
                                array(
                                    'id'   => static::INSURED_TYPE_500_PLUS,
                                    'name' => $this->l('More than €500'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Custom value (more than €500)'),
                        'name'     => static::DEFAULT_CONCEPT_INSURED_AMOUNT,
                        'size'     => 10,
                        'prefix'   => $currency->sign,
                        'class'    => 'fixed-width-sm',
                        'currency' => (version_compare(_PS_VERSION_, '1.6', '<')) ? false : true,
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the structure of the config form
     *
     * @return array
     *
     * @since 2.0.0
     */
    protected function getDefaultSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Data'),
                    'icon'  => 'icon-filter',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Share customer\'s email address with MyParcel'),
                        'desc'    =>
                            $this->l('Sharing the customer\'s email address with MyParcel makes sure that')
                            .' '
                            .$this->l('MyParcel can send a Track and Trace email. You can configure the')
                            .' '
                            .$this->l('email settings in the MyParcel back office.'),
                        'name'    => static::LINK_EMAIL,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Share customer\'s phone number with MyParcel'),
                        'desc'    =>
                            $this->l('When sharing the customer\'s phone number with MyParcel the')
                            .' '
                            .$this->l('carrier can use this phone number for delivery.')
                            .' '
                            .$this->l('This greatly increases the chance of a successful delivery')
                            .' '
                            .$this->l('when sending shipments abroad.'),
                        'name'    => static::LINK_PHONE,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use the pickup location address'),
                        'desc'    =>
                            $this->l('When enabled, the pickup location\'s address will be set as')
                            .' '
                            .$this->l('the customer\'s delivery address.'),
                        'name'    => static::USE_PICKUP_ADDRESS,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function displayDeliveryOptionsPage()
    {
        $output = '';

        $this->updateCarriers();

        $this->context->controller->addJS($this->_path.'views/js/forms.js');
        $this->context->controller->addCSS($this->_path.'views/css/forms.css');

        if (Tools::isSubmit('delivery'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            $this->removeOldExceptions(Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']));
        }

        if (Tools::isSubmit(MyParcelCarrierDeliverySetting::$definition['primary'])
            && Tools::isSubmit('add'.MyParcelCarrierDeliverySetting::$definition['table'])
            || Tools::isSubmit('update'.MyParcelCarrierDeliverySetting::$definition['table'])
        ) {
            $output .= $this->renderDeliveryOptionForm();
        } else {
            try {
                $output .= $this->renderDeliveryOptionList();
            } catch (PrestaShopException $e) {
            }
        }

        return $output;
    }

    /**
     * Update carriers
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function updateCarriers()
    {
        $carriers = Carrier::getCarriers(
            Context::getContext()->language->id,
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']));
        try {
            $currentList = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            $currentList = array();
        }

        foreach ($carriers as $carrier) {
            $found = false;
            foreach ($currentList as $current) {
                if ($carrier['id_reference'] == $current['id_reference']) {
                    $found = true;
                    break;
                }
            }
            if (!$found && !empty($carrier['id_reference'])) {
                try {
                    Db::getInstance()->insert(
                        bqSQL(MyParcelCarrierDeliverySetting::$definition['table']),
                        array(
                            'id_reference'                           => (int) $carrier['id_reference'],
                            MyParcelCarrierDeliverySetting::DELIVERY => false,
                            MyParcelCarrierDeliverySetting::PICKUP   => false,
                            'id_shop'                                => $this->getShopId(),
                        )
                    );
                } catch (PrestaShopException $e) {
                    Logger::AddLog("MyParcel module - unable to add carrier setting: {$e->getMessage()}");
                }
            }
        }
    }

    /**
     * Get the Shop ID of the current context
     * Retrieves the Shop ID from the cookie
     *
     * @return int Shop ID
     *
     * @since 2.0.0
     */
    protected function getShopId()
    {
        if (isset(Context::getContext()->employee->id)
            && Context::getContext()->employee->id && Shop::getContext() == Shop::CONTEXT_SHOP
        ) {
            $cookie = Context::getContext()->cookie->getFamily('shopContext');

            return (int) Tools::substr($cookie['shopContext'], 2, count($cookie['shopContext']));
        }

        return (int) Context::getContext()->shop->id;
    }

    /**
     * Clean up old dates from exception schemes
     *
     * @param int $idMyParcelDeliveryOption MyParcel Delivery Option ID
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function removeOldExceptions($idMyParcelDeliveryOption)
    {
        $samedayDeliveryOption = new MyParcelCarrierDeliverySetting($idMyParcelDeliveryOption);
        if (Validate::isLoadedObject($samedayDeliveryOption)) {
            $exceptions = json_decode($samedayDeliveryOption->cutoff_exceptions, true);
            if (is_array($exceptions)) {
                $exceptionDates = array_keys($exceptions);
                for ($i = 0; $i < count($exceptionDates); $i++) {
                    if (strtotime($exceptionDates[$i]) < time()) {
                        $dateToRemove = $exceptionDates[$i];
                        unset($exceptions[$dateToRemove]);
                    }
                }
                if (empty($exceptions)) {
                    $samedayDeliveryOption->cutoff_exceptions = '{}';
                } else {
                    $samedayDeliveryOption->cutoff_exceptions = json_encode($exceptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            $samedayDeliveryOption->save();
        }
    }

    /**
     * Display forms
     *
     * @return string Forms HTML
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function renderDeliveryOptionForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.MyParcelCarrierDeliverySetting::$definition['primary'];
        $helper->currentIndex = $this->baseUrl.'&menu='.static::MENU_DEFAULT_DELIVERY_OPTIONS;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getDeliveryOptionsFormValues(
                (int) Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])
            ),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getDeliverySettingForm(), $this->getCutoffForm()));
    }

    /**
     * Set values for the inputs of the configuration form
     *
     * @param int $idMyParcelCarrierDeliverySetting MyParcel Delivery Option ID
     *
     * @return array Array with current values
     *
     * @since 2.0.0
     */
    protected function getDeliveryOptionsFormValues($idMyParcelCarrierDeliverySetting)
    {
        $mcds = new MyParcelCarrierDeliverySetting($idMyParcelCarrierDeliverySetting);
        $mcds->{MyParcelCarrierDeliverySetting::$definition['primary']} = $mcds->id;

        return (array) $mcds;
    }

    /**
     * Create the structure of the extra form
     *
     * @return array Form array
     * @throws PrestaShopException
     */
    protected function getDeliverySettingForm()
    {
        $deliveryDaysOptions = array(
            array(
                'id'   => -1,
                'name' => $this->l('Hide days'),
            ),
        );
        for ($i = 1; $i < 15; $i++) {
            $deliveryDaysOptions[] = array(
                'id'   => $i,
                'name' => sprintf($this->l('%d days'), $i),
            );
        }

        $dropoffDelayOptions = array(
            array(
                'id'   => 0,
                'name' => $this->l('No delay'),
            ),
            array(
                'id'   => 1,
                'name' => $this->l('1 day'),
            ),
        );
        for ($i = 2; $i < 15; $i++) {
            $dropoffDelayOptions[] = array(
                'id'   => $i,
                'name' => sprintf($this->l('%d days'), $i),
            );
        }

        $currency = Currency::getDefaultCurrency();

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Delivery options'),
                    'icon'  => 'icon-truck',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Mailbox package'),
                        'name'    => MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Timeframes'),
                        'desc'    => $this->l('Show available timeframes'),
                        'name'    => MyParcelCarrierDeliverySetting::DELIVERY,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Amount of days to show ahead'),
                        'name'     => MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW,
                        'required' => true,
                        'options'  => array(
                            'query' => $deliveryDaysOptions,
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Drop off delay'),
                        'name'     => MyParcelCarrierDeliverySetting::DROPOFF_DELAY,
                        'required' => true,
                        'options'  => array(
                            'query' => $dropoffDelayOptions,
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Morning delivery'),
                        'desc'    => $this->l('Morning delivery before 12:00 PM'),
                        'name'    => MyParcelCarrierDeliverySetting::MORNING,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Morning delivery fee'),
                        'desc'     => $this->l('Extra fee for morning delivery'),
                        'name'     => MyParcelCarrierDeliverySetting::MORNING_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Evening delivery'),
                        'name'    => MyParcelCarrierDeliverySetting::EVENING,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Evening delivery fee'),
                        'desc'     => $this->l('Extra fee for evening delivery'),
                        'name'     => MyParcelCarrierDeliverySetting::EVENING_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Signed'),
                        'name'    => MyParcelCarrierDeliverySetting::SIGNED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Signed fee'),
                        'desc'     => $this->l('Extra fee for signed'),
                        'name'     => MyParcelCarrierDeliverySetting::SIGNED_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Recipient only'),
                        'name'    => MyParcelCarrierDeliverySetting::RECIPIENT_ONLY,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Fee for recipient only'),
                        'desc'     => $this->l('Extra fee for recipient only'),
                        'name'     => MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Fee for signed + recipient only'),
                        'desc'     => $this->l('Extra fee for signed + recipient only (when combined)'),
                        'name'     => MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Post Offices'),
                        'desc'    => $this->l('Show available post offices'),
                        'name'    => MyParcelCarrierDeliverySetting::PICKUP,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Morning pickup'),
                        'desc'    => $this->l('Morning pickup from 8:30 AM'),
                        'name'    => MyParcelCarrierDeliverySetting::MORNING_PICKUP,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'price'    => true,
                        'label'    => $this->l('Morning pickup fee'),
                        'desc'     => $this->l('Extra fee for morning pickup'),
                        'name'     => MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Create the structure of the cut off form
     *
     * @return array Form array
     */
    protected function getCutoffForm()
    {
        return array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Next day delivery'),
                    'icon'  => 'icon-clock-o',
                ),
                'description' => (date_default_timezone_get() === 'Europe/Amsterdam')
                    ? '' :
                    sprintf(
                        $this->l('The module assumes that you are using the following timezone: %s'),
                        ini_get('date.timezone')
                    ),
                'input'       => array(
                    array(
                        'type' => 'hidden',
                        'name' => MyParcelCarrierDeliverySetting::$definition['primary'],
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Monday'),
                        'name'    => MyParcelCarrierDeliverySetting::MONDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Monday'),
                        'name'  => MyParcelCarrierDeliverySetting::MONDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Tuesday'),
                        'name'    => MyParcelCarrierDeliverySetting::TUESDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Tuesday'),
                        'name'  => MyParcelCarrierDeliverySetting::TUESDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Wednesday'),
                        'name'    => MyParcelCarrierDeliverySetting::WEDNESDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Wednesday'),
                        'name'  => MyParcelCarrierDeliverySetting::WEDNESDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Thursday'),
                        'name'    => MyParcelCarrierDeliverySetting::THURSDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Thursday'),
                        'name'  => MyParcelCarrierDeliverySetting::THURSDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Friday'),
                        'name'    => MyParcelCarrierDeliverySetting::FRIDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Friday'),
                        'name'  => MyParcelCarrierDeliverySetting::FRIDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Saturday'),
                        'name'    => MyParcelCarrierDeliverySetting::SATURDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Saturday'),
                        'name'  => MyParcelCarrierDeliverySetting::SATURDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Dispatch on Sunday'),
                        'name'    => MyParcelCarrierDeliverySetting::SUNDAY_ENABLED,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'  => 'time',
                        'label' => $this->l('Cut-off time on Sunday'),
                        'name'  => MyParcelCarrierDeliverySetting::SUNDAY_CUTOFF,
                    ),
                    array(
                        'type' => 'hr',
                        'name' => '',
                    ),
                    array(
                        'type'  => 'cutoffexceptions',
                        'label' => $this->l('Exception schedule'),
                        'name'  => MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS,
                    ),
                ),
                'submit'      => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Function used to render the list to display for this controller
     *
     * @return string|false
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function renderDeliveryOptionList()
    {
        $fieldsList = array(
            bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']) => array('title' => $this->l('ID')),
            'name'                                                        => array('title' => $this->l('Name')),
            MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE               => array(
                'title'  => $this->l('Brievenbuspakje'),
                'type'   => 'bool',
                'active' => MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE,
                'ajax'   => false,
                'align'  => 'center',
            ),
            MyParcelCarrierDeliverySetting::DELIVERY                      => array(
                'title'  => $this->l('Timeframes enabled'),
                'type'   => 'bool',
                'active' => MyParcelCarrierDeliverySetting::DELIVERY,
                'ajax'   => false,
                'align'  => 'center',

            ),
            MyParcelCarrierDeliverySetting::PICKUP                        => array(
                'title'  => $this->l('Post offices enabled'),
                'type'   => 'bool',
                'active' => MyParcelCarrierDeliverySetting::PICKUP,
                'ajax'   => false,
                'align'  => 'center',
            ),
            'cutoff_times'                                                => array(
                'title'           => $this->l('Cut off times'),
                'type'            => 'cutoff_times',
                'align'           => 'center',
                'orderby'         => false,
                'search'          => false,
                'class'           => 'sameday-cutoff-labels',
                'callback'        => 'printCutOffItems',
                'callback_object' => 'MyParcelTools',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = array('edit');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->identifier = bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']);
        $helper->title = $this->l('Cutoff times');
        $helper->table = MyParcelCarrierDeliverySetting::$definition['table'];
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->baseUrl.'&menu='.static::MENU_DEFAULT_DELIVERY_OPTIONS;
        $helper->colorOnBackground = true;
        $helper->no_link = true;
        $list = $this->getDeliveryOptionsList($helper);
        $helper->listTotal = count($list);

        foreach ($list as $carrier) {
            if ($carrier['external_module_name'] && $carrier['external_module_name'] !== $this->name) {
                $this->context->controller->warnings[] =
                    $this->l('Some carriers are managed by external modules.')
                    .' '.
                    $this->l('Delivery options will not be available for these carriers.');
                break;
            }
        }

        return $helper->generateList($list, $fieldsList);
    }

    /**
     * Get the current objects' list form the database
     *
     * @param HelperList $helper
     *
     * @throws PrestaShopException
     *
     * @return array
     *
     * @since 2.0.0
     */
    protected function getDeliveryOptionsList(HelperList $helper)
    {
        $sql = new DbQuery();
        $sql->select('mcds.*, c.`name`, c.`external_module_name`');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']), 'mcds');
        $sql->innerJoin('carrier', 'c', 'mcds.`id_reference` = c.`id_reference` AND c.`deleted` = 0');
        $sql->where('mcds.`id_shop` = '.(int) $this->context->shop->id);

        $list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $skipList = array();

        foreach ($list as &$samedaySetting) {
            $cutoffExceptions = json_decode(
                $samedaySetting[MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS],
                true
            );
            if (!is_array($cutoffExceptions)) {
                $cutoffExceptions = array();
            }

            $cutoffTimes = array();
            $date = new DateTime('NOW');
            for ($i = 0; $i < 7; $i++) {
                if (array_key_exists($date->format('d-m-Y'), $cutoffExceptions)) {
                    $exceptionInfo = $cutoffExceptions[$date->format('d-m-Y')];

                    if ((array_key_exists('nodispatch', $exceptionInfo) && $exceptionInfo['nodispatch'])
                        && (array_key_exists('cutoff', $exceptionInfo))
                    ) {
                        $nodispatch = false;
                    } else {
                        $nodispatch = true;
                    }

                    $cutoffTimes[$i] = array(
                        'name'       => $this->l($date->format('D')),
                        'time'       => (array_key_exists('cutoff', $exceptionInfo) ? $exceptionInfo['cutoff'] : ''),
                        'exception'  => true,
                        'nodispatch' => $nodispatch,
                    );
                } elseif ((bool) $samedaySetting[Tools::strtolower($date->format('l')).'_enabled']) {
                    $cutoffTimes[$i] = array(
                        'name'       => $this->l($date->format('D')),
                        'time'       => $samedaySetting[Tools::strtolower($date->format('l')).'_cutoff'],
                        'exception'  => false,
                        'nodispatch' => false,
                    );
                } else {
                    $cutoffTimes[$i] = array(
                        'name'       => $this->l($date->format('D')),
                        'time'       => '',
                        'exception'  => false,
                        'nodispatch' => true,
                    );
                }
                $date->modify('+1 day');
            }

            $samedaySetting['cutoff_times'] = $cutoffTimes;
            if ($samedaySetting['external_module_name'] && $samedaySetting['external_module_name'] != $this->name) {
                $samedaySetting['color'] = '#E08F95';
                $samedaySetting[MyParcelCarrierDeliverySetting::PICKUP] = null;
                $samedaySetting[MyParcelCarrierDeliverySetting::DELIVERY] = null;
                $samedaySetting[MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE] = null;
                $samedaySetting['cutoff_times'] = null;
                $skipList[] = $samedaySetting[MyParcelCarrierDeliverySetting::$definition['primary']];
            }
        }
        $helper->list_skip_actions['edit'] = $skipList;

        return $list;
    }

    /**
     * Display main settings page
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function displayMainSettingsPage()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addCSS($this->_path.'views/css/fontselect.css', 'all');
        $this->context->controller->addJS($this->_path.'views/js/fontselect.js');

        return $this->displayMainForm();
    }

    /**
     * Configuration Page: display form
     *
     * @return string Main page form HTML
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function displayMainForm()
    {
        // Get default language
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='
                    .Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ),
        );
        $helper->fields_value = $this->getMainFormValues();

        $this->context->controller->addJS($this->_path.'views/js/app/dist/checkout-89de0dc04f63df99.bundle.min.js');
        $this->context->controller->addJS($this->_path.'views/js/app/dist/paperselector-89de0dc04f63df99.bundle.min.js');

        return $helper->generateForm(array(
            $this->getApiForm(),
            $this->getCheckoutForm(),
            $this->getLabelForm(),
            $this->getNotificationForm(),
            $this->getAdvancedForm(),
        ));
    }

    /**
     * Get Main form configuration values
     *
     * @return array Configuration values
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function getMainFormValues()
    {
        return array(
            static::API_KEY               => Configuration::get(static::API_KEY),
            static::CHECKOUT_FG_COLOR1    => Configuration::get(static::CHECKOUT_FG_COLOR1) ?: '#FFFFFF',
            static::CHECKOUT_FG_COLOR2    => Configuration::get(static::CHECKOUT_FG_COLOR2 ?: '#000000'),
            static::CHECKOUT_BG_COLOR1    => Configuration::get(static::CHECKOUT_BG_COLOR1 ?: '#FBFBFB'),
            static::CHECKOUT_BG_COLOR2    => Configuration::get(static::CHECKOUT_BG_COLOR2) ?: '#01BBC5',
            static::CHECKOUT_BG_COLOR3    => Configuration::get(static::CHECKOUT_BG_COLOR3) ?: '#75D3D8',
            static::CHECKOUT_HL_COLOR     => Configuration::get(static::CHECKOUT_HL_COLOR) ?: '#FF8C00',
            static::CHECKOUT_FONT         => Configuration::get(static::CHECKOUT_FONT) ?: 'Exo',
            static::CHECKOUT_FONT_SIZE    => Configuration::get(static::CHECKOUT_FONT_SIZE) ?: 2,
            static::UPDATE_ORDER_STATUSES => Configuration::get(static::UPDATE_ORDER_STATUSES),
            static::LOG_API               => Configuration::get(static::LOG_API),
            static::PRINTED_STATUS        => Configuration::get(static::PRINTED_STATUS),
            static::SHIPPED_STATUS        => Configuration::get(static::SHIPPED_STATUS),
            static::RECEIVED_STATUS       => Configuration::get(static::RECEIVED_STATUS),
            static::NOTIFICATIONS         => Configuration::get(static::NOTIFICATIONS),
            static::NOTIFICATION_MOMENT   => Configuration::get(static::NOTIFICATION_MOMENT),
            static::LABEL_DESCRIPTION     => Configuration::get(static::LABEL_DESCRIPTION),
            static::PAPER_SELECTION       => Configuration::get(static::PAPER_SELECTION),
        );
    }

    /**
     * Get the API form
     *
     * @return array Form
     */
    protected function getApiForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('MyParcel API'),
                    'icon'  => 'icon-server',
                ),
                'input'  => array(
                    array(
                        'type'      => 'text',
                        'label'     => $this->l('MyParcel API Key'),
                        'name'      => static::API_KEY,
                        'size'      => 50,
                        'maxlength' => 50,
                        'required'  => true,
                    ),
                ),
                'cancel' => array(
                    'title' => 'cancel',
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get the notification form
     *
     * @return array Form
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getNotificationForm()
    {
        $shippedStatus = new OrderState(Configuration::get('PS_OS_SHIPPING'), $this->context->language->id);
        $deliveredStatus = new OrderState(Configuration::get('PS_OS_DELIVERED'), $this->context->language->id);
        if (!Validate::isLoadedObject($shippedStatus)) {
            $shippedStatus = array(
                'name' => $this->l('Verzonden'),
            );
        }
        if (!Validate::isLoadedObject($deliveredStatus)) {
            $deliveredStatus = array(
                'name' => $this->l('Afgeleverd'),
            );
        }
        $orderStatuses = array(
            array(
                'name'           => $this->l('Disable this status'),
                'id_order_state' => '0',
            ),
        );
        $orderStatuses = array_merge($orderStatuses, OrderState::getOrderStates($this->context->language->id));

        for ($i = 0; $i < count($orderStatuses); $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'].' - '.$orderStatuses[$i]['name'];
        }

        $this->aasort($orderStatuses, 'id_order_state');

        $this->context->smarty->assign(array(
            'shippedStatusName'   => $shippedStatus->name,
            'deliveredStatusName' => $deliveredStatus->name,
        ));
        try {
            $orderStatusDescription = $this->display(__FILE__, 'views/templates/hook/orderstatusinfo.tpl');
        } catch (Exception $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");
        }

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Notifications'),
                    'icon'  => 'icon-bell',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Automate order statuses'),
                        'desc'    => $orderStatusDescription,
                        'name'    => static::UPDATE_ORDER_STATUSES,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Printed status'),
                        'desc'     => $this->l('Apply this status when the label has been printed'),
                        'name'     => static::PRINTED_STATUS,
                        'options'  => array(
                            'query'   => $orderStatuses,
                            'id'      => 'id_order_state',
                            'name'    => 'name',
                            'orderby' => 'id_order_state',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Shipped status'),
                        'desc'     => $this->l('Apply this status when the order has been received by PostNL'),
                        'name'     => static::SHIPPED_STATUS,
                        'options'  => array(
                            'query'   => $orderStatuses,
                            'id'      => 'id_order_state',
                            'name'    => 'name',
                            'orderby' => 'id_order_state',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Received'),
                        'desc'     => $this->l('Apply this status when the order has been received by your customer'),
                        'name'     => static::RECEIVED_STATUS,
                        'options'  => array(
                            'query' => $orderStatuses,
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => sprintf(
                            $this->l('Send notification emails via %s'),
                            defined('_TB_VERSION_') ? 'thirty bees' : 'PrestaShop'
                        ),
                        'name'    => static::NOTIFICATIONS,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Send a notification when'),
                        'desc'     => $this->l('NOTE: sending a notification while printing may slow it down'),
                        'name'     => static::NOTIFICATION_MOMENT,
                        'options'  => array(
                            'query' => array(
                                array(
                                    'id_moment' => static::MOMENT_PRINTED,
                                    'name' => $this->l('the label has been printed'),
                                ),
                                array(
                                    'id_moment' => static::MOMENT_SCANNED,
                                    'name' => $this->l('the parcel has been scanned by PostNL'),
                                ),
                            ),
                            'id'    => 'id_moment',
                            'name'  => 'name',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                ),
                'cancel' => array(
                    'title' => 'cancel',
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get the checkout form
     *
     * @return array Form
     */
    protected function getCheckoutForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Checkout'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Header text color'),
                        'name'     => static::CHECKOUT_FG_COLOR1,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Body text color'),
                        'name'     => static::CHECKOUT_FG_COLOR2,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Body background color'),
                        'name'     => static::CHECKOUT_BG_COLOR1,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Selected tab color'),
                        'name'     => static::CHECKOUT_BG_COLOR2,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Deselected tab color'),
                        'name'     => static::CHECKOUT_BG_COLOR3,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Highlight color'),
                        'name'     => static::CHECKOUT_HL_COLOR,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'  => 'fontselect',
                        'label' => $this->l('Font family'),
                        'name'  => static::CHECKOUT_FONT,
                    ),
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Font size'),
                        'name'     => static::CHECKOUT_FONT_SIZE,
                        'options'  => array(
                            'query' => array(
                                array('id' => static::FONT_SMALL, 'name' => $this->l('Small')),
                                array('id' => static::FONT_MEDIUM, 'name' => $this->l('Medium')),
                                array('id' => static::FONT_LARGE, 'name' => $this->l('Large')),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'label'    => $this->l('Preview'),
                        'name'     => '',
                        'type'     => 'checkout',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get the label form
     *
     * @return array Form
     * @throws Exception
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function getLabelForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Labels'),
                    'icon'  => 'icon-file-text',
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Label description'),
                        'name'     => static::LABEL_DESCRIPTION,
                        'size'     => 50,
                        'desc'     => $this->display(__FILE__, 'views/templates/admin/labeldesc.tpl'),
                    ),
                    array(
                        'label' => $this->l('Default page size'),
                        'name' => static::PAPER_SELECTION,
                        'type' => 'paperselector',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Get the advanced form
     *
     * @return array Form
     */
    protected function getAdvancedForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Advanced'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('API logger'),
                        'desc'    => $this->l('By enabling this option, API calls are being logged.')
                            .' '
                            .$this->l('They can be found on the page `Advanced Parameters > Logs`.'),
                        'name'    => static::LOG_API,
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => true,
                                'label' => Translate::getAdminTranslation('Enabled', 'AdminCarriers'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => false,
                                'label' => Translate::getAdminTranslation('Disabled', 'AdminCarriers'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Display before carrier
     *
     * @return string
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    public function hookDisplayBeforeCarrier()
    {
        $smartyVars = $this->context->smarty->getTemplateVars();
        if (!isset($smartyVars['widgetHook'])) {
            $this->context->smarty->assign('widgetHook', 'beforeCarrier');
        }

        $this->context->smarty->assign(
            array(
                'myparcel_checkout_link'        =>
                    $this->context->link->getModuleLink(
                        $this->name,
                        'myparcelcheckout',
                        array(),
                        Tools::usingSecureMode()
                    ),
                'myparcel_deliveryoptions_link' =>
                    $this->context->link->getModuleLink(
                        $this->name,
                        'deliveryoptions',
                        array(),
                        Tools::usingSecureMode()
                    ),
                'link' => $this->context->link,
            )
        );

        /** @var Cart $cart */
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            if (Configuration::get(static::LOG_API)) {
                Logger::addLog("{$this->displayName}: No valid cart found");
            }

            return '';
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->display(__FILE__, 'views/templates/hook/beforecarrier17.tpl');
        }

        $address = new Address((int) $cart->id_address_delivery);
        if (!preg_match(MyParcel::SPLIT_STREET_REGEX, MyParcelTools::getAddressLine($address))) {
            // No house number
            if (Configuration::get(static::LOG_API)) {
                Logger::addLog("{$this->displayName}: No housenumber for Cart {$cart->id}");
            }

            return '';
        }

        $carrier = new Carrier($cart->id_carrier);
        if (!Validate::isLoadedObject($carrier)) {
            $idZone = (int) Country::getIdZone($address->id_country);
            $availableCarriers = Carrier::getCarriersForOrder($idZone, null, $cart);
            if (isset($availableCarriers[0])) {
                $this->carrier = new Carrier((int) $availableCarriers[0]['id_carrier']);
            }
        }

        $mcds = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!Validate::isLoadedObject($mcds)) {
            if (Configuration::get(static::LOG_API)) {
                Logger::addLog("{$this->displayName}: Cannot retrieve settings from the database");
            }

            return '';
        }


        if ($mcds->delivery || $mcds->pickup) {
            return $this->display(__FILE__, 'views/templates/hook/beforecarrier.tpl');
        }

        return '';
    }

    /**
     * Display before carrier hook
     *
     * @return string Hook HTML
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    public function hookDisplayCarrierList()
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            // Deprecated on 1.7, hook to displayBeforeCarrier instead
            return '';
        }

        // Do not display if already hooked to `displayBeforeCarrier`
        if ($moduleList = Hook::getModulesFromHook(Hook::getIdByName('displayBeforeCarrier'))) {
            foreach ($moduleList as $module) {
                if ($module['name'] === $this->name) {
                    return '';
                }
            }
        }

        $this->context->smarty->assign('widgetHook', 'extraCarrier');

        return $this->hookDisplayBeforeCarrier();
    }

    /**
     * Hook on admin order page
     *
     * @param array $params Hook parameters
     *
     * @return string Hook HTML
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    public function hookAdminOrder($params)
    {
        $countries = array();
        $supportedCountries = static::getSupportedCountries();
        if (isset($supportedCountries['data']['countries'][0])) {
            $countryIsos = array_keys($supportedCountries['data']['countries'][0]);
            foreach (Country::getCountries($this->context->language->id) as $country) {
                if (in_array(Tools::strtoupper($country['iso_code']), $countryIsos)) {
                    $countries[Tools::strtoupper($country['iso_code'])] = array(
                        'iso_code' => Tools::strtoupper($country['iso_code']),
                        'name'     => $country['name'],
                        'region'   => $supportedCountries['data']['countries'][0]
                                      [Tools::strtoupper($country['iso_code'])]['region'],
                    );
                }
            }
        }

        $order = new Order($params['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $this->context->smarty->assign(
            array(
                'idOrder'             => (int) $params['id_order'],
                'concept'             => MyParcelDeliveryOption::getByOrder((int) $params['id_order']),
                'preAlerted'          =>
                    json_encode(
                        MyParcelOrder::getByOrderIds(array((int) $params['id_order'])),
                        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    ),
                'myparcelProcessUrl'  => $this->moduleUrlWithoutToken.'&token='
                    .Tools::getAdminTokenLite('AdminModules').'&ajax=1',
                'myparcel_module_url' => __PS_BASE_URI__."modules/{$this->name}/",
                'jsCountries'         => $countries,
                'invoiceSuggestion'   => MyParcelTools::getInvoiceSuggestion($order),
                'weightSuggestion'    => MyParcelTools::getWeightSuggestion($order),
                'papersize'           => json_decode(Configuration::get(MyParcel::PAPER_SELECTION)),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/orderpage/adminorderdetail.tpl');
    }

    /**
     * Validate order hook
     *
     * @param array $params
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @since 2.0.0
     */
    public function hookActionValidateOrder($params)
    {
        /** @var Order $order */
        $order = $params['order'];

        /** @var Cart $cart */
        $cart = $params['cart'];

        $carrier = new Carrier($order->id_carrier);
        $address = new Address($order->id_address_delivery);
        $country = new Country($address->id_country);
        $customer = new Customer($order->id_customer);

        $address->email = $customer->email;
        $deliveryOption = MyParcelDeliveryOption::getRawByCartId($cart->id);
        $mpcs = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        $mailboxPackage = false;
        if (empty($deliveryOption)) {
            if (!$mailboxPackage = MyParcelDeliveryOption::checkMailboxPackage($cart)) {
                return;
            }
        }

        // Check if the chosen carrier supports the MyParcel pickup or delivery options
        if (!$mpcs
            || !Validate::isLoadedObject($mpcs)
            || !isset($deliveryOption->type)
            || !$mpcs->{$deliveryOption->type}
            || !in_array($deliveryOption->type, array('delivery', 'pickup'))
            || !in_array(Tools::strtoupper($country->iso_code), array('NL', 'BE'))
            ) {
            MyParcelDeliveryOption::removeDeliveryOption($cart->id);

            return;
        }

        $concept = MyParcelDeliveryOption::createConcept($order, $deliveryOption, $address, $mailboxPackage);

        try {
            if ($deliveryOption->type === 'pickup' && Configuration::get(MyParcel::USE_PICKUP_ADDRESS)) {
                $newAddress = MyParcelTools::getCustomerAddress($customer->id, $deliveryOption->data->location_code);
                if (!Validate::isLoadedObject($newAddress)) {
                    $newAddress->id_customer = $customer->id;
                    $newAddress->alias = "myparcel-{$deliveryOption->data->location_code}";
                    $newAddress->company = $deliveryOption->data->location;
                    $newAddress->firstname = $address->firstname;
                    $newAddress->lastname = $address->lastname;
                    $newAddress->postcode = $deliveryOption->data->postal_code;
                    $newAddress->city = $deliveryOption->data->city;
                    $newAddress->id_country = $address->id_country;
                    $newAddress->phone = $deliveryOption->data->phone_number;
                    list (, $housenumberField, $extensionField) = $addressFields =
                        MyParcelTools::getAddressLineFields($newAddress->id_country);
                    $addressLine = "{$deliveryOption->data->street} {$deliveryOption->data->number}";
                    $addressFields = array_filter($addressFields, function ($item) {
                        return (bool) $item;
                    });
                    switch (array_sum($addressFields)) {
                        case 2:
                            if (preg_match(MyParcel::SPLIT_STREET_REGEX, $addressLine, $m)) {
                                $newAddress->address1 = $deliveryOption->data->street;
                                $newAddress->{$housenumberField} = isset($m['street_suffix'])
                                    ? $m['street_suffix']
                                    : '';
                            } else {
                                $newAddress->address1 = $addressLine;
                            }
                            break;
                        case 3:
                            if (preg_match(MyParcel::SPLIT_STREET_REGEX, $addressLine, $m)) {
                                $newAddress->address1 = $deliveryOption->data->street;
                                $newAddress->{$housenumberField} = isset($m['number']) ? $m['number'] : '';
                                $newAddress->{$extensionField} = isset($m['number_suffix']) ? $m['number_suffix'] : '';
                            } else {
                                $newAddress->address1 = $addressLine;
                            }
                            break;
                        default:
                            $newAddress->address1 = $addressLine;
                            break;
                    }

                    $newAddress->save();
                }

                $order->id_address_delivery = $newAddress->id;
                $order->update();
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error while saving pickup address: {$e->getMessage()}");
        }

        if (isset($deliveryOption->data)) {
            $deliveryOption = array(
                'data'         => $deliveryOption->data,
                'type'         => (isset($deliveryOption->type) ? (string) $deliveryOption->type : 'delivery'),
                'extraOptions' => (isset($deliveryOption->extraOptions) ? $deliveryOption->extraOptions : array()),
                'concept'      => $concept,
            );
        } else {
            $deliveryOption = array(
                'concept' => $concept,
            );
        }
        MyParcelDeliveryOption::saveRawDeliveryOption(
            json_encode($deliveryOption, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $cart->id
        );
    }

    /**
     * Edit order grid display
     *
     * @param array $params
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (isset($params['select'])) {
            $params['select'] .= ",\n\t\tmpdo.`myparcel_delivery_option`, IFNULL(mpdo.`date_delivery`, '1970-01-01 00:".
                "00:00') as `myparcel_date_delivery`, mpdo.`pickup`, UPPER(country.`iso_code`) AS `myparcel_country_is".
                "o`, 1 as `myparcel_void_1`, 1 as `myparcel_void_2`";
        }
        if (isset($params['join'])) {
            $params['join'] .= "\n\t\tLEFT JOIN `"._DB_PREFIX_.bqSQL(MyParcelDeliveryOption::$definition['table'])."` ".
                "mpdo ON (mpdo.`id_cart` = a.`id_cart`)";
        }
        if (isset($params['fields'])) {
            $params['fields']['myparcel_date_delivery'] = array(
                'title'           => $this->l('Preferred delivery date'),
                'class'           => 'fixed-width-lg',
                'callback'        => 'printOrderGridPreference',
                'callback_object' => 'MyParcelTools',
                'filter_key'      => 'mpdo!date_delivery',
                'type'            => 'date',
            );
            $params['fields']['myparcel_void_1'] = array(
                'title'           => $this->l('MyParcel'),
                'class'           => 'fixed-width-lg',
                'callback'        => 'printMyParcelTrackTrace',
                'callback_object' => 'MyParcelTools',
                'search'          => false,
                'orderby'         => false,
                'remove_onclick'  => true,
            );
            $params['fields']['myparcel_void_2'] = array(
                'title'           => '',
                'class'           => 'fixed-width-xs',
                'callback'        => 'printMyParcelIcon',
                'callback_object' => 'MyParcelTools',
                'search'          => false,
                'orderby'         => false,
                'remove_onclick'  => true,
            );
        }
    }

    /**
     * Get MyParcel locale
     *
     * @return string
     *
     * @since 2.0.9
     */
    public static function getLocale()
    {
        $language = Context::getContext()->language;

        return (Tools::strlen($language->language_code) >= 5)
            ? Tools::strtolower(Tools::substr($language->language_code, 0, 2)).'-'.Tools::strtoupper(
                Tools::substr($language->language_code, 3, 2)
            )
            : Tools::strtolower(Tools::substr($language->language_code, 0, 2)).'-'.Tools::strtoupper(
                Tools::substr($language->language_code, 0, 2)
            );
    }

    /**
     * Get order shipping costs external
     *
     * @param array $params Hook parameters
     *
     * @return bool|float
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    public function getOrderShippingCostExternal($params)
    {
        return $this->getOrderShippingCost($params, 0);
    }

    /**
     * Get shipping costs for order
     *
     * @param array $params       Hook parameters
     * @param float $shippingCost Shipping costs before calling this method
     *
     * @return bool|float Processed shipping costs
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    public function getOrderShippingCost($params, $shippingCost)
    {
        if (!Module::isEnabled($this->name) || $shippingCost === false) {
            return false;
        }

        if (get_class($params) === __CLASS__) {
            $cart = $this->context->cart;
        } else {
            /** @var Cart $cart */
            $cart = $params;
        }

        // Detect carrier settings
        $carrier = new Carrier((int) $this->id_carrier);
        $deliveryOption = MyParcelDeliveryOption::getRawByCartId($cart->id, false);
        $deliverySetting = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!Validate::isLoadedObject($deliverySetting)) {
            // External module name has been set to `myparcel`, but not a single delivery setting is available
            return false;
        }
        $address = new Address($cart->id_address_delivery);
        $countryIso = (string) Country::getIsoById($address->id_country);
        if (!$countryIso) {
            $countryIso = Context::getContext()->country->iso_code;
        }
        $countryIso = Tools::strtoupper($countryIso);

        if ($deliverySetting->mailbox_package) {
            // Disable if not delivering to the Netherlands
            if ($countryIso !== 'NL') {
                return false;
            }

            $amountOfBoxes = (int) $this->howManyMailboxPackages($cart->getProducts(), true);
            if ($amountOfBoxes < 1) {
                return false;
            }
        }

        $extraCosts = 0;
        // Just a check to see if we actually have a delivery option available
        if (isset($deliveryOption->extraOptions)) {
            $selectedOptions = $deliveryOption->extraOptions;
            if (in_array($countryIso, array('NL', 'BE'))
                && isset($deliveryOption->type)
            ) {
                if ($deliveryOption->type === 'delivery') {
                    if ($selectedOptions->signed
                        && $selectedOptions->recipientOnly
                        && !in_array($deliveryOption->data->time[0]->type, array('1', '3'))) {
                        $extraCosts += (float) $deliverySetting->signed_recipient_only_fee_tax_incl;
                    } elseif (in_array($deliveryOption->data->time[0]->type, array('1', '3'))) {
                        if ($deliveryOption->data->time[0]->type == 1) {
                            $extraCosts += (float) $deliverySetting->morning_fee_tax_incl;
                        } else {
                            $extraCosts += (float) $deliverySetting->evening_fee_tax_incl;
                        }

                        if ($selectedOptions->signed) {
                            $extraCosts += (float) $deliverySetting->signed_fee_tax_incl;
                        }
                    } else {
                        if ($selectedOptions->signed) {
                            $extraCosts += (float) $deliverySetting->signed_fee_tax_incl;
                        }
                        if ($selectedOptions->recipientOnly) {
                            $extraCosts += (float) $deliverySetting->recipient_only_fee_tax_incl;
                        }
                    }
                } elseif ($deliveryOption->type === 'pickup'
                    && isset($deliveryOption->data->price_comment)
                    && $deliveryOption->data->price_comment === 'retailexpress') {
                    $extraCosts = (float) $deliverySetting->morning_pickup_fee_tax_incl;
                }
            }
        }
        // Calculate the conversion to make before displaying prices
        // It is comprised of taxes and currency conversions
        /** @var Currency $defaultCurrency */
        $defaultCurrency = Currency::getCurrencyInstance(Configuration::get(' PS_CURRENCY_DEFAULT'));
        /** @var Currency $currentCurrency */
        $currentCurrency = $this->context->currency;
        $conversion = $defaultCurrency->conversion_rate * $currentCurrency->conversion_rate;
        // Extra costs are entered with 21% VAT
        $taxRate = 1 / 1.21;

        $shippingCost = (float) $this->calcPackageShippingCost(
            $cart,
            $carrier->id,
            false,
            null,
            null,
            null,
            false
        );

        return $extraCosts * $conversion * $taxRate + $shippingCost;
    }

    /**
     * Does it fit in the box?
     *
     * @param array $products Array of products to be added
     * @param bool  $multiple Multiple boxes allowed
     *
     * @return int Amount of boxes, 0 if it doesn't fit
     *
     * @since 2.0.0
     */
    protected function howManyMailboxPackages($products, $multiple = false)
    {
        // Init calculator
        $packer = new \MyParcelModule\BoxPacker\Packer();
        try {
            $unitConfig = Configuration::getMultiple(
                array(
                    'PS_WEIGHT_UNIT',
                    'PS_DIMENSION_UNIT',
                )
            );
        } catch (PrestaShopException $e) {
            return 0;
        }
        if (in_array(Tools::strtolower($unitConfig['PS_WEIGHT_UNIT']), array('kg', 'kilo', 'kilogram'))) {
            $maxWeight = 2;
        } elseif (in_array(Tools::strtolower($unitConfig['PS_WEIGHT_UNIT']), array('g', 'gram', 'gramme'))) {
            $maxWeight = 2000;
        } else {
            return false;
        }
        if (Tools::strtolower($unitConfig['PS_DIMENSION_UNIT']) == 'cm') {
            $maxWidth = 38;
            $maxHeight = 3.2;
            $maxDepth = 26.5;
        } elseif (Tools::strtolower($unitConfig['PS_DIMENSION_UNIT']) == 'mm') {
            $maxWidth = 380;
            $maxHeight = 32;
            $maxDepth = 265;
        } else {
            return false;
        }
        // Add the box
        $packer->addBox(
            new MyParcelMailboxPackage(
                'Brievenbuspakje',
                $maxWidth,
                $maxHeight,
                $maxDepth,
                0,
                $maxWidth,
                $maxHeight,
                $maxDepth,
                ($maxWeight == 0) ? INF : $maxWeight
            )
        );
        $productCount = 0;
        // Add the items
        foreach ($products as $product) {
            /** @var Product $product */
            // If the item has no dimensions we'll be careful: the items do not fit
            if ((float) $product['height'] * (float) $product['width'] * (float) $product['depth'] <= 0) {
                return 1;
            }
            $packer->addItem(
                new MyParcelBrievenbuspakjeItem(
                    '',
                    (float) $product['width'],
                    (float) $product['height'],
                    (float) $product['depth'],
                    (float) $product['weight'],
                    false
                ),
                (int) $product['quantity']
            );
            $productCount += (int) $product['quantity'];
        }
        // How many boxes?
        try {
            $packedBoxes = $packer->pack();
        } catch (\RuntimeException $e) {
            // Too large to fit
            return 0;
        }
        // Does everything fit?
        $itemsThatFit = 0;
        $boxesCount = 0;
        foreach ($packedBoxes as $packedBox) {
            /** @var MyParcelModule\BoxPacker\PackedBox $packedBox */
            $itemsThatFit += count($packedBox->getItems());
            $boxesCount++;
        }
        if ($itemsThatFit != $productCount) {
            return 0;
        }

        return ($boxesCount > 1 && !$multiple) ? 0 : $boxesCount;
    }

    /**
     * Return package shipping cost
     *
     * @param Cart         $cart           Cart object
     * @param int          $idCarrier      Carrier ID (default : current carrier)
     * @param bool         $useTax         Apply taxes
     * @param Country|null $defaultCountry Default country
     * @param array|null   $productList    List of product concerned by the shipping.
     *                                     If null, all the product of the cart are used
     *                                     to calculate the shipping cost
     * @param int|null     $idZone         Zone ID
     * @param bool         $recursion      Enable module recursion?
     *
     * @return float|false Shipping total, false if not possible
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    public function calcPackageShippingCost(
        $cart,
        $idCarrier,
        $useTax = true,
        $defaultCountry = null,
        $productList = null,
        $idZone = null,
        $recursion = true
    ) {
        if ($cart->isVirtualCart()) {
            return 0;
        }

        if (!$defaultCountry) {
            $defaultCountry = Context::getContext()->country;
        }

        if (!is_null($productList)) {
            foreach ($productList as $key => $value) {
                if ($value['is_virtual'] == 1) {
                    unset($productList[$key]);
                }
            }
        }

        if (is_null($productList)) {
            $products = $cart->getProducts();
        } else {
            $products = $productList;
        }

        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
            $addressId = (int) $cart->id_address_invoice;
        } elseif (is_array($productList) && count($productList)) {
            $prod = array_values($productList);
            $prod = $prod[0];
            $addressId = (int) $prod['id_address_delivery'];
        } else {
            $addressId = null;
        }
        if (!Address::addressExists($addressId)) {
            $addressId = null;
        }

        if (is_null($idCarrier) && !empty($cart->id_carrier)) {
            $idCarrier = (int) $cart->id_carrier;
        }

        $cacheId = $this->name.'MyParcelconfPackageShippingCost_'.(int) $cart->id.'_'.(int) $addressId.'_'
            .(int) $idCarrier.'_'.(int) $useTax.'_'.(int) $defaultCountry->id;
        if ($products) {
            foreach ($products as $product) {
                $cacheId .= '_'.(int) $product['id_product'].'_'.(int) $product['id_product_attribute'];
            }
        }

        if (Cache::isStored($cacheId)) {
            return Cache::retrieve($cacheId);
        }

        // Order total in default currency without fees
        $orderTotal = $cart->getOrderTotal(true, Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING, $productList);

        // Start with shipping cost at 0
        $shippingCost = 0;
        // If no product added, return 0
        if (!count($products)) {
            Cache::store($cacheId, $shippingCost);

            return $shippingCost;
        }

        if (!isset($idZone)) {
            // Get id zone
            if (!$cart->isMultiAddressDelivery()
                && isset($cart->id_address_delivery) // Be careful, id_address_delivery is not useful on 1.5
                && $cart->id_address_delivery
                && Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)
            ) {
                $idZone = Address::getZoneById((int) $cart->id_address_delivery);
            } else {
                if (!Validate::isLoadedObject($defaultCountry)) {
                    $defaultCountry = new Country(
                        Configuration::get('PS_COUNTRY_DEFAULT'),
                        Configuration::get('PS_LANG_DEFAULT')
                    );
                }

                $idZone = (int) $defaultCountry->id_zone;
            }
        }

        if ($idCarrier && !$cart->isCarrierInRange((int) $idCarrier, (int) $idZone)) {
            $idCarrier = '';
        }

        if (empty($idCarrier)
            && $cart->isCarrierInRange((int) Configuration::get('PS_CARRIER_DEFAULT'), (int) $idZone)
        ) {
            $idCarrier = (int) Configuration::get('PS_CARRIER_DEFAULT');
        }

        $totalPackageWithoutShippingTaxInc = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $productList);

        if (!isset(static::$cachedCarriers[$idCarrier])) {
            static::$cachedCarriers[$idCarrier] = new Carrier((int) $idCarrier);
        }

        /** @var Carrier $carrier */
        $carrier = static::$cachedCarriers[$idCarrier];

        $shippingMethod = $carrier->getShippingMethod();
        // Get only carriers that are compliant with shipping method
        if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT
                && $carrier->getMaxDeliveryPriceByWeight((int) $idZone) === false)
            || ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE
                && $carrier->getMaxDeliveryPriceByPrice((int) $idZone) === false)
        ) {
            return false;
        }

        // If out-of-range behavior carrier is set on "Deactivate carrier"
        if ($carrier->range_behavior) {
            $checkDeliveryPriceByWeight = Carrier::checkDeliveryPriceByWeight(
                $idCarrier,
                $cart->getTotalWeight(),
                (int) $idZone
            );

            $totalOrder = $totalPackageWithoutShippingTaxInc;
            $checkDeliveryPriceByPrice = Carrier::checkDeliveryPriceByPrice(
                $idCarrier,
                $totalOrder,
                (int) $idZone,
                (int) $cart->id_currency
            );

            // Get only carriers that have a range compatible with cart
            if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT && !$checkDeliveryPriceByWeight)
                || ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE && !$checkDeliveryPriceByPrice)
            ) {
                return false;
            }
        }

        if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) {
            $shipping = $carrier->getDeliveryPriceByWeight($cart->getTotalWeight($productList), (int) $idZone);
        } else {
            $shipping = $carrier->getDeliveryPriceByPrice($orderTotal, (int) $idZone, (int) $cart->id_currency);
        }

        /**
         * @global float $minShippingPrice -- Could be global
         *
         * @codingStandardsIgnoreStart
         */
        if (!isset($minShippingPrice)) {
            $minShippingPrice = $shipping;
        }
        /**
         * @codingStandardsIgnoreEnd
         */

        if ($shipping <= $minShippingPrice) {
            $idCarrier = (int) $idCarrier;
            $minShippingPrice = $shipping;
        }

        if (empty($idCarrier)) {
            $idCarrier = '';
        }

        if (!isset(static::$cachedCarriers[$idCarrier])) {
            static::$cachedCarriers[$idCarrier] = new Carrier(
                (int) $idCarrier,
                Configuration::get('PS_LANG_DEFAULT')
            );
        }

        $carrier = static::$cachedCarriers[$idCarrier];

        // No valid Carrier or $id_carrier <= 0 ?
        if (!Validate::isLoadedObject($carrier)) {
            Cache::store($cacheId, 0);

            return 0;
        }
        $shippingMethod = $carrier->getShippingMethod();

        if (!$carrier->active) {
            Cache::store($cacheId, $shippingCost);

            return $shippingCost;
        }

        // Free fees if free carrier
        if ($carrier->is_free == 1) {
            Cache::store($cacheId, 0);

            return 0;
        }

        // Select carrier tax
        if ($useTax && !Tax::excludeTaxeOption()) {
            try {
                $address = Address::initialize((int) $addressId);
            } catch (PrestaShopException $e) {
                $address = new Address();
            }

            if (Configuration::get('PS_ATCP_SHIPWRAP')) {
                $carrierTax = 0;
            } else {
                $carrierTax = $carrier->getTaxesRate($address);
            }
        }

        try {
            $configuration = Configuration::getMultiple(
                array(
                    'PS_SHIPPING_FREE_PRICE',
                    'PS_SHIPPING_HANDLING',
                    'PS_SHIPPING_METHOD',
                    'PS_SHIPPING_FREE_WEIGHT',
                )
            );
        } catch (PrestaShopException $e) {
            return false;
        }

        // Free fees
        $freeFeesPrice = 0;
        if (isset($configuration['PS_SHIPPING_FREE_PRICE'])) {
            $freeFeesPrice = Tools::convertPrice(
                (float) $configuration['PS_SHIPPING_FREE_PRICE'],
                Currency::getCurrencyInstance((int) $cart->id_currency)
            );
        }
        $orderTotalwithDiscounts = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);
        if ($orderTotalwithDiscounts >= (float) ($freeFeesPrice) && (float) ($freeFeesPrice) > 0) {
            Cache::store($cacheId, $shippingCost);

            return $shippingCost;
        }

        if (isset($configuration['PS_SHIPPING_FREE_WEIGHT'])
            && $cart->getTotalWeight() >= (float) $configuration['PS_SHIPPING_FREE_WEIGHT']
            && (float) $configuration['PS_SHIPPING_FREE_WEIGHT'] > 0
        ) {
            Cache::store($cacheId, $shippingCost);

            return $shippingCost;
        }

        // Get shipping cost using correct method
        if ($carrier->range_behavior) {
            if (!isset($idZone)) {
                // Get id zone
                if (isset($cart->id_address_delivery)
                    && $cart->id_address_delivery
                    && Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)
                ) {
                    $idZone = Address::getZoneById((int) $cart->id_address_delivery);
                } else {
                    $idZone = (int) $defaultCountry->id_zone;
                }
            }

            if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT
                    && !Carrier::checkDeliveryPriceByWeight($carrier->id, $cart->getTotalWeight(), (int) $idZone))
                || ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE
                    && !Carrier::checkDeliveryPriceByPrice(
                        $carrier->id,
                        $totalPackageWithoutShippingTaxInc,
                        $idZone,
                        (int) $cart->id_currency
                    )
                )
            ) {
                $shippingCost += 0;
            } else {
                if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) {
                    $shippingCost += $carrier->getDeliveryPriceByWeight($cart->getTotalWeight($productList), $idZone);
                } else { // by price
                    $shippingCost += $carrier->getDeliveryPriceByPrice($orderTotal, $idZone, (int) $cart->id_currency);
                }
            }
        } else {
            if ($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT) {
                $shippingCost += $carrier->getDeliveryPriceByWeight($cart->getTotalWeight($productList), $idZone);
            } else {
                $shippingCost += $carrier->getDeliveryPriceByPrice($orderTotal, $idZone, (int) $cart->id_currency);
            }
        }
        // Adding handling charges
        if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling) {
            $shippingCost += (float) $configuration['PS_SHIPPING_HANDLING'];
        }

        // Additional Shipping Cost per product
        foreach ($products as $product) {
            if (!$product['is_virtual']) {
                $shippingCost += $product['additional_shipping_cost'] * $product['cart_quantity'];
            }
        }

        $shippingCost = Tools::convertPrice($shippingCost, Currency::getCurrencyInstance((int) $cart->id_currency));

        if ($carrier->shipping_external) {
            $moduleName = $carrier->external_module_name;
            /** @var CarrierModule $module */
            $module = Module::getInstanceByName($moduleName);
            if (Validate::isLoadedObject($module)) {
                if (property_exists($module, 'id_carrier')) {
                    $module->id_carrier = $carrier->id;
                }
                if ($recursion) {
                    if ($carrier->need_range) {
                        if (method_exists($module, 'getPackageShippingCost')) {
                            $shippingCost = $module->getPackageShippingCost($this, $shippingCost, $products);
                        } else {
                            $shippingCost = $module->getOrderShippingCost($this, $shippingCost);
                        }
                    } else {
                        $shippingCost = $module->getOrderShippingCostExternal($this);
                    }
                }
                // Check if carrier is available
                if ($shippingCost === false) {
                    Cache::store($cacheId, false);

                    return false;
                }
            } else {
                Cache::store($cacheId, false);

                return false;
            }
        }

        if (Configuration::get('PS_ATCP_SHIPWRAP')) {
            if ($useTax) {
                // With PS_ATCP_SHIPWRAP, we apply the proportionate tax rate to the shipping
                // costs. This is on purpose and required in many countries in the European Union.
                $shippingCost *= (1 + $cart->getAverageProductsTaxRate());
            }
        } else {
            // Apply tax
            if ($useTax && isset($carrierTax)) {
                $shippingCost *= 1 + ($carrierTax / 100);
            }
        }

        $mdo = MyParcelDeliveryOption::getRawByCartId($cart->id);

        if (isset($mdo->type) && $mdo->type == 'timeframe') {
            if (isset($mdo->data->time->price_comment)) {
                switch ($mdo->data->time->price_comment) {
                    case 'morning':
                        return 4;
                    case 'night':
                        return 5;
                }
            }
        }

        $shippingCost = (float) Tools::ps_round((float) $shippingCost, 2);
        Cache::store($cacheId, $shippingCost);

        return $shippingCost;
    }

    /**
     * Get Carrier IDs by references
     *
     * @param array $references Array with reference IDs
     *
     * @return array|bool Carrier IDs
     *
     * @since 2.0.0
     */
    protected function getCarriersByReferences($references)
    {
        if (empty($references) && !is_array($references)) {
            return false;
        }
        $sql = new DbQuery();
        $sql->select('`id_carrier`');
        $sql->from('carrier');
        $where = '`id_reference` = '.(int) $references[0];
        for ($i = 1; $i < count($references); $i++) {
            $where .= ' OR `id_reference` = '.(int) $references[$i];
        }
        $sql->where($where);
        try {
            $carriersDb = Db::getInstance()->executeS($sql);
        } catch (PrestaShopException $e) {
            $carriersDb = array();
        }

        $carrierIds = array();
        foreach ($carriersDb as $carrier) {
            $carrierIds[] = (int) $carrier['id_carrier'];
        }

        return $carrierIds;
    }

    /**
     * Detect whether the order has a shipping number.
     *
     * @param $order Order The order to check
     *
     * @return bool True if the order has a shipping number
     *
     * @since 2.0.0
     * @throws PrestaShopException
     */
    protected function orderHasShippingNumber($order)
    {
        if (isset($order->shipping_number) && $order->shipping_number) {
            return true;
        }
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        if ($orderCarrier->tracking_number) {
            return true;
        }

        return false;
    }

    /**
     * 2D array sort by key
     *
     * @param $array
     * @param $key
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function aasort(&$array, $key)
    {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }

    /**
     * Get carrier reference by delivery option ID
     *
     * @param int $idMyParcelCarrierDeliverySetting Delivery option ID
     *
     * @return int Carrier reference
     *
     * @since 2.0.0
     */
    protected function getCarrierReferenceByOptionId($idMyParcelCarrierDeliverySetting)
    {
        $sql = new DbQuery();
        $sql->select('`id_reference`');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']));
        $sql->where('`'.bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']).'` = '
            .(int) $idMyParcelCarrierDeliverySetting);

        try {
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        } catch (PrestaShopException $e) {
            return 0;
        }
    }

    /**
     * Add information message
     *
     * @param string $message Message
     * @param bool   $private Only display on module's configuration page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function addInformation($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->informations[] = '<a href="'.$this->baseUrl.'">'
                    .$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->informations[] = $message;
        }
    }

    /**
     * Add warning message
     *
     * @param string $message Message
     * @param bool   $private Only display on module's configuration page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function addWarning($message, $private = true)
    {
        if (!Tools::isSubmit('configure')) {
            if (!$private) {
                $this->context->controller->warnings[] = '<a href="'.$this->baseUrl.'">'
                    .$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * @param Carrier $carrier
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function addGroups($carrier)
    {
        $groupsIds = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groupsIds[] = $group['id_group'];
        }

        $carrier->setGroups($groupsIds);
    }

    /**
     * @param Carrier $carrier
     *
     * @return RangePrice
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function addPriceRange($carrier)
    {
        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';
        $rangePrice->add();

        return $rangePrice;
    }

    /**
     * @param Carrier $carrier
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }

        return $zones;
    }

    /**
     * Performs a basic check and return an array with errors
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.9
     */
    protected function basicCheck()
    {
        $errors = array();
        if (!Country::getByIso('NL') && !Country::getByIso('BE')) {
            $errors[] =
                $this->l('At least one of the following countries should be enabled: the Netherlands or Belgium.');
        }
        if (!Currency::getIdByIsoCode('EUR')) {
            $errors[] = $this->l('At least this currency has to be enabled: EUR');
        }

        return $errors;
    }

    /**
     * Get module version
     *
     * @param string $moduleCode
     *
     * @return string
     * @throws PrestaShopException
     */
    protected function getModuleVersion($moduleCode)
    {
        $sql = new DbQuery();
        $sql->select('`version`');
        $sql->from('module');
        $sql->where('`name` = \''.pSQL($moduleCode).'\'');

        return (string) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}

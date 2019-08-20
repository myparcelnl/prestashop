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

if (!defined('_PS_VERSION_')) {
    return;
}

require_once dirname(__FILE__).'/vendor/autoload.php';

/**
 * Class MyParcel
 *
 * @since 1.0.0
 *
 * Translation block:
 * $this->l('Closed');
 * $this->l('unknown');
 */
class MyParcel extends Module
{
    const MENU_MAIN_SETTINGS = 0;
    const MENU_DEFAULT_SETTINGS = 1;
    const MENU_DEFAULT_DELIVERY_OPTIONS = 2;

    const POSTNL_DEFAULT_CARRIER = 'MYPARCEL_DEFAULT_CARRIER';
    const POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER = 'MYPARCEL_DEFAULT_MAILPACK';
    const POSTNL_DEFAULT_DIGITAL_STAMP_CARRIER = 'MYPARCEL_DEFAULT_DIGSTAMP';
    const SUPPORTED_COUNTRIES_URL = 'https://backoffice.myparcel.nl/api/system_country_codes';

    const SUPPORTED_COUNTRIES = 'MYPARCEL_SUPPORTED_COUNTRIES';

    const API_KEY = 'MYPARCEL_API_KEY';

    const LINK_EMAIL = 'MYPARCEL_LINK_EMAIL';
    const LINK_PHONE = 'MYPARCEL_LINK_PHONE';
    const USE_PICKUP_ADDRESS = 'MYPARCEL_USE_PICKUP_ADDRESS';
    const DIGITAL_STAMP_USE_SHIPPED_STATUS = 'MYPARCEL_DIGSTAMP_SHIPPED';

    const LABEL_DESCRIPTION = 'MYPARCEL_LABEL_DESCRIPTION';
    const PAPER_SELECTION = 'MYPARCEL_PAPER_SELECTION';
    const ASK_PAPER_SELECTION = 'MYPARCEL_ASK_PAPER_SELECT';
    const ASK_RETURN_SELECTION = 'MYPARCEL_ASK_RETURN_SELECT';
    const WEIGHT_UNIT = 'MYPARCEL_WEIGHT_UNIT';
    const DIMENSION_UNIT = 'MYPARCEL_DISTANCE_UNIT';

    const CHECKOUT_LIVE = 'MYPARCEL_LIVE_CHECKOUT';
    const CHECKOUT_FG_COLOR1 = 'MYPARCEL_CHECKOUT_FG_COLOR1';
    const CHECKOUT_FG_COLOR2 = 'MYPARCEL_CHECKOUT_FG_COLOR2';
    const CHECKOUT_FG_COLOR3 = 'MYPARCEL_CHECKOUT_FG_COLOR3';
    const CHECKOUT_BG_COLOR1 = 'MYPARCEL_CHECKOUT_BG_COLOR1';
    const CHECKOUT_BG_COLOR2 = 'MYPARCEL_CHECKOUT_BG_COLOR2';
    const CHECKOUT_BG_COLOR3 = 'MYPARCEL_CHECKOUT_BG_COLOR3';
    const CHECKOUT_HL_COLOR = 'MYPARCEL_CHECKOUT_HL_COLOR';
    const CHECKOUT_INACTIVE_COLOR = 'MYPARCEL_CHECKOUT_IA_COLOR';
    const CHECKOUT_FONT = 'MYPARCEL_CHECKOUT_FONT';
    const CHECKOUT_FONT_SIZE = 'MYPARCEL_CHECKOUT_FSIZE';

    const DEFAULT_CONCEPT_PARCEL_TYPE = 'MYPARCEL_DEFCON_PT';

    const DEFAULT_CONCEPT_LARGE_PACKAGE = 'MYPARCEL_DEFCON_LP';
    const DEFAULT_CONCEPT_HOME_DELIVERY_ONLY = 'MYPARCEL_DEFCON_HDO';
    const DEFAULT_CONCEPT_RETURN = 'MYPARCEL_DEFCON_RETURN';
    const DEFAULT_CONCEPT_SIGNED = 'MYPARCEL_DEFCON_S';
    const DEFAULT_CONCEPT_INSURED = 'MYPARCEL_DEFCON_I';
    const DEFAULT_CONCEPT_INSURED_TYPE = 'MYPARCEL_DEFCON_I_TYPE';
    const DEFAULT_CONCEPT_INSURED_AMOUNT = 'MYPARCEL_DEFCON_I_AMOUNT';

    const DEFAULT_CONCEPT_CUSTOMS_STATUS = 'MYPARCEL_DEFCON_CS';
    const DEFAULT_CONCEPT_CLASSIFICATION = 'MYPARCEL_DEFCON_CLASS';
    const DEFAULT_CONCEPT_COUNTRY_OF_ORIGIN = 'MYPARCEL_DEFCON_COO';
    const DEFAULT_CONCEPT_AGE_CHECK = 'MYPARCEL_DEFCON_AC';
    const DEFAULT_CONCEPT_COOLED_DELIVERY = 'MYPARCEL_DEFCON_CD';

    const DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE = 'MYPARCEL_RETDEFCON_LP';
    const DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY = 'MYPARCEL_RETDEFCON_HDO';
    const DEFAULT_RETURN_CONCEPT_RETURN = 'MYPARCEL_RETDEFCON_RETURN';
    const DEFAULT_RETURN_CONCEPT_SIGNED = 'MYPARCEL_RETDEFCON_S';
    const DEFAULT_RETURN_CONCEPT_INSURED = 'MYPARCEL_RETDEFCON_I';
    const DEFAULT_RETURN_CONCEPT_INSURED_TYPE = 'MYPARCEL_RETDEFCON_I_TYPE';
    const DEFAULT_RETURN_CONCEPT_INSURED_AMOUNT = 'MYPARCEL_RETDEFCON_I_AMOUNT';
    const MONDAY_DELIVERY_SUPPORTED = 'MYPARCEL_MON_DEL';

    const INSURED_TYPE_100 = 1;
    const INSURED_TYPE_250 = 2;
    const INSURED_TYPE_500 = 3;
    const INSURED_TYPE_500_PLUS = 4;

    const WEBHOOK_CHECK_INTERVAL = 86400; //daily check
    const WEBHOOK_LAST_CHECK = 'MYPARCEL_WEBHOOK_UPD';
    const WEBHOOK_ID = 'MYPARCEL_WEBHOOK_ID';
    const NEW_VERSION_AVAILABLE = 'MYPARCEL_NEW_VERSION';

    const TOUR_CURRENT_STEP = 'MYPARCEL_TOUR_STEP';
    const TOUR_STEP_MAIN = 1;
    const TOUR_STEP_DELIVERY_OPTIONS = 2;
    const TOUR_STEP_DESIGN = 3;
    const TOUR_STEP_LABELS_NOTIFICATIONS = 4;
    const TOUR_STEP_CARRIER_CONFIG = 5;
    const TOUR_STEP_START_SHIPPING = 6;

    const DEV_MODE_SET_VERSION = 'MYPARCEL_SET_VERSION';
    const DEV_MODE_RESET_TOUR = 'MYPARCEL_RESET_TOUR';
    const DEV_MODE_CHECK_WEBHOOKS = 'MYPARCEL_CHECK_WEBHOOKS';
    const DEV_MODE_REMOVE_WEBHOOK = 'MYPARCEL_REMOVE_WEBHOOK';
    const DEV_MODE_HIDE_PREFERRED = 'MYPARCEL_HIDE_PREFERRED';
    const DEV_MODE_SEARCH_TRACKTRACE = 'MYPARCEL_SEARCH_TRACKTRACE';

    const API_TIMEOUT = 20;
    const CONNECTION_ATTEMPTS = 5;
    const LOG_API = 'MYPARCEL_LOG_API';
    const ADDRESS_FIELD_OVERRIDE = 'MYPARCEL_ADDRFIELDOVERR_';

    const PRINTED_STATUS = 'MYPARCEL_PRINTED_STATUS';
    const SHIPPED_STATUS = 'MYPARCEL_SHIPPED_STATUS';
    const RECEIVED_STATUS = 'MYPARCEL_RECEIVED_STATUS';
    const IGNORE_ORDER_STATUSES = 'MYPARCEL_IGNORE_STATUSES';
    const NOTIFICATIONS = 'MYPARCEL_NOTIFS';
    const NOTIFICATION_MOMENT = 'MYPARCEL_NOTIF_MOMENT';
    const MOMENT_SCANNED = 0;
    const MOMENT_PRINTED = 1;

    const FONT_SMALL = 1;
    const FONT_MEDIUM = 2;
    const FONT_LARGE = 3;

    /**
     * Address format regex
     *
     * This is a RegEx that can be used to grab the address fields from the AddressFormat object
     */
    const ADDRESS_FORMAT_REGEX = '~^(address1)(?: +([a-zA-Z0-9_]+))?(?: +([a-zA-Z0-9_]+))?~m';

    // @codingStandardsIgnoreStart
    /** @var array $cachedCarriers */
    protected static $cachedCarriers = array();
    /** @var int $id_carrier */
    public $id_carrier;
    /** @var Carrier $carrier */
    public $carrier;
    /** @var array $hooks */
    public $hooks = array(
        'displayCarrierList',
        'displayAdminProductsExtra',
        'displayHeader',
        'displayBackOfficeHeader',
        'adminOrder',
        'orderDetail',
        'actionValidateOrder',
        'actionLogsGridDefinitionModifier',
        'actionlogsGridPresenterModifier',
        'actionAdminOrdersListingFieldsModifier',
        'actionAdminLogsListingFieldsModifier',
        'registerGDPRConsent',
        'actionDeleteGDPRCustomer',
        'actionExportGDPRData',
        'actionProductSave',
    );
    /** @var array $statuses */
    protected $statuses = array();
    /** @var int $menu */
    protected $menu = self::MENU_MAIN_SETTINGS;
    /** @var string $baseUrl */
    protected $baseUrl;
    /** @var string $baseUrlWithoutToken */
    protected $baseUrlWithoutToken;
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
        $this->version = '2.3.4';
        $this->author = 'MyParcel';
        $this->module_key = 'c9bb3b85a9726a7eda0de2b54b34918d';
        $this->bootstrap = true;
        $this->controllers = array('myparcelcheckout', 'myparcelcheckoutdemo', 'deliveryoptions', 'hook');

        parent::__construct();

        if (!empty(Context::getContext()->employee->id)) {
            $this->baseUrlWithoutToken = $this->getAdminLink(
                'AdminModules',
                false,
                array(
                    'configure'   => $this->name,
                    'tab_module'  => $this->tab,
                    'module_name' => $this->name,
                )
            );
            $this->baseUrl = $this->getAdminLink(
                'AdminModules',
                true,
                array(
                    'configure'   => $this->name,
                    'tab_module'  => $this->tab,
                    'module_name' => $this->name,
                )
            );
            if (version_compare($this->version, Configuration::get(static::NEW_VERSION_AVAILABLE), '<')) {
                $this->warning = sprintf($this->l('This module can be updated to version %s'), Configuration::get(static::NEW_VERSION_AVAILABLE));
            }
        }

        $this->displayName = $this->l('MyParcel');
        $this->description = $this->l('Shipping made easy with MyParcel.nl');
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     * @throws ErrorException
     */
    public function ajaxProcessCheckWebhooks()
    {
        $this->checkWebhooks();
    }

    /**
     * Check webhooks + update info
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws ErrorException
     *
     * @since 2.0.0
     */
    protected function checkWebhooks()
    {
        $lastCheck = (int) Configuration::get(static::WEBHOOK_LAST_CHECK);
        $webHookId = trim(Configuration::get(static::WEBHOOK_ID));

        if ((time() > ($lastCheck + static::WEBHOOK_CHECK_INTERVAL)) || empty($webHookId)) {
            // Time to update webhooks
            $curl = new \MyParcelModule\MyParcelHttpClient();
            $curl->setHeader('Accept', 'application/json');
            $response = $curl->get("https://api.myparcel.nl/webhook_subscriptions/$webHookId");

            $webhookUrl = rtrim((Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').Tools::getShopDomainSsl().__PS_BASE_URI__, '/')."/index.php?fc=module&module={$this->name}&controller=hook";
            $data = mypa_dot($response);
            $found = null;

            if (is_array($data->get('data.webhook_subscriptions'))) {
                foreach ($data->get('data.webhook_subscriptions') as $subscription) {
                    if (isset($subscription['id']) && (string) $subscription['id'] === (string) $webHookId) {
                        $found = $subscription;
                        break;
                    }
                }
            }

            if (isset($found['id']) && isset($found['url'])) {
                $localUrlParts = parse_url($webhookUrl);
                $remoteUrlParts = parse_url($found['url']);

                if (!isset($remoteUrlParts['host'])
                    || !isset($remoteUrlParts['path'])
                    || !isset($remoteUrlParts['query'])
                    || $localUrlParts['host'] !== $remoteUrlParts['host']
                    || $localUrlParts['path'] !== $remoteUrlParts['path']
                    || $localUrlParts['query'] !== $remoteUrlParts['query']
                ) {
                    $curl = new \MyParcelModule\MyParcelHttpClient();
                    $curl->delete("https://api.myparcel.nl/webhook_subscriptions/{$found['id']}");
                    $found = null;
                }
            }

            if (!$found) {
                $curl = new \MyParcelModule\MyParcelHttpClient();
                $curl->setHeader('Content-Type', 'application/json;charset=utf-8');
                $response = $curl->post('https://api.myparcel.nl/webhook_subscriptions', mypa_json_encode(array(
                    'data' => array(
                        'webhook_subscriptions' => array(
                            array(
                                'hook' => 'shipment_status_change',
                                'url'  => $webhookUrl,
                            ),
                        ),
                    ),
                )));

                if ($response) {
                    $data = mypa_dot($response);
                    if ($data->get('data.ids.0.id')) {
                        Configuration::updateValue(static::WEBHOOK_ID, (int) $data->get('data.ids.0.id'));
                    }
                }
            }

            Configuration::updateValue(static::WEBHOOK_LAST_CHECK, time());
            MyParcelTools::retrieveSupportedCountries();
            static::checkForUpdates();
        }
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
                    if (filetype($dir.'/'.$object) === 'dir') {
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
     * @throws Adapter_Exception
     * @throws PrestaShopException
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
     * @throws Adapter_Exception
     * @throws ReflectionException
     *
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

        if (!extension_loaded('curl')) {
            $this->addError(
                $this->l('Unable to install the MyParcel module. Please enable the PHP cURL extension.'),
                false
            );

            return false;
        }

        if (!extension_loaded('pcre')) {
            $this->addError(
                $this->l('Unable to install the MyParcel module. Please enable the PHP PCRE (RegEx) extension.'),
                false
            );

            return false;
        }

        if (!extension_loaded('date')) {
            $this->addError(
                $this->l('Unable to install the MyParcel module. Please enable the date extension.'),
                false
            );

            return false;
        }

        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $this->addError(
                $this->l('This module cannot be installed with developer mode turned on.'),
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
        $this->addCarrier('PostNL Briefpost', static::POSTNL_DEFAULT_DIGITAL_STAMP_CARRIER);

        $sql = new DbQuery();
        $sql->select('`id_reference`');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']));
        $sql->where('`delivery` = \'1\' OR `pickup` = \'1\' OR `mailbox_package` = \'1\' OR `digital_stamp` = \'1\'');
        $carrierReferences = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (is_array($carrierReferences) && count($carrierReferences) >= 1) {
            Db::getInstance()->update(
                bqSQL(Carrier::$definition['table']),
                array(
                    'is_module'            => true,
                    'shipping_external'    => true,
                    'external_module_name' => $this->name,
                ),
                '`id_reference` IN ('.implode(',', array_map('intval', array_column($carrierReferences, 'id_reference'))).')'
            );
        }

        // On 1.7 only the hook `displayBeforeCarrier` works properly
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $index = array_search('displayCarrierList', $this->hooks);
            unset($this->hooks[$index]);
            $this->hooks[] = 'displayBeforeCarrier';
        }
        foreach ($this->hooks as $hook) {
            $this->registerHook($hook);
        }

        Configuration::updateValue(static::CHECKOUT_FG_COLOR1, '#FFFFFF');
        Configuration::updateValue(static::CHECKOUT_FG_COLOR2, '#000000');
        Configuration::updateValue(static::CHECKOUT_FG_COLOR3, '#000000');
        Configuration::updateValue(
            static::CHECKOUT_BG_COLOR1,
            version_compare(_PS_VERSION_, '1.7.0.0', '>=') ? 'transparent' : '#FBFBFB'
        );
        Configuration::updateValue(static::CHECKOUT_BG_COLOR2, '#01BBC5');
        Configuration::updateValue(static::CHECKOUT_BG_COLOR3, '#75D3D8');
        Configuration::updateValue(static::CHECKOUT_HL_COLOR, '#FF8C00');
        Configuration::updateValue(static::CHECKOUT_INACTIVE_COLOR, '#848484');
        Configuration::updateValue(static::CHECKOUT_FONT, 'Exo');
        Configuration::updateValue(static::CHECKOUT_FONT_SIZE, 2);
        Configuration::updateValue(static::LABEL_DESCRIPTION, '{order.reference}');
        Configuration::updateValue(static::PRINTED_STATUS, 0);
        Configuration::updateValue(static::SHIPPED_STATUS, (int) Configuration::get('PS_OS_SHIPPING'));
        Configuration::updateValue(static::RECEIVED_STATUS, (int) Configuration::get('PS_OS_DELIVERED'));
        Configuration::updateValue(static::IGNORE_ORDER_STATUSES, '[]');
        Configuration::updateValue(static::LINK_EMAIL, true);
        Configuration::updateValue(static::LINK_PHONE, true);
        Configuration::updateValue(static::USE_PICKUP_ADDRESS, true);
        Configuration::updateValue(static::NOTIFICATIONS, true);
        Configuration::updateValue(static::NOTIFICATION_MOMENT, static::MOMENT_SCANNED);
        Configuration::updateValue(static::PAPER_SELECTION, mypa_json_encode(array(
            'size' => 'standard',
            'labels' => array(
                1 => true,
                2 => true,
                3 => true,
                4 => true,
            ),
        )));
        Configuration::updateValue(static::ASK_PAPER_SELECTION, true);
        Configuration::updateValue(static::ASK_RETURN_SELECTION, true);
        Configuration::updateValue(static::DIGITAL_STAMP_USE_SHIPPED_STATUS, true);
        Configuration::updateValue(static::DEFAULT_CONCEPT_CUSTOMS_STATUS, MyParcelProductSetting::CUSTOMS_ENABLE_STRING);

        if (method_exists('Tools', 'clearCache')) {
            Tools::clearCache(Context::getContext()->smarty);
        }

        return true;
    }

    /**
     * Install DB tables
     *
     * @return bool Indicates whether the DB tables have been successfully installed
     *
     * @since 1.0.0
     *
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    protected function installSql()
    {
        if (!(MyParcelCarrierDeliverySetting::createDatabase()
            && MyParcelDeliveryOption::createDatabase()
            && MyParcelOrder::createDatabase()
            && MyParcelOrderHistory::createDatabase()
            && MyParcelProductSetting::createDatabase()
            && MyParcelGoodsNomenclature::createDatabase()
        )) {
            $this->addError(Db::getInstance()->getMsgError(), false);
            $this->uninstallSql();

            return false;
        }
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue("
        SELECT * 
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
        WHERE `CONSTRAINT_TYPE` = 'UNIQUE'
            AND TABLE_NAME = '"._DB_PREFIX_.pSQL(bqSQL(MyParcelDeliveryOption::$definition['table']))."'")
        ) {
            Db::getInstance()->execute(
                'ALTER TABLE `'._DB_PREFIX_.bqSQL(MyParcelDeliveryOption::$definition['table'])
                .'` ADD CONSTRAINT `id_cart` UNIQUE (`id_cart`)'
            );
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
     * @throws Adapter_Exception
     *
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
            $deliverySetting->monday_enabled = false;
            $deliverySetting->tuesday_enabled = false;
            $deliverySetting->wednesday_enabled = false;
            $deliverySetting->thursday_enabled = false;
            $deliverySetting->friday_enabled = false;
            $deliverySetting->saturday_enabled = false;
            $deliverySetting->sunday_enabled = false;
            $deliverySetting->pickup = false;
            $deliverySetting->delivery = false;
            $deliverySetting->mailbox_package = false;
            $deliverySetting->digital_stamp = false;
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
            } elseif ($key === static::POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER) {
                $deliverySetting->mailbox_package = true;
            } else {
                $deliverySetting->digital_stamp = true;
            }
            try {
                $deliverySetting->add();
            } catch (PrestaShopException $e) {
                Logger::addLog(
                    sprintf(
                        "{$this->l('MyParcel: unable to save carrier settings for carrier with reference %d')}: {$e->getMessage()}",
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
     *
     * @since 1.0.0
     */
    public function uninstall()
    {
        Configuration::deleteByName(static::API_KEY);
        Configuration::deleteByName(static::NEW_VERSION_AVAILABLE);
        Configuration::deleteByName(static::WEBHOOK_ID);
        Configuration::deleteByName(static::WEBHOOK_LAST_CHECK);

        foreach ($this->hooks as $hook) {
            $this->unregisterHook($hook);
        }

        Db::getInstance()->update(
            bqSQL(Carrier::$definition['table']),
            array(
                'is_module'            => false,
                'shipping_external'    => false,
                'external_module_name' => null,
            ),
            '`external_module_name` = \'myparcel\'',
            0,
            true
        );

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
     *
     * @since 2.0.0
     */
    public function hookDisplayOrderDetail($params)
    {
        $this->context->smarty->assign(array(
            'shipments'   => MyParcelOrderHistory::getShipmentHistoryByOrderId($params['order']->id),
            'languageIso' => Tools::strtoupper($this->context->language->iso_code),
        ));

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
     *
     * @since 1.0.0
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (!Module::isEnabled($this->name)) {
            return '';
        }

        if ((isset($this->context->controller->controller_name) && $this->context->controller->controller_name === 'AdminProducts'
            || $this->context->controller instanceof AdminProductsController)
            && !Tools::isSubmit('addproduct')
            && !Tools::isSubmit('viewproduct')
            && !Tools::isSubmit('updateproduct')
            && !Tools::isSubmit('deleteproduct')
        ) {
            $countries = array(
                    'SKIP' => array(
                        'iso_code' => 'SKIP',
                        'name'     => $this->l('Do not change'),
                        'region'   => 'CD',
                    ),
                ) + static::getCountries();
            $this->context->smarty->assign(
                array(
                    'mpJsCountries'                  => $countries,
                    'mpAssetsUrl'                    => static::getMediaPath($this->_path),
                    'mpGoodsNomenclatureInstallUrl'  => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'InstallGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureSearchUrl'   => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'SearchGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureBrowseUrl'   => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'BrowseGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureNavigateUrl' => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'NavigateGoodsNomenclature',
                        )
                    ),
                    'mpProductSettingsBulkUrl'           => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'ProductSettings',
                        )
                    ),
                )
            );

            return $this->display(__FILE__, 'views/templates/admin/product/bulk.tpl');
        }

        if ($this->shouldShowTour()) {
            $this->context->smarty->assign(array(
                'current_step' => Configuration::get(static::TOUR_CURRENT_STEP),
                'employee'     => $this->context->employee,
                'module_dir'   => __PS_BASE_URI__."modules/{$this->name}/",
            ));
            $this->context->controller->addCSS($this->_path.'views/css/tour.css');
        }
        $this->context->controller->addCSS($this->_path.'views/css/back.css', 'screen');

        if (!Configuration::get(static::API_KEY)) {
            return '';
        }
        $html = '';
        if (Tools::getValue('controller') === 'AdminOrders'
            && !Tools::isSubmit('addorder')
            && !Tools::isSubmit('updateorder')
            && !Tools::isSubmit('vieworder')
        ) {
            $lastWebhookCheck = (int) Configuration::get(static::WEBHOOK_LAST_CHECK);
            $webHookId = trim(Configuration::get(static::WEBHOOK_ID));
            $this->context->smarty->assign(
                array(
                    'mpProcessUrl'                   => static::appendQueryToUrl($this->baseUrl, array('ajax' => '1')),
                    'mpModuleDir'                    => __PS_BASE_URI__."modules/{$this->name}/",
                    'mpJsCountries'                  => static::getCountries(),
                    'mpPaperSize'                    => @json_decode(Configuration::get(static::PAPER_SELECTION)),
                    'mpAskPaperSize'                 => (bool) Configuration::get(static::ASK_PAPER_SELECTION),
                    'mpAskReturnConfig'              => (bool) Configuration::get(static::ASK_RETURN_SELECTION),
                    'mpCheckWebhooks'                => (time() > ($lastWebhookCheck + static::WEBHOOK_CHECK_INTERVAL)) || empty($webHookId),
                    'mpReturnInsuranceAmount'        => MyParcelTools::getInsuranceAmount(true),
                    'mpLogApi'                       => (bool) Configuration::get(static::LOG_API),
                    'mpOnlyRecipient'                => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY),
                    'mpSignature'                    => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_SIGNED),
                    'mpExtraLarge'                   => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE),
                    'mpReturnUndeliverable'          => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_RETURN),
                    'mpCurrency'                     => Context::getContext()->currency,
                    'mpGoodsNomenclatureInstallUrl'  => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'InstallGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureSearchUrl'   => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'SearchGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureBrowseUrl'   => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'BrowseGoodsNomenclature',
                        )
                    ),
                    'mpGoodsNomenclatureNavigateUrl' => $this->getAdminLink(
                        'AdminModules',
                        true,
                        array(
                            'configure'   => $this->name,
                            'module_name' => $this->name,
                            'ajax'        => '1',
                            'action'      => 'NavigateGoodsNomenclature',
                        )
                    ),
                    'mpMultistore'                   => Shop::isFeatureActive() && in_array(Shop::getContext(), array(Shop::CONTEXT_ALL, Shop::CONTEXT_GROUP)),
                )
            );
            $html .= $this->display(__FILE__, 'views/templates/admin/ordergrid/adminvars.tpl');
            $html .= $this->display(__FILE__, 'load_webpack_chunks.tpl');
            $this->context->controller->addCSS($this->_path.'views/css/forms.css');
        } elseif (Tools::getValue('controller') === 'AdminModules'
            && Tools::getValue('configure') === $this->name
        ) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('datepicker-nl');

            $this->context->smarty->assign(
                array('current_lang_iso' => Tools::strtolower(Language::getIsoById($this->context->language->id)))
            );

            $html .= $this->display(__FILE__, 'views/templates/hook/initdeliverysettings.tpl');
        }

        if ($this->shouldShowTour()) {
            $tourElement = false;
            $submitForm = false;
            $extraSubmits = array();
            switch ((int) Configuration::get(static::TOUR_CURRENT_STEP, null, 0, 0)) {
                case static::TOUR_STEP_MAIN:
                    if ((int) Tools::getValue('menu') === static::MENU_MAIN_SETTINGS) {
                        $tourElement = '#fieldset_0';
                        $submitForm = '#configuration_form';
                        $extraSubmits = array(
                            'configuration_form_submit_btn' => 1,
                        );
                    }
                    break;
                case static::TOUR_STEP_DELIVERY_OPTIONS:
                    $tourElement = '#module_form .panel';
                    $submitForm = '#module_form';
                    $extraSubmits = array(
                        'module_form_submit_btn' => 1,
                    );
                    break;
                case static::TOUR_STEP_DESIGN:
                    $tourElement = '#fieldset_1_1';
                    $submitForm = '#configuration_form';
                    $extraSubmits = array(
                        'configuration_form_submit_btn' => 1,
                    );
                    break;
                case static::TOUR_STEP_LABELS_NOTIFICATIONS:
                    if ((int) Tools::getValue('menu') === static::MENU_MAIN_SETTINGS) {
                        $tourElement = '#fieldset_2_2, #fieldset_3_3';
                        $submitForm = '#configuration_form';
                        $extraSubmits = array(
                            'configuration_form_submit_btn' => 1,
                        );
                    }
                    break;
                case static::TOUR_STEP_CARRIER_CONFIG:
                    break;
                case static::TOUR_STEP_START_SHIPPING:
                    break;
            }

            $this->context->smarty->assign(array(
                'link'         => $this->context->link,
                'moduleCode'   => $this->name,
                'moduleTab'    => $this->tab,
                'moduleUrl'    => $this->baseUrl,
                'shouldResume' => $this->shouldResumeTour(),
                'tourElement'  => $tourElement,
                'submitForm'   => $submitForm,
                'extraSubmits' => $extraSubmits,
            ));

            $html .= $this->display(__FILE__, 'views/templates/admin/tour/backoffice_top.tpl');
        }

        return $html;
    }

    /**
     * Display tab on admin product page
     *
     * @param array $params
     *
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.3.0
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct) {
            if (!isset($params['id_product'])) {
                return '';
            } else {
                $idProduct = (int) $params['id_product'];
            }
        }
        if (!$idProduct) {
            return '';
        }
        $productSettings = MyParcelProductSetting::getByProductId($idProduct);
        $this->context->smarty->assign(
            array(
                'mpIdProduct'                    => (int) $idProduct,
                'mpJsCountries'                  => static::getCountries(),
                'mpAssetsUrl'                    => static::getMediaPath($this->_path),
                'mpProductSettings'              => $productSettings,
                'mpGoodsNomenclatureInstallUrl'  => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'InstallGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureSearchUrl'   => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'SearchGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureBrowseUrl'   => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'BrowseGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureNavigateUrl' => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'NavigateGoodsNomenclature',
                    )
                ),
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/product/tab.tpl');
    }

    /**
     * Process tour step ajax call
     *
     * @throws PrestaShopException
     */
    public function ajaxProcessSetTourStep()
    {
        $tourStep = (int) Tools::getValue('tour_step');
        $success = true;
        if ($tourStep) {
            $success &= Configuration::updateValue(static::TOUR_CURRENT_STEP, $tourStep, false, 0, 0);
        }

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success' => $success,
        )));
    }

    public function ajaxProcessInstallGoodsNomenclature()
    {
        header('Content-Type: text/plain;charset=utf-8');
        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        if (!$request || !isset($request['step'])) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $success = MyParcelGoodsNomenclature::install($request['step']);

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success' => $success,
        )));
    }

    /**
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcessSearchGoodsNomenclature()
    {
        header('Content-Type: text/plain;charset=utf-8');
        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        if (!$request) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $results = MyParcelGoodsNomenclature::search($request['query']);

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success' => is_array($results),
            'results' => $results,
        )));
    }

    /**
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcessBrowseGoodsNomenclature()
    {
        header('Content-Type: text/plain;charset=utf-8');
        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        $results = MyParcelGoodsNomenclature::browse(
            isset($request['parentCode']) ? $request['parentCode'] : null,
            isset($request['path']) ? $request['path'] : null
        );

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success' => is_array($results),
            'results' => $results,
        )));
    }

    /**
     * @since 2.3.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcessNavigateGoodsNomenclature()
    {
        header('Content-Type: text/plain;charset=utf-8');
        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        if (!$request) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $results = MyParcelGoodsNomenclature::navigate($request['code']);

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success' => is_array($results),
            'results' => $results,
        )));
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
     *
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::getValue('ajax') && Tools::getValue('action')) {
            $method = Tools::ucfirst(Tools::getValue('action'));
            $this->{"ajaxProcess{$method}"}();
            exit;
        }

        if (Tools::getValue('demo')) {
            MyParcelDemo::renderDemo();
        }

        $output = '';
        if ($this->checkTemplateCompilation()) {
            $this->context->smarty->assign(array(
                'settingKey'   => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Template compilation', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Template compilation', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never recompile template files', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never recompile template files', 'AdminPerformance'),
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
            ));
            $this->context->controller->warnings[] = $this->display(__FILE__, 'smarty_warning.tpl');
        }
        if ($this->checkStaleSmartyCache()) {
            $this->context->smarty->assign(array(
                'settingKey'   => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Clear cache', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Clear cache', 'AdminPerformance'),
                'settingValue' => version_compare(_PS_VERSION_, '1.7.3.0', '>=')
                    ? $this->trans('Never clear cache files', array(), 'Admin.Advparameters.Feature')
                    : Translate::getAdminTranslation('Never clear cache files', 'AdminPerformance'),
                'settingsPage' => static::getMenuLocation('AdminPerformance'),
            ));
            $this->context->controller->errors[] = $this->display(__FILE__, 'smarty_error.tpl');
        }
        $this->context->smarty->assign(array(
            'menutabs' => $this->initNavigation(),
            'ajaxUrl'  => $this->baseUrlWithoutToken,
        ));
        if (version_compare($this->version, Configuration::get(static::NEW_VERSION_AVAILABLE), '<')) {
            $this->context->smarty->assign(array(
                'this_version'    => $this->version,
                'release_version' => Configuration::get(static::NEW_VERSION_AVAILABLE),
                'publicPath'      => static::getWebpackPublicPath(),
                'updateEndpoint'  => static::appendQueryToUrl($this->baseUrl, array('ajax' => '1')),
            ));
            $output .= $this->display(__FILE__, 'views/templates/admin/new_release.tpl');
        }

        foreach ($this->basicCheck() as $error) {
            $this->context->controller->errors[] = $error;
        }

        $this->postProcess();
        if ($this->shouldShowTour()) {
            // Redundant but necessary on the module config page
            $this->context->smarty->assign(array(
                'current_step' => Configuration::get(static::TOUR_CURRENT_STEP),
                'employee'     => $this->context->employee,
                'module_dir'   => __PS_BASE_URI__."modules/{$this->name}/",
            ));
            $this->context->controller->addCSS($this->_path.'views/css/tour.css');
        }
        $output .= $this->display(__FILE__, 'views/templates/admin/navbar.tpl');

        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/config.js');
        $this->context->controller->addCSS($this->_path.'views/css/forms.css');

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
        return 'PrestaShop/'._PS_VERSION_;
    }

    /**
     * Process newly created labels
     *
     * @param MyParcelModule\MyParcelNL\Sdk\src\Helper\MyParcelCollection $collection
     *
     * @return array
     *
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws \MyParcelModule\MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelModule\MyParcelNL\Sdk\src\Exception\ConnectionException
     *
     * @since 2.1.0
     * @since 2.3.0 Remove idOrders and concepts array
     */
    protected function processNewLabels(MyParcelModule\MyParcelNL\Sdk\src\Helper\MyParcelCollection $collection)
    {
        $processedLabels = array();
        foreach ($collection->getConsignments() as $consignment) {
            $idShipment = $consignment->getMyParcelConsignmentId();
            $consignmentEncoded = $consignment->apiEncode();
            @list(,,$idOrder) = explode('_', $consignment->getReferenceId());
            if (!$idOrder) {
                continue;
            }

            $myparcelOrder = new MyParcelOrder();
            $myparcelOrder->id_order = $idOrder;
            $myparcelOrder->id_shipment = $idShipment;
            $myparcelOrder->postnl_status = 1;
            $myparcelOrder->postcode = $consignment->getPostalCode();
            $myparcelOrder->retour = false;
            $myparcelOrder->postnl_final = false;
            $myparcelOrder->shipment = mypa_json_encode($consignmentEncoded);
            if (isset($consignmentEncoded['options']['delivery_type'])) {
                $myparcelOrder->type = (int) $consignmentEncoded['options']['delivery_type'];
            } else {
                $myparcelOrder->type = MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE;
            }

            $myparcelOrder->add();
            $processedLabel = $myparcelOrder->getFields();
            $processedLabel['shipment'] = $consignmentEncoded;
            $processedLabel[MyParcelOrder::$definition['primary']] = (int) $myparcelOrder->id;
            $processedLabel['id_shipment'] = (int) $processedLabel['id_shipment'];

            // PrestaShop bug
            $processedLabel['postnl_final'] = (bool) $processedLabel['postnl_final'];
            $processedLabel['retour'] = (bool) $processedLabel['retour'];

            $processedLabels[] = $processedLabel;
        }

        return $processedLabels;
    }

    /**
     * Retrieve order info
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     *
     * @since 2.0.0
     */
    public function ajaxProcessOrderInfo()
    {
        header('Content-Type: text/plain;charset=utf-8');
        if (!$this->active) {
            if (function_exists('http_response_code')) {
                http_response_code(404);
            } else {
                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
                header("$protocol 404 Not Found");
            }
            die('MyParcel module has been disabled');
        }

        // @codingStandardsIgnoreStart
        $payload = @json_decode(file_get_contents('php://input'), true);
        // @codingStandardsIgnoreEnd
        $orderIds = $payload['ids'];

        // Retrieve customer preferences
        die(
            mypa_json_encode(
                array(
                    'preAlerted' => MyParcelOrder::getByOrderIds($orderIds),
                    'concepts'   => MyParcelDeliveryOption::getByOrderIds($orderIds),
                )
            )
        );
    }

    /**
     * Ajax process product details
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public function ajaxProcessProductSettings()
    {
        header('Content-Type: application/json;charset=utf-8');
        $success = MyParcelProductSetting::saveMulti(@json_decode(file_get_contents('php://input'), true));
        die(mypa_json_encode(array(
            'success' => $success,
        )));
    }

    /**
     * Get delivery options (BO)
     *
     * @throws ErrorException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public function ajaxProcessDeliveryOptions()
    {
        header('Content-Type: text/plain;charset=utf-8');
        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        if (!$request) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $response = static::getDeliveryOptions($request['cc'], $request['postal_code'], $request['number']);
        if (!$response) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        die(mypa_json_encode($response));
    }

    /**
     * @throws PrestaShopException
     *
     * @since 2.0.0
     * @throws Adapter_Exception
     * @throws ErrorException
     */
    public function ajaxProcessGetShipment()
    {
        header('Content-Type: application/json; charset=utf-8');
        $requestBody = @json_decode(file_get_contents('php://input'), true);
        $curl = new \MyParcelModule\MyParcelHttpClient();
        $shipments = implode(';', $requestBody['moduleData']['shipments']);
        $order = MyParcelOrder::getOrderByShipmentId($requestBody['moduleData']['shipments'][0]);
        if (!Validate::isLoadedObject($order)) {
            die(mypa_json_encode(array('success' => false)));
        }
        $curl->setHeader('Authorization', 'basic '.base64_encode(Configuration::get(MyParcel::API_KEY, null, $order->id_shop_group, $order->id_shop)));
        $responseContent = $curl->get("https://api.myparcel.nl/shipments/{$shipments}");

        $this->getShipmentApiInterceptor($responseContent);

        $response = mypa_dot($responseContent);
        foreach ($response->get('data.shipments') as $index => $shipment) {
            $newShipment = array();
            $newShipment['id_shipment'] = (int) $shipment['id'];
            $mypaOrder = MyParcelOrder::getByShipmentId($shipment['id']);
            if (Validate::isLoadedObject($mypaOrder)) {
                $order = new Order($mypaOrder->id_order);
                $state = new OrderState($order->getCurrentState(), $this->context->language->id);

                $newShipment['id_order'] = (int) $mypaOrder->id_order;
                $newShipment['id_myparcel_order'] = (int) $mypaOrder->id;
                $newShipment['retour'] = (bool) $mypaOrder->retour;
                $newShipment['backgroundColor'] = $state->color;
                $newShipment['color'] = Tools::getBrightness($state->color) < 128 ? '#ffffff' : '#383838';
                $newShipment['state_text'] = $state->name;
                $newShipment['date_upd'] = $mypaOrder->date_upd;
            } else {
                $newShipment['id_order'] = null;
                $newShipment['id_myparcel_order'] = null;
                $newShipment['backgroundColor'] = null;
                $newShipment['color'] = null;
                $newShipment['state_text'] = null;
                $newShipment['retour'] = false;
                $newShipment['date_upd'] = date('Y-m-d H:i:s', strtotime($shipment['created']));
            }
            $newShipment['postcode'] = $shipment['recipient']['postal_code'];
            $newShipment['tracktrace'] = $shipment['barcode'];
            $newShipment['postnl_status'] = $shipment['status'];
            $newShipment['postnl_final'] = $shipment['status'] >= 7;
            $newShipment['shipment'] = $shipment;
            $response->set("data.shipments.{$index}", $newShipment);
        }

        die(mypa_json_encode($response->jsonSerialize()));
    }

    /**
     * @throws PrestaShopException
     *
     * @since 2.3.0
     *
     * @throws Adapter_Exception
     * @throws ErrorException
     */
    public function ajaxProcessGetShipmentHistory()
    {
        header('Content-Type: application/json; charset=utf-8');
        $requestBody = @json_decode(file_get_contents('php://input'), true);
        $curl = new \MyParcelModule\MyParcelHttpClient();
        $shipments = implode(';', $requestBody['moduleData']['shipments']);
        $order = MyParcelOrder::getOrderByShipmentId($requestBody['moduleData']['shipments'][0]);
        if (!Validate::isLoadedObject($order)) {
            die(mypa_json_encode(array('success' => false)));
        }
        $curl->setHeader('Authorization', 'basic '.base64_encode(Configuration::get(MyParcel::API_KEY, null, $order->id_shop_group, $order->id_shop)));
        $responseContent = $curl->get("https://api.myparcel.nl/tracktraces/{$shipments}");

        $this->getTrackTraceApiInterceptor($responseContent);
        die(mypa_json_encode($responseContent));
    }

    /**
     * Delete shipment
     *
     * @since 2.1.0
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function ajaxProcessDeleteShipment()
    {
        header('Content-Type: application/json; charset=utf-8');
        // @codingStandardsIgnoreStart
        $request = @json_decode(file_get_contents('php://input'), true);
        // @codingStandardsIgnoreEnd
        if (isset($request['idShipment'])) {
            $success = true;
            foreach ($request['idShipment'] as $idShipment) {
                $success &= MyParcelOrder::deleteShipment($idShipment);
            }
            die(mypa_json_encode(array(
                'success' => $success,
            )));
        }

        die(mypa_json_encode(array(
            'success' => false,
        )));
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
     * @throws ErrorException
     *
     * @since 2.0.0
     */
    protected function getShipmentApiInterceptor($responseContent)
    {
        if ($responseContent) {
            if (!is_array($responseContent)) {
                $responseContent = @json_decode($responseContent, true);
            }
            if (is_array(mypa_dot($responseContent)->get('data.shipments'))) {
                foreach (mypa_dot($responseContent)->get('data.shipments') as $shipment) {
                    $shipment = mypa_dot($shipment);
                    $myparcelOrder = MyParcelOrder::getByShipmentId($shipment->get('id'));
                    if (Validate::isLoadedObject($myparcelOrder)) {
                        if ($shipment->get('barcode') || in_array((int) $shipment->get('status'), array(12, 13, 14))) {
                            MyParcelOrder::updateStatus(
                                $myparcelOrder->id_shipment,
                                $shipment->get('barcode'),
                                $shipment->get('status'),
                                $shipment->get('modified')
                            );
                            if (!$myparcelOrder->tracktrace) {
                                MyParcelOrder::updateOrderTrackingNumber($myparcelOrder->id_order, $shipment->get('barcode'));
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Intercept Get Tracktrace API calls
     *
     * @param string $responseContent
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ErrorException
     *
     * @since 2.3.0
     */
    protected function getTrackTraceApiInterceptor($responseContent)
    {
        if (!$responseContent) {
            return;
        }
        if (!is_array($responseContent)) {
            $responseContent = @json_decode($responseContent, true);
        }
        /** @var \MyParcelModule\Firstred\Dot $responseContent */
        $responseContent = mypa_dot($responseContent);
        if (is_array($responseContent->get('data.tracktraces'))) {
            foreach ($responseContent->get('data.tracktraces') as $tracktrace) {
                $tracktrace = mypa_dot($tracktrace);
                $myparcelOrder = MyParcelOrder::getByShipmentId($tracktrace->get('shipment_id'));
                if (Validate::isLoadedObject($myparcelOrder) && $myparcelOrder->tracktrace) {
                    MyParcelOrder::updateStatus(
                        $myparcelOrder->id_shipment,
                        $myparcelOrder->tracktrace,
                        $tracktrace->get('status.current'),
                        date('Y-m-d', strtotime($tracktrace->get('time')))
                    );
                }
            }
        }
    }

    /**
     * Ajax process - create label(s)
     *
     * @throws ErrorException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public function ajaxProcessCreateLabel()
    {
        header('Content-Type: application/json; charset=utf-8');
        \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::setHttpClientClass('\\MyParcelModule\\MyParcelHttpClient');
        $request = @json_decode(file_get_contents('php://input'), true);

        try {
            $myParcelCollection = MyParcelDeliveryOption::consignmentCollectionFromConceptData($request['conceptData']);
            $myParcelCollection->createConcepts();
            $labelData = $this->processNewLabels($myParcelCollection);
            die(mypa_json_encode($labelData));
        } catch (Exception $e) {
            die(mypa_json_encode(array(
                'success' => false,
                'error'   => $e->getMessage(),
            )));
        }
    }

    /**
     * Print label
     *
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     * @throws Adapter_Exception
     * @throws ErrorException
     */
    public function ajaxProcessPrintLabel()
    {
        header('Content-Type: application/json; charset=utf-8');
        $requestBody = file_get_contents('php://input');
        $request = @json_decode($requestBody, true);
        if (is_array($request) && array_key_exists('idShipments', $request)) {
            $idShipments = $request['idShipments'];
            $shipments = implode(';', $idShipments);
        } else {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }
        $order = MyParcelOrder::getOrderByShipmentId($request['idShipments'][0]);
        if (!Validate::isLoadedObject($order)) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }
        /** @var Order $myparcelOrder */
        $curl = new \MyParcelModule\MyParcelHttpClient();
        $curl->setHeader('Accept', 'application/json; charset=utf-8');
        $curl->setHeader('Authorization', 'basic '.base64_encode(Configuration::get(static::API_KEY, null, $order->id_shop_group, $order->id_shop)));

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

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

        $response = $curl->get("https://api.myparcel.nl/shipment_labels/{$shipments}?positions={$positions}&format={$pageSize}");
        if ($response) {
            $response['success'] = true;
            foreach ($idShipments as $idShipment) {
                $mpo = MyParcelOrder::getByShipmentId($idShipment);
                if (!Validate::isLoadedObject($mpo)) {
                    $response['success'] = false;
                } else {
                    $response['success'] &= $mpo->printed();
                }
            }

            die(mypa_json_encode($response));
        }

        die(mypa_json_encode(array(
            'success' => false,
        )));
    }

    /**
     * @return void
     *
     * @since 2.0.0
     * @throws PrestaShopException
     * @throws ErrorException
     */
    public function ajaxProcessCreateRelatedReturnLabel()
    {
        header('Content-Type: application/json; charset=utf-8');
        $request = mypa_dot(@json_decode(file_get_contents('php://input'), true));
        if ($request->has('moduleData.parent')) {
            $parent = (int) $request->get('moduleData.parent');
        } else {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $sql = new DbQuery();
        $sql->select('c.`firstname`, c.`lastname`, c.`email`, mo.`id_shipment`, mo.`postcode`, o.`id_order`');
        $sql->from(bqSQL(MyParcelOrder::$definition['table']), 'mo');
        $sql->innerJoin(bqSQL(Order::$definition['table']), 'o', 'o.`id_order` = mo.`id_order`');
        $sql->innerJoin(bqSQL(Customer::$definition['table']), 'c', 'c.`id_customer` = o.`id_customer`');
        $sql->where('`id_shipment` = '.(int) $parent);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        if (!$result) {
            die(mypa_json_encode(array(
                'success' => false,
                'error'   => 'No shipments found in db',
            )));
        }

        // @codingStandardsIgnoreStart
        $curl = new \MyParcelModule\MyParcelHttpClient();
        $curl->setHeader('Content-Type', 'application/vnd.return_shipment+json;charset=utf-8');
        $packageType = (int) $request->get('moduleData.returnConfig.packageType', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL);
        $returnShipment = array(
            'parent'  => (int) $parent,
            'carrier' => 1,
            'name'    => (string) $request->get('moduleData.returnConfig.name'),
            'email'   => (string) $request->get('moduleData.returnConfig.email'),
            'options' => array(
                'package_type'        => (int) $packageType,
                'label_description'   => (string) $request->get('moduleData.returnConfig.labelDescription'),
            ),
        );
        if ($packageType === MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL) {
            if ($request->get('moduleData.returnConfig.largeFormat')) {
                $returnShipment['options']['large_format'] = (int) $request->get('moduleData.returnConfig.largeFormat');
            }

            if ($request->get('moduleData.returnConfig.cc') === 'NL') {
                $returnShipment['options']['signature'] = (int) $request->get('moduleData.returnConfig.signature');
                $returnShipment['options']['only_recipient'] = (int) $request->get('moduleData.returnConfig.onlyeRecipient');
                $returnShipment['options']['return'] = (int) $request->get('moduleData.returnConfig.returnUndeliverable');
                $returnShipment['options']['insurance'] = array(
                    'amount'   => (int) $request->get('moduleData.returnConfig.insurance'),
                    'currency' => 'EUR',
                );
            }
        }
        if ($request->get('moduleData.returnConfig.returnContribution')) {
            $returnShipment['options']['contribution'] = array(
                'amount'   => (int) ($request->get('moduleData.returnConfig.returnContributionAmount') * 100),
                'currency' => 'EUR',
            );
        }
        $response = $curl->post(
            'https://api.myparcel.nl/shipments',
            mypa_json_encode(array('data' => array('return_shipments' => array($returnShipment))))
        );

        if ($response && isset($response['data'])) {
            Db::getInstance()->update(
                bqSQL(MyParcelOrder::$definition['table']),
                array('retour' => true),
                '`id_shipment` = '.(int) $parent
            );
            die(mypa_json_encode(array('success' => true)));
        }

        die(mypa_json_encode(array('success' => false)));
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
    public function ajaxProcessSaveConceptData()
    {
        header('Content-Type: application/json; charset=utf-8');
        // @codingStandardsIgnoreStart
        $data = mypa_dot(@json_decode(file_get_contents('php://input'), true));
        // @codingStandardsIgnoreEnd

        if ($data->has('idOrder') && $data->has('concept')) {
            $success = MyParcelDeliveryOption::saveConceptData((int) $data['idOrder'], $data);
            die(mypa_json_encode(array('success' => (bool) $success)));
        }

        die(mypa_json_encode(array('success' => false)));
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
                'href'   => static::appendQueryToUrl($this->baseUrl, array('menu' => (string) static::MENU_MAIN_SETTINGS)),
                'active' => false,
                'icon'   => 'icon-gears',
            ),
            'defaultsettings' => array(
                'short'  => $this->l('Shipping settings'),
                'desc'   => $this->l('Default shipping settings'),
                'href'   => static::appendQueryToUrl($this->baseUrl, array('menu' => (string) static::MENU_DEFAULT_SETTINGS)),
                'active' => false,
                'icon'   => 'icon-truck',
            ),
            'deliveryoptions' => array(
                'short'  => $this->l('Delivery options'),
                'desc'   => $this->l('Available delivery options'),
                'href'   => static::appendQueryToUrl($this->baseUrl, array('menu' => (string) static::MENU_DEFAULT_DELIVERY_OPTIONS)),
                'active' => false,
                'icon'   => 'icon-shopping-cart',
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
     * @throws Adapter_Exception
     * @throws ErrorException
     *
     * @since 2.0.0
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('tour_step')) {
            $this->postProcessTour();
        }

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
     * @return void
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.2.0
     */
    protected function postProcessTour()
    {
        $tourStep = (int) Tools::getValue('tour_step');

        Configuration::updateValue(static::TOUR_CURRENT_STEP, (int) Tools::getValue('tour_step'), false, 0, 0);

        if (Tools::isSubmit('tour_redirect')) {
            switch ($tourStep) {
                case static::TOUR_STEP_MAIN:
                case static::TOUR_STEP_DESIGN:
                case static::TOUR_STEP_LABELS_NOTIFICATIONS:
                    Tools::redirectAdmin(static::appendQueryToUrl(
                        $this->baseUrl,
                        array('tour_step' => $tourStep, 'menu' => static::MENU_MAIN_SETTINGS)
                    ));
                    break;
                case static::TOUR_STEP_DELIVERY_OPTIONS:
                    Tools::redirectAdmin(static::appendQueryToUrl(
                        $this->baseUrl,
                        array(
                            'tour_step'                                                   => $tourStep,
                            'menu'                                                        => static::MENU_DEFAULT_DELIVERY_OPTIONS,
                            MyParcelCarrierDeliverySetting::$definition['primary']        => MyParcelCarrierDeliverySetting::getByCarrierReference(Configuration::get(static::POSTNL_DEFAULT_CARRIER))->id,
                            'update'.MyParcelCarrierDeliverySetting::$definition['table'] => '',
                        )
                    ));
                    break;
                case static::TOUR_STEP_CARRIER_CONFIG:
                    Tools::redirectAdmin($this->getAdminLink(
                        'AdminCarrierWizard',
                        true,
                        array(
                            'id_carrier' => Carrier::getCarrierByReference(Configuration::get(static::POSTNL_DEFAULT_CARRIER))->id,
                        )
                    ));
                    break;
                case static::TOUR_STEP_START_SHIPPING:
                    Tools::redirectAdmin($this->getAdminLink('AdminOrders', true));
                    break;
                default:
                    break;
            }
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
                        if ((int) Tools::getValue(static::DEFAULT_CONCEPT_INSURED_TYPE) === static::INSURED_TYPE_500_PLUS) {
                            $correctedAmount = max(1000, static::findValidInsuranceAmount((int) Tools::getValue($key)));
                            Configuration::updateValue($key, $correctedAmount * 100);
                        }
                        break;
                    case static::DEFAULT_RETURN_CONCEPT_INSURED_AMOUNT:
                        if ((int) Tools::getValue(static::DEFAULT_RETURN_CONCEPT_INSURED_TYPE) === static::INSURED_TYPE_500_PLUS) {
                            $correctedAmount = max(static::findValidInsuranceAmount((int) Tools::getValue($key)), 1000);
                            Configuration::updateValue($key, $correctedAmount * 100);
                        }
                        break;
                    default:
                        Configuration::updateValue($key, Tools::getValue($key));
                        break;
                }
            }
        }

        if (Tools::isSubmit('myparcel-62-item-status')) {
            Configuration::updateValue(static::DEFAULT_CONCEPT_CUSTOMS_STATUS, (string) Tools::getValue('myparcel-62-item-status'));
            Configuration::updateValue(static::DEFAULT_CONCEPT_AGE_CHECK, (bool) Tools::isSubmit('myparcel-62-item-age-check'));
            Configuration::updateValue(static::DEFAULT_CONCEPT_COOLED_DELIVERY, (bool) Tools::isSubmit('myparcel-62-item-cooled-delivery'));
        }
        if (Tools::isSubmit('myparcel-62-item-classification')) {
            Configuration::updateValue(static::DEFAULT_CONCEPT_CLASSIFICATION, (string) Tools::getValue('myparcel-62-item-classification'));
        }
        if (Tools::isSubmit('myparcel-62-item-country')) {
            Configuration::updateValue(static::DEFAULT_CONCEPT_COUNTRY_OF_ORIGIN, (string) Tools::getValue('myparcel-62-item-country'));
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
            static::LINK_EMAIL         => Configuration::get(static::LINK_EMAIL),
            static::LINK_PHONE         => Configuration::get(static::LINK_PHONE),
            static::USE_PICKUP_ADDRESS => Configuration::get(static::USE_PICKUP_ADDRESS),

            static::DEFAULT_CONCEPT_PARCEL_TYPE        => Configuration::get(static::DEFAULT_CONCEPT_PARCEL_TYPE),
            static::DEFAULT_CONCEPT_LARGE_PACKAGE      => Configuration::get(static::DEFAULT_CONCEPT_LARGE_PACKAGE),
            static::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY => Configuration::get(static::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
            static::DEFAULT_CONCEPT_SIGNED             => Configuration::get(static::DEFAULT_CONCEPT_SIGNED),
            static::DEFAULT_CONCEPT_RETURN             => Configuration::get(static::DEFAULT_CONCEPT_RETURN),
            static::DEFAULT_CONCEPT_INSURED            => Configuration::get(static::DEFAULT_CONCEPT_INSURED),
            static::DEFAULT_CONCEPT_INSURED_TYPE       => Configuration::get(static::DEFAULT_CONCEPT_INSURED_TYPE),
            static::DEFAULT_CONCEPT_INSURED_AMOUNT     => (int) Configuration::get(static::DEFAULT_CONCEPT_INSURED_AMOUNT) / 100,

            static::ASK_RETURN_SELECTION                      => Configuration::get(static::ASK_RETURN_SELECTION),
            static::DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE      => Configuration::get(static::DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE),
            static::DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY => Configuration::get(static::DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY),
            static::DEFAULT_RETURN_CONCEPT_SIGNED             => Configuration::get(static::DEFAULT_RETURN_CONCEPT_SIGNED),
            static::DEFAULT_RETURN_CONCEPT_RETURN             => Configuration::get(static::DEFAULT_RETURN_CONCEPT_RETURN),
            static::DEFAULT_RETURN_CONCEPT_INSURED            => Configuration::get(static::DEFAULT_RETURN_CONCEPT_INSURED),
            static::DEFAULT_RETURN_CONCEPT_INSURED_TYPE       => Configuration::get(static::DEFAULT_RETURN_CONCEPT_INSURED_TYPE),
            static::DEFAULT_RETURN_CONCEPT_INSURED_AMOUNT     => (int) Configuration::get(static::DEFAULT_RETURN_CONCEPT_INSURED_AMOUNT) / 100,
            static::MONDAY_DELIVERY_SUPPORTED                 => (bool) Configuration::get(static::MONDAY_DELIVERY_SUPPORTED),
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
     * @throws Adapter_Exception
     * @throws PrestaShopException
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
        } elseif (Tools::isSubmit('digital_stamp'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::toggleDigitalStamp(
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function postProcessDeliverySettingForm()
    {
        $mss = new MyParcelCarrierDeliverySetting((int) Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']));
        /** @var Carrier $carrier */
        $carrier = Carrier::getCarrierByReference($mss->id_reference);
        // PrestaShop does not load a full Carrier object by default
        $carrier = new Carrier($carrier->id);
        if (!Validate::isLoadedObject($mss) || !Validate::isLoadedObject($carrier)) {
            $this->addError($this->l('Could not process delivery setting'));

            return;
        }
        if (array_sum(array(
            Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY),
            Tools::getValue(MyParcelCarrierDeliverySetting::PICKUP),
            Tools::getValue(MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE),
            Tools::getValue(MyParcelCarrierDeliverySetting::DIGITAL_STAMP),
        ))) {
            $carrier->external_module_name = $this->name;
            $carrier->shipping_external = true;
            $carrier->is_module = true;
        } else {
            $carrier->external_module_name = '';
            $carrier->shipping_external = false;
            $carrier->is_module = false;
        }
        $carrier->save();

        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW} = (int) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW);
        $mss->{MyParcelCarrierDeliverySetting::DROPOFF_DELAY} = (int) Tools::getValue(MyParcelCarrierDeliverySetting::DROPOFF_DELAY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::PICKUP} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE);
        $mss->{MyParcelCarrierDeliverySetting::DIGITAL_STAMP} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DIGITAL_STAMP);
        $mss->{MyParcelCarrierDeliverySetting::MORNING} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING);
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::EVENING} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::EVENING);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED);
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY);

        if ($mss->{MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW} +
            $mss->{MyParcelCarrierDeliverySetting::DROPOFF_DELAY} > 14
        ) {
            $this->addError($this->l('Total of `Drop off delay` and `Amount of days to show ahead` cannot be more than 14'));
            return;
        }

        $mss->{MyParcelCarrierDeliverySetting::MONDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MONDAY_ENABLED);
        $mondayTime = Tools::getValue(MyParcelCarrierDeliverySetting::MONDAY_CUTOFF);
        if ($this->isTime($mondayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::MONDAY_CUTOFF} = pSQL($mondayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::TUESDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::TUESDAY_ENABLED);
        $tuesdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::TUESDAY_CUTOFF);
        if ($this->isTime($tuesdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::TUESDAY_CUTOFF} = pSQL($tuesdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::WEDNESDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::WEDNESDAY_ENABLED);
        $wednesdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::WEDNESDAY_CUTOFF);
        if ($this->isTime($wednesdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::WEDNESDAY_CUTOFF} = pSQL($wednesdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::THURSDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::THURSDAY_ENABLED);
        $thursdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::THURSDAY_CUTOFF);
        if ($this->isTime($thursdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::THURSDAY_CUTOFF} = pSQL($thursdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::FRIDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::FRIDAY_ENABLED);
        $fridayTime = Tools::getValue(MyParcelCarrierDeliverySetting::FRIDAY_CUTOFF);
        if ($this->isTime($fridayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::FRIDAY_CUTOFF} = pSQL($fridayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::SATURDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SATURDAY_ENABLED);
        $saturdayTime = Tools::getValue(MyParcelCarrierDeliverySetting::SATURDAY_CUTOFF);
        if ($this->isTime($saturdayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::SATURDAY_CUTOFF} = pSQL($saturdayTime);
        }
        $mss->{MyParcelCarrierDeliverySetting::SUNDAY_ENABLED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SUNDAY_ENABLED);
        $sundayTime = Tools::getValue(MyParcelCarrierDeliverySetting::SUNDAY_CUTOFF);
        if ($this->isTime($sundayTime)) {
            $mss->{MyParcelCarrierDeliverySetting::SUNDAY_CUTOFF} = pSQL($sundayTime);
        }

        if (Tools::isSubmit(MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS)) {
            $mss->{MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS} = Tools::getValue(MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS);
        }

        $mss->{MyParcelCarrierDeliverySetting::MORNING_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_FEE));
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE));
        $mss->{MyParcelCarrierDeliverySetting::EVENING_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::EVENING_FEE));
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_FEE));
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE));
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE} = (float) str_replace(',', '.', Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE));

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
            $myparcelCarrierDeliverySetting->digital_stamp = false;
        } elseif ($myparcelCarrierDeliverySetting->digital_stamp) {
            $myparcelCarrierDeliverySetting->mailbox_package = false;
            $myparcelCarrierDeliverySetting->delivery = false;
            $myparcelCarrierDeliverySetting->pickup = false;
        } elseif ($myparcelCarrierDeliverySetting->delivery || $myparcelCarrierDeliverySetting->pickup) {
            $myparcelCarrierDeliverySetting->mailbox_package = false;
            $myparcelCarrierDeliverySetting->digital_stamp = false;
        }
    }

    /**
     * Post process main settings page
     *
     * @return void
     *
     * @throws ErrorException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
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
            Configuration::updateValue(static::CHECKOUT_FG_COLOR3, Tools::getValue(static::CHECKOUT_FG_COLOR3));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR1, Tools::getValue(static::CHECKOUT_BG_COLOR1));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR2, Tools::getValue(static::CHECKOUT_BG_COLOR2));
            Configuration::updateValue(static::CHECKOUT_BG_COLOR3, Tools::getValue(static::CHECKOUT_BG_COLOR3));
            Configuration::updateValue(static::CHECKOUT_HL_COLOR, Tools::getValue(static::CHECKOUT_HL_COLOR));
            Configuration::updateValue(static::CHECKOUT_INACTIVE_COLOR, Tools::getValue(static::CHECKOUT_INACTIVE_COLOR));
            Configuration::updateValue(static::CHECKOUT_FONT, Tools::getValue(static::CHECKOUT_FONT));
            Configuration::updateValue(
                static::CHECKOUT_FONT_SIZE,
                (int) Tools::getValue(static::CHECKOUT_FONT_SIZE) ?: 14
            );
            Configuration::updateValue(static::LOG_API, (bool) Tools::getValue(static::LOG_API));
            Configuration::updateValue(static::DEV_MODE_HIDE_PREFERRED, (bool) Tools::getValue(static::DEV_MODE_HIDE_PREFERRED));
            Configuration::updateValue(static::DEV_MODE_SEARCH_TRACKTRACE, (bool) Tools::getValue(static::DEV_MODE_SEARCH_TRACKTRACE));
            Configuration::updateValue(static::PRINTED_STATUS, (int) Tools::getValue(static::PRINTED_STATUS));
            Configuration::updateValue(static::SHIPPED_STATUS, (int) Tools::getValue(static::SHIPPED_STATUS));
            Configuration::updateValue(static::RECEIVED_STATUS, (int) Tools::getValue(static::RECEIVED_STATUS));
            Configuration::updateValue(static::IGNORE_ORDER_STATUSES, mypa_json_encode($this->getStatusesValue(static::IGNORE_ORDER_STATUSES)));
            Configuration::updateValue(static::NOTIFICATIONS, (bool) Tools::getValue(static::NOTIFICATIONS));
            Configuration::updateValue(static::NOTIFICATION_MOMENT, Tools::getValue(static::NOTIFICATION_MOMENT) ? 1 : 0);
            Configuration::updateValue(static::DIGITAL_STAMP_USE_SHIPPED_STATUS, (bool) Tools::getValue(static::DIGITAL_STAMP_USE_SHIPPED_STATUS));
            Configuration::updateValue(static::LABEL_DESCRIPTION, Tools::getValue(static::LABEL_DESCRIPTION));
            Configuration::updateValue(static::PAPER_SELECTION, Tools::getValue(static::PAPER_SELECTION));
            Configuration::updateValue(static::ASK_PAPER_SELECTION, Tools::getValue(static::ASK_PAPER_SELECTION));

            if (Tools::isSubmit(static::DEV_MODE_SET_VERSION)) {
                $version = Tools::getValue(static::DEV_MODE_SET_VERSION);
                if ($version) {
                    // Reset the module version in the database
                    Db::getInstance()->update(
                        'module',
                        array(
                            'version' => pSQL($version),
                        ),
                        '`name` = \''.pSQL($this->name).'\''
                    );

                    // Invalidate OPCache if necessary
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate(_PS_MODULE_DIR_."{$this->name}/{$this->name}.php");
                    }

                    // Remove config xml files
                    foreach(scandir(_PS_MODULE_DIR_."{$this->name}/") as $file) {
                        if (preg_match('/^config([_a-zA-Z]{3})?\.xml$/', $file)) {
                            @unlink(_PS_MODULE_DIR_."{$this->name}/{$file}");
                        }
                    }

                    $this->context->controller->confirmations[] =
                        sprintf($this->l('Module %s successfully downgraded/upgraded to %s'), $this->name, $version);
                }
            }

            if (Tools::isSubmit(static::DEV_MODE_RESET_TOUR)) {
                if (Tools::getValue(static::DEV_MODE_RESET_TOUR)) {
                    Configuration::updateValue(static::TOUR_CURRENT_STEP, 0, false, 0, 0);
                }
            }
            if (Tools::isSubmit(static::ADDRESS_FIELD_OVERRIDE.'NL')) {
                Configuration::updateValue(static::ADDRESS_FIELD_OVERRIDE.'NL', Tools::getValue(static::ADDRESS_FIELD_OVERRIDE.'NL'));
                Configuration::updateValue(static::ADDRESS_FIELD_OVERRIDE.'BE', Tools::getValue(static::ADDRESS_FIELD_OVERRIDE.'BE'));
            }
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
     *
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
        $helper->currentIndex = $this->getAdminLink(
            'AdminModules',
            false,
            array(
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
                'menu'        => static::MENU_DEFAULT_SETTINGS,
            )
        );
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $countries = array(
                'SKIP' => array(
                    'iso_code' => 'SKIP',
                    'name'     => $this->l('Do not set'),
                    'region'   => 'CD',
                ),
            ) + static::getCountries();

        $helper->tpl_vars = array(
            'fields_value'                   => $this->getDefaultSettingsFormValues(),
            'languages'                      => $this->context->controller->getLanguages(),
            'id_language'                    => $this->context->language->id,
            'mpJsCountries'                  => $countries,
            'mpAssetsUrl'                    => static::getMediaPath($this->_path),
            'mpProductSettings'              => array(
                'classification' => (string) Configuration::get(static::DEFAULT_CONCEPT_CLASSIFICATION),
                'country'        => (string) Configuration::get(static::DEFAULT_CONCEPT_COUNTRY_OF_ORIGIN),
                'status'         => (string) Configuration::get(static::DEFAULT_CONCEPT_CUSTOMS_STATUS),
                'ageCheck'       => (bool) Configuration::get(static::DEFAULT_CONCEPT_AGE_CHECK),
                'cooledDelivery' => (bool) Configuration::get(static::DEFAULT_CONCEPT_COOLED_DELIVERY),
            ),
            'mpGoodsNomenclatureInstallUrl'  => $this->getAdminLink(
                'AdminModules',
                true,
                array(
                    'configure'   => $this->name,
                    'module_name' => $this->name,
                    'ajax'        => '1',
                    'action'      => 'InstallGoodsNomenclature',
                )
            ),
            'mpGoodsNomenclatureSearchUrl'   => $this->getAdminLink(
                'AdminModules',
                true,
                array(
                    'configure'   => $this->name,
                    'module_name' => $this->name,
                    'ajax'        => '1',
                    'action'      => 'SearchGoodsNomenclature',
                )
            ),
            'mpGoodsNomenclatureBrowseUrl'   => $this->getAdminLink(
                'AdminModules',
                true,
                array(
                    'configure'   => $this->name,
                    'module_name' => $this->name,
                    'ajax'        => '1',
                    'action'      => 'BrowseGoodsNomenclature',
                )
            ),
            'mpGoodsNomenclatureNavigateUrl' => $this->getAdminLink(
                'AdminModules',
                true,
                array(
                    'configure'   => $this->name,
                    'module_name' => $this->name,
                    'ajax'        => '1',
                    'action'      => 'NavigateGoodsNomenclature',
                )
            ),
        );

        $forms = array(
            $this->getDefaultConceptsForm(),
            $this->getDefaultReturnConceptsForm(),
            $this->getDefaultProductSettingsForm(),
            $this->getDefaultSettingsForm(),
        );

        if (!static::detectWeightUnit() || !static::detectDimensionUnit()) {
            array_splice($forms, 2, 0, array($this->getDefaultUnitsForm()));
        }

        return $helper->generateForm($forms);
    }

    /**
     * Create the structure of the config form
     *
     * @return array
     *
     * @since 2.0.0
     *
     * @throws PrestaShopException
     */
    protected function getDefaultConceptsForm()
    {
        $currency = Currency::getDefaultCurrency();

        return array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Default concept'),
                    'icon'  => 'icon-pencil-square',
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
                                    'id'   => MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_NORMAL,
                                    'name' => $this->l('Parcel'),
                                ),
                                array(
                                    'id'   => MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_MAILBOX_PACKAGE,
                                    'name' => $this->l('Mailbox package'),
                                ),
                                array(
                                    'id'   => MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_DIGITAL_STAMP,
                                    'name' => $this->l('Digital stamp'),
                                ),
                                array(
                                    'id'   => MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::PACKAGE_TYPE_UNSTAMPED,
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
                                    'id'   => static::INSURED_TYPE_100,
                                    'name' => $this->l('€100'),
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
                        'currency' => version_compare(_PS_VERSION_, '1.6', '>='),
                    ),
                    array(
                        'type'     => 'br',
                        'name'     => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Monday delivery'),
                        'name'    => static::MONDAY_DELIVERY_SUPPORTED,
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
     *
     * @throws PrestaShopException
     */
    protected function getDefaultReturnConceptsForm()
    {
        $currency = Currency::getDefaultCurrency();

        return array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Default return concept'),
                    'icon'  => 'icon-pencil-square',
                ),
                'description' => $this->l('These are the default return concept settings'),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Always prompt for the return settings'),
                        'name'    => static::ASK_RETURN_SELECTION,
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
                        'type' => 'br',
                        'name' => '',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Extra large parcel'),
                        'name'    => static::DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE,
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
                        'name'    => static::DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY,
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
                        'name'    => static::DEFAULT_RETURN_CONCEPT_SIGNED,
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
                        'name'    => static::DEFAULT_RETURN_CONCEPT_RETURN,
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
                        'name'    => static::DEFAULT_RETURN_CONCEPT_INSURED,
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
                        'name'    => static::DEFAULT_RETURN_CONCEPT_INSURED_TYPE,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => static::INSURED_TYPE_100,
                                    'name' => $this->l('€100'),
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
                        'name'     => static::DEFAULT_RETURN_CONCEPT_INSURED_AMOUNT,
                        'size'     => 10,
                        'prefix'   => $currency->sign,
                        'class'    => 'fixed-width-sm',
                        'currency' => version_compare(_PS_VERSION_, '1.6', '>='),
                    ),
                ),
                'submit' => array(
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
     * @throws PrestaShopException
     */
    protected function getDefaultUnitsForm()
    {
        return array(
            'form' => array(
                'legend'      => array(
                    'title' => Translate::getAdminTranslation('Units', 'AdminLocalization'),
                    'icon'  => 'icon-globe',
                ),
                'input'  => array(
                    array(
                        'type'    => 'select',
                        'label'   => Translate::getAdminTranslation('Weight unit', 'AdminLocalization'),
                        'name'    => static::WEIGHT_UNIT,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => 'g',
                                    'name' => $this->l('grams'),
                                ),
                                array(
                                    'id'   => 'kg',
                                    'name' => $this->l('kilograms'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => Translate::getAdminTranslation('Distance unit', 'AdminLocalization'),
                        'name'    => static::DIMENSION_UNIT,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => 'mm',
                                    'name' => $this->l('millimeters'),
                                ),
                                array(
                                    'id'   => 'cm',
                                    'name' => $this->l('centimeters'),
                                ),
                                array(
                                    'id'   => 'm',
                                    'name' => $this->l('meters'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
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
     * Create the defualt product settings form
     *
     * @return array
     *
     * @throws PrestaShopException
     * @throws SmartyException
     *
     * @since 2.3.0
     */
    protected function getDefaultProductSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Default product settings'),
                    'icon'  => 'icon-pencil-square',
                ),
                'input'  => array(
                    array(
                        'type'    => 'myparcel-product-settings',
                        'label'   => $this->l('Default product settings'),
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
                ),
                'submit' => array(
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
     * @throws PrestaShopException
     * @throws SmartyException
     * @since 2.0.0
     */
    protected function getDefaultSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Data'),
                    'icon'  => 'icon-shield',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->display(__FILE__, 'views/templates/admin/gdpr/badge-email.tpl')
                            .' '
                            .$this->l('Share customer\'s email address with MyParcel'),
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
                        'label'   => $this->display(__FILE__, 'views/templates/admin/gdpr/badge-phone.tpl')
                            .' '
                            .$this->l('Share customer\'s phone number with MyParcel'),
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
            $output .= $this->renderDeliveryOptionList();
        }

        return $output;
    }

    /**
     * Update carriers
     *
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
        $currentList = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($carriers as $carrier) {
            $found = false;
            foreach ($currentList as $current) {
                if ($carrier['id_reference'] == $current['id_reference']) {
                    $found = true;
                    break;
                }
            }
            if (!$found && !empty($carrier['id_reference'])) {
                Db::getInstance()->insert(
                    bqSQL(MyParcelCarrierDeliverySetting::$definition['table']),
                    array(
                        'id_reference'                           => (int) $carrier['id_reference'],
                        MyParcelCarrierDeliverySetting::DELIVERY => false,
                        MyParcelCarrierDeliverySetting::PICKUP   => false,
                        'id_shop'                                => $this->getShopId(),
                    )
                );
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function removeOldExceptions($idMyParcelDeliveryOption)
    {
        $samedayDeliveryOption = new MyParcelCarrierDeliverySetting($idMyParcelDeliveryOption);
        if (Validate::isLoadedObject($samedayDeliveryOption)) {
            $exceptions = @json_decode($samedayDeliveryOption->cutoff_exceptions, true);
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
                    $samedayDeliveryOption->cutoff_exceptions = mypa_json_encode($exceptions);
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
        $helper->currentIndex = static::appendQueryToUrl($this->baseUrlWithoutToken, array('menu' => (string) static::MENU_DEFAULT_DELIVERY_OPTIONS));
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
     * @throws Adapter_Exception
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function getDeliveryOptionsFormValues($idMyParcelCarrierDeliverySetting)
    {
        $mcds = new MyParcelCarrierDeliverySetting($idMyParcelCarrierDeliverySetting);
        $mcds->{MyParcelCarrierDeliverySetting::$definition['primary']} = $mcds->id;

        $mcds->{MyParcelCarrierDeliverySetting::MORNING_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::MORNING_FEE});
        $mcds->{MyParcelCarrierDeliverySetting::EVENING_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::EVENING_FEE});
        $mcds->{MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE});
        $mcds->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE});
        $mcds->{MyParcelCarrierDeliverySetting::SIGNED_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::SIGNED_FEE});
        $mcds->{MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE} = str_replace('.', ',', $mcds->{MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE});

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

        $dropOffDelayOptions = array(
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
            $dropOffDelayOptions[] = array(
                'id'   => $i,
                'name' => sprintf($this->l('%d days'), $i),
            );
        }

        $currency = Currency::getDefaultCurrency();
        $carrier = Carrier::getCarrierByReference(MyParcelCarrierDeliverySetting::getCarrierReferenceById(Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])));
        if (!Validate::isLoadedObject($carrier)) {
            return array('form' => array());
        }

        $inputs = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Delivery options')." <div class=\"badge badge-info\">{$carrier->name}</div>",
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
                        'label'   => $this->l('Digital stamp'),
                        'name'    => MyParcelCarrierDeliverySetting::DIGITAL_STAMP,
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
                        'desc'    => $this->l('Show available delivery timeframes'),
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
                            'query' => $dropOffDelayOptions,
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
                        'pricing'    => true,
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
                        'pricing'    => true,
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
                        'pricing'    => true,
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
                        'pricing'    => true,
                        'label'    => $this->l('Fee for recipient only'),
                        'desc'     => $this->l('Extra fee for recipient only'),
                        'name'     => MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'pricing'    => true,
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
                        'pricing'    => true,
                        'label'    => $this->l('Morning pickup fee'),
                        'desc'     => $this->l('Extra fee for morning pickup'),
                        'name'     => MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE,
                        'prefix'   => $currency->sign,
                        'suffix'   => $this->l('incl. 21% VAT'),
                        'required' => true,
                    ),
                ),
            ),
        );

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $inputs['form']['buttons'] = array(
                'back'   => array(
                    'title' => $this->l('Back'),
                    'href'  => "{$this->baseUrl}&menu=".static::MENU_DEFAULT_DELIVERY_OPTIONS,
                    'icon'  => 'process-icon-back',
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default  pull-right',
                    'type'  => 'submit',
                    'icon'  => 'process-icon-save',
                ),
            );
        } else {
            $inputs['form']['submit'] = array(
                'title' => $this->l('Save'),
            );
        }

        return $inputs;
    }

    /**
     * Create the structure of the cut off form
     *
     * @return array Form array
     */
    protected function getCutoffForm()
    {
        $input = array(
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
        );
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $input[] =  array(
                'type'  => 'cutoffexceptions',
                'label' => $this->l('Exception schedule'),
                'name'  => MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS,
            );
        }

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
                'input'       => $input,
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
            'name'                                                        => array(
                'title'           => $this->l('Name'),
                'callback_object' => 'MyParcelTools',
                'callback'        => 'printCarrierName',
            ),
            MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE               => array(
                'title'  => $this->l('Mailbox package'),
                'type'   => 'bool',
                'active' => MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE,
                'ajax'   => false,
                'align'  => 'center',
            ),
            MyParcelCarrierDeliverySetting::DIGITAL_STAMP                 => array(
                'title'  => $this->l('Digital stamp'),
                'type'   => 'bool',
                'active' => MyParcelCarrierDeliverySetting::DIGITAL_STAMP,
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
        );

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $fieldsList['cutoff_times'] = array(
                'title'           => $this->l('Cut off times'),
                'type'            => 'cutoff_times',
                'align'           => 'center',
                'orderby'         => false,
                'search'          => false,
                'class'           => 'sameday-cutoff-labels',
                'callback'        => 'printCutOffItems',
                'callback_object' => 'MyParcelTools',
            );
        }

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = array('edit');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->identifier = bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']);
        $helper->title = "<i class='icon icon-truck'></i> {$this->l('Delivery options')}";
        $helper->table = MyParcelCarrierDeliverySetting::$definition['table'];
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = static::appendQueryToUrl($this->baseUrlWithoutToken, array('menu' => (string) static::MENU_DEFAULT_DELIVERY_OPTIONS));
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
            $cutoffExceptions = @json_decode(
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
            if ($samedaySetting['external_module_name'] && $samedaySetting['external_module_name'] !== $this->name) {
                $samedaySetting['color'] = '#E08F95';
                $samedaySetting[MyParcelCarrierDeliverySetting::PICKUP] = null;
                $samedaySetting[MyParcelCarrierDeliverySetting::DELIVERY] = null;
                $samedaySetting[MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE] = null;
                $samedaySetting[MyParcelCarrierDeliverySetting::DIGITAL_STAMP] = null;
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

        return $this->display(__FILE__, 'load_webpack_chunks.tpl').$this->displayMainForm();
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
        $helper->currentIndex = static::appendQueryToUrl(AdminController::$currentIndex, array('configure' => $this->name));

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
                'href' => static::appendQueryToUrl(
                    AdminController::$currentIndex,
                    array(
                        'configure'         => $this->name,
                        "save{$this->name}" => '',
                        'token'             => Tools::getAdminTokenLite('AdminModules'),
                    )
                ),
            ),
            'back' => array(
                'href' => static::appendQueryToUrl(AdminController::$currentIndex, array('token' => Tools::getAdminTokenLite('AdminModules'))),
                'desc' => $this->l('Back to list'),
            ),
        );
        $helper->fields_value = $this->getMainFormValues();

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
        $values = array(
            static::API_KEY                          => Configuration::get(static::API_KEY),
            static::CHECKOUT_FG_COLOR1               => Configuration::get(static::CHECKOUT_FG_COLOR1) ?: '#FFFFFF',
            static::CHECKOUT_FG_COLOR2               => Configuration::get(static::CHECKOUT_FG_COLOR2 ?: '#000000'),
            static::CHECKOUT_FG_COLOR3               => Configuration::get(static::CHECKOUT_FG_COLOR3 ?: '#000000'),
            static::CHECKOUT_BG_COLOR1               => Configuration::get(static::CHECKOUT_BG_COLOR1 ?: '#FBFBFB'),
            static::CHECKOUT_BG_COLOR2               => Configuration::get(static::CHECKOUT_BG_COLOR2) ?: '#01BBC5',
            static::CHECKOUT_BG_COLOR3               => Configuration::get(static::CHECKOUT_BG_COLOR3) ?: '#75D3D8',
            static::CHECKOUT_HL_COLOR                => Configuration::get(static::CHECKOUT_HL_COLOR) ?: '#FF8C00',
            static::CHECKOUT_INACTIVE_COLOR          => Configuration::get(static::CHECKOUT_INACTIVE_COLOR) ?: '#848484',
            static::CHECKOUT_FONT                    => Configuration::get(static::CHECKOUT_FONT) ?: 'Exo',
            static::CHECKOUT_FONT_SIZE               => Configuration::get(static::CHECKOUT_FONT_SIZE) ?: 2,
            static::LOG_API                          => Configuration::get(static::LOG_API),
            static::PRINTED_STATUS                   => Configuration::get(static::PRINTED_STATUS),
            static::SHIPPED_STATUS                   => Configuration::get(static::SHIPPED_STATUS),
            static::RECEIVED_STATUS                  => Configuration::get(static::RECEIVED_STATUS),
            static::NOTIFICATIONS                    => Configuration::get(static::NOTIFICATIONS),
            static::NOTIFICATION_MOMENT              => Configuration::get(static::NOTIFICATION_MOMENT),
            static::DIGITAL_STAMP_USE_SHIPPED_STATUS => Configuration::get(static::DIGITAL_STAMP_USE_SHIPPED_STATUS),
            static::LABEL_DESCRIPTION                => Configuration::get(static::LABEL_DESCRIPTION),
            static::PAPER_SELECTION                  => Configuration::get(static::PAPER_SELECTION),
            static::ASK_PAPER_SELECTION              => Configuration::get(static::ASK_PAPER_SELECTION),
            static::DEV_MODE_RESET_TOUR              => null,
            static::DEV_MODE_SET_VERSION             => null,
            static::ADDRESS_FIELD_OVERRIDE.'NL'      => Configuration::get(static::ADDRESS_FIELD_OVERRIDE.'NL'),
            static::ADDRESS_FIELD_OVERRIDE.'BE'      => Configuration::get(static::ADDRESS_FIELD_OVERRIDE.'BE'),
            static::DEV_MODE_HIDE_PREFERRED          => Configuration::get(static::DEV_MODE_HIDE_PREFERRED),
            static::DEV_MODE_SEARCH_TRACKTRACE       => Configuration::get(static::DEV_MODE_SEARCH_TRACKTRACE),
        );

        foreach (static::getIgnoredStatuses() as $conf) {
            $values[static::IGNORE_ORDER_STATUSES.'_'.(int) $conf] = true;
        }

        return $values;
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
                    'title' => '<img width="128" height="128" style="width: 16px; height: 16px" src="'.static::getMediaPath($this->_path.'views/img/myparcelnl-grayscale.png').'"> '.$this->l('MyParcel API'),
                ),
                'description' => MyParcelTools::ppTags(
                    $this->l('Please enter your API key. You can find this on the general settings page of the MyParcel [1]back office[/1].'),
                    array('<a href="https://backoffice.myparcel.nl/settings" target="_blank" rel="noopener noreferrer">')
                ),
                'input'  => array(
                    array(
                        'type'      => 'text',
                        'label'     => $this->l('MyParcel API Key'),
                        'name'      => static::API_KEY,
                        'required'  => true,
                        'prefix'    => '<i class="icon icon-key"></i>',
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
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
        $orderStatuses = OrderState::getOrderStates($this->context->language->id);

        for ($i = 0; $i < count($orderStatuses); $i++) {
            $orderStatuses[$i]['name'] = $orderStatuses[$i]['id_order_state'].' - '.$orderStatuses[$i]['name'];
        }

        $this->aasort($orderStatuses, 'id_order_state');

        $this->context->smarty->assign(array(
            'shippedStatusName'   => $shippedStatus->name,
            'deliveredStatusName' => $deliveredStatus->name,
        ));

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Notifications'),
                    'icon'  => 'icon-bell',
                ),
                'input'  => array(
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Printed status'),
                        'desc'     => $this->l('Apply this status when the label has been printed'),
                        'name'     => static::PRINTED_STATUS,
                        'options'  => array(
                            'default' => array('value' => '0', 'label' => "0 - {$this->l('Disable this status')}"),
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
                            'default' => array('value' => '0', 'label' => "0 - {$this->l('Disable this status')}"),
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
                            'default' => array('value' => '0', 'label' => "0 - {$this->l('Disable this status')}"),
                            'query' => $orderStatuses,
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'checkbox',
                        'label'    => $this->l('Ignored order statuses'),
                        'desc'     => $this->l('Once an order hits one of the following statuses, the MyParcel module will stop updating the order for as long as it has the specific status. This can be useful for e.g. backorders.'),
                        'name'     => static::IGNORE_ORDER_STATUSES,
                        'multiple' => true,
                        'values'   => array(
                            'query' => $orderStatuses,
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                        'expand'   => (count($orderStatuses) > 20) ? array(
                            'print_total' => count($orderStatuses),
                            'default'     => 'show',
                            'show'        => array('text' => $this->l('Show'), 'icon' => 'plus-sign-alt'),
                            'hide'        => array('text' => $this->l('Hide'), 'icon' => 'minus-sign-alt'),
                        ) : null,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->display(__FILE__, 'views/templates/admin/gdpr/badge-notifications.tpl')
                            .' '
                            .sprintf(
                                $this->l('Send notification emails via %s'),
                                'PrestaShop'
                            ),
                        'name'    => static::NOTIFICATIONS,
                        'desc'    => $this->display(__FILE__, 'views/templates/admin/notificationdesc.tpl'),
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
                        'label'    => $this->l('Automatically mark a shipment as `shipped` when a digital stamp is printed'),
                        'name'    => static::DIGITAL_STAMP_USE_SHIPPED_STATUS,
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
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function getCheckoutForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Checkout design'),
                    'icon'  => 'icon-paint-brush',
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
                        'label'    => $this->l('Title color'),
                        'name'     => static::CHECKOUT_FG_COLOR3,
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
                        'type'     => 'color',
                        'label'    => $this->l('Inactive color'),
                        'name'     => static::CHECKOUT_INACTIVE_COLOR,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'  => 'fontselect',
                        'label' => "{$this->display(__FILE__, 'views/templates/admin/gdpr/badge-fonts.tpl')} {$this->l('Font family')}",
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
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Always prompt for the paper size'),
                        'name'    => static::ASK_PAPER_SELECTION,
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
     * Get the advanced form
     *
     * @return array Form
     * @throws PrestaShopException
     * @throws SmartyException
     */
    protected function getAdvancedForm()
    {
        $inputs = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Advanced'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => "{$this->display(__FILE__, 'views/templates/admin/gdpr/badge-logging.tpl')} {$this->l('API logger')}",
                        'desc'    => $this->l('By enabling this option, API calls are being logged.')
                            .' '
                            .sprintf($this->l('They can be found on the page `%s > %s`.'), MyParcelTools::getTabName('AdminTools'), MyParcelTools::getTabName('AdminLogs')),
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
                    array(
                        'type'    => 'switch',
                        'name'    => static::DEV_MODE_RESET_TOUR,
                        'label'   => $this->l('Reset tour'),
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
                        'hidden'   => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Force module version'),
                        'name'     => static::DEV_MODE_SET_VERSION,
                        'size'     => 50,
                        'hidden'   => true,
                        'class'    => 'myparcel-dev-always-hidden fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => 'Override address line for NL',
                        'name'     => static::ADDRESS_FIELD_OVERRIDE.'NL',
                        'size'     => 100,
                        'hidden'   => true,
                        'class'    => 'myparcel-dev-always-hidden fixed-width-xxl',
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => 'Override address line for BE',
                        'name'     => static::ADDRESS_FIELD_OVERRIDE.'BE',
                        'size'     => 100,
                        'hidden'   => true,
                        'class'    => 'myparcel-dev-always-hidden fixed-width-xxl',
                    ),
                    array(
                        'type'    => 'switch',
                        'name'    => static::DEV_MODE_HIDE_PREFERRED,
                        'label'   => $this->l('Hide column with preferred delivery dates'),
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
                        'hidden'   => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'name'    => static::DEV_MODE_SEARCH_TRACKTRACE,
                        'label'   => $this->l('Add a search box to the track&trace column'),
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
                        'hidden'   => true,
                    ),
                ),
            ),
        );

        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $inputs['form']['buttons'] = array(
                'submit' => array(
                    'title' => $this->l('Save'),
                    'icon'  => 'process-icon-save',
                    'class' => 'btn btn-default pull-right',
                    'type'  => 'submit',
                ),
                'dev_mode' => array(
                    'id'    => 'myparcel-dev-btn',
                    'title' => 'Dev',
                    'icon'  => 'process-icon-cogs',
                    'class' => 'btn btn-default myparcel-dev-hidden',
                ),
            );
        } else {
            $inputs['form']['submit'] = array(
                'title' => $this->l('Save'),
            );
        }

        return $inputs;
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
        /** @var array $smartyVars */
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
        $countryIso = Tools::strtoupper(Country::getIsoById($address->id_country));
        if (!in_array($countryIso, array('NL', 'BE'))) {
            return '';
        }

        $matches = MyParcelTools::getParsedAddress(new Address((int) $cart->id_address_delivery));
        if (!$matches['number']) {
            // No house number
            if (Configuration::get(static::LOG_API)) {
                Logger::addLog("{$this->displayName}: No house number for Cart {$cart->id}");
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
        if (!Validate::isLoadedObject($mcds) || $carrier->external_module_name !== $this->name) {
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
        $idOrder = (int) $params['id_order'];
        if (!$idOrder) {
            $idOrder = (int) Tools::getValue('id_order');
        }
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $this->context->smarty->assign(
            array(
                'mpIdOrder'                      => (int) $idOrder,
                'mpConcept'                      => mypa_json_encode(MyParcelDeliveryOption::getByOrderId((int) $idOrder)),
                'mpPreAlerted'                   => mypa_json_encode(MyParcelOrder::getByOrderIds(array((int) $idOrder))),
                'mpProcessUrl'                   => static::appendQueryToUrl($this->baseUrl, array('ajax' => '1')),
                'mpModuleDir'                    => __PS_BASE_URI__."modules/{$this->name}/",
                'mpJsCountries'                  => static::getCountries(),
                'mpInvoiceSuggestion'            => MyParcelTools::getInvoiceSuggestion($order),
                'mpWeightSuggestion'             => MyParcelTools::getWeightSuggestion($order),
                'mpPaperSize'                    => @json_decode(Configuration::get(static::PAPER_SELECTION)),
                'mpAskPaperSize'                 => (bool) Configuration::get(static::ASK_PAPER_SELECTION),
                'mpAskReturnConfig'              => (bool) Configuration::get(static::ASK_RETURN_SELECTION),
                'mpReturnInsuranceAmount'        => MyParcelTools::getInsuranceAmount(true),
                'mpLogApi'                       => (bool) Configuration::get(static::LOG_API),
                'mpOnlyRecipient'                => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_HOME_DELIVERY_ONLY),
                'mpSignature'                    => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_SIGNED),
                'mpExtraLarge'                   => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_LARGE_PACKAGE),
                'mpReturnUndeliverable'          => (bool) Configuration::get(static::DEFAULT_RETURN_CONCEPT_RETURN),
                'mpCurrency'                     => Context::getContext()->currency,
                'mpGoodsNomenclatureInstallUrl'  => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'InstallGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureSearchUrl'   => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'SearchGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureBrowseUrl'   => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'BrowseGoodsNomenclature',
                    )
                ),
                'mpGoodsNomenclatureNavigateUrl' => $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(
                        'configure'   => $this->name,
                        'module_name' => $this->name,
                        'ajax'        => '1',
                        'action'      => 'NavigateGoodsNomenclature',
                    )
                ),
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
     * @throws ErrorException
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

        $deliveryOption = mypa_dot(MyParcelDeliveryOption::getRawByCartId($cart->id));
        $mpcs = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        $mailboxPackage = MyParcelDeliveryOption::checkMailboxPackage($cart);
        $digitalStamp = MyParcelDeliveryOption::checkDigitalStamp($cart);

        // Check if the chosen carrier supports the MyParcel pickup or delivery options
        if (!$mpcs
            || !Validate::isLoadedObject($mpcs)
            || !in_array(Tools::strtoupper($country->iso_code), array('NL', 'BE'))
        ) {
            MyParcelDeliveryOption::removeDeliveryOption($cart->id);

            return;
        }

        if ($mpcs->pickup && !$mpcs->delivery && !$deliveryOption->has('data.time.0.type')) {
            $parsedAddress = MyParcelTools::getParsedAddress($address);
            $deliveryOptions = mypa_dot(static::getDeliveryOptions(Tools::strtoupper($country->iso_code), $address->postcode, $parsedAddress['number']));
            if (!$deliveryOptions->has('data.pickup.0')) {
                return;
            }
            $pickups = $deliveryOptions->get('data.pickup');
            usort($pickups, function ($a, $b) {
               return $b['distance'] < $a['distance'];
            });
            list($pickup) = $pickups;
            $pickup['time'] = array_filter($pickup['time'], function ($time) {
                return (int) $time['type'] === MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DELIVERY_TYPE_RETAIL;
            });
            $deliveryOption = mypa_dot(array());
            $deliveryOption->set('idOrder', (int) $order->id);
            $deliveryOption->set('data', $pickup);
        }

        $concept = MyParcelDeliveryOption::createConcept($order, $deliveryOption, $address, $mailboxPackage, $digitalStamp);
        // Convert the pickup address to a PrestaShop address when enabled
        if ($mpcs->pickup
            && in_array($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE), array(4, 5))
            && Configuration::get(static::USE_PICKUP_ADDRESS)
        ) {
            $newAddress = MyParcelTools::getCustomerAddress($customer->id, $deliveryOption->get('data.location_code'));
            if (!Validate::isLoadedObject($newAddress)) {
                $newAddress->id_customer = $customer->id;
                $newAddress->alias = "myparcel-{$deliveryOption->get('data.location_code')}";
                $newAddress->company = $deliveryOption->get('data.location');
                $newAddress->firstname = $address->firstname;
                $newAddress->lastname = $address->lastname;
                $newAddress->postcode = $deliveryOption->get('data.postal_code');
                $newAddress->city = $deliveryOption->get('data.city');
                $newAddress->id_country = $address->id_country;
                $newAddress->phone = $deliveryOption->get('data.phone_number');

                // Figure out which address fields are active and parse the MyParcel formatted address
                list (, $numberField, $numberSuffixField) = $addressFields = MyParcelTools::getAddressLineFields($newAddress->id_country);
                $addressLine = "{$deliveryOption->get('data.street')} {$deliveryOption->get('data.number')}";
                $addressFields = array_filter($addressFields, function ($item) {
                    return (bool) $item;
                });

                // Convert to a PrestaShop address
                switch (array_sum($addressFields)) {
                    case 2:
                        if (preg_match(MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::SPLIT_STREET_REGEX, $addressLine, $m)) {
                            $newAddress->address1 = $deliveryOption->get('data.street');
                            $newAddress->{$numberField} = isset($m['street_suffix']) ? $m['street_suffix'] : '';
                        } else {
                            $newAddress->address1 = $addressLine;
                        }
                        break;
                    case 3:
                        if (preg_match(MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::SPLIT_STREET_REGEX, $addressLine, $m)) {
                            $newAddress->address1 = $deliveryOption->get('data.street');
                            $newAddress->{$numberField} = isset($m['number']) ? $m['number'] : '';
                            $newAddress->{$numberSuffixField} = isset($m['number_suffix']) ? $m['number_suffix'] : '';
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

        if ($deliveryOption->has('data')) {
            $extraOptions = $deliveryOption->get('extraOptions', array());
            if (($mpcs->delivery && in_array($deliveryOption->get('data.time.0.type'), array(1, 2, 3)))
                || ($mpcs->pickup && in_array($deliveryOption->get('data.time.0.type'), array(4, 5)))
            ) {
                $deliveryOption = array('data' => array());
            } else {
                $deliveryOption = array();
            }
            $deliveryOption += array(
                'extraOptions' => $extraOptions,
                'concept'      => $concept,
                'idOrder'      => (int) $order->id,
            );
        } else {
            $deliveryOption = array(
                'idOrder' => (int) $order->id,
                'concept' => $concept,
            );
        }
        MyParcelDeliveryOption::saveRawDeliveryOption(mypa_json_encode($deliveryOption), $cart->id);
    }

    /**
     * Edit order grid display
     *
     * @param array $params
     *
     * @since 2.1.0
     *
     * @throws PrestaShopException
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        if (!Tools::isSubmit('exportorder') && Configuration::get(static::API_KEY)) {
            if (isset($params['select'])) {
                $params['select'] .= ",\n\t\tmpdo.`myparcel_delivery_option`, IFNULL(mpdo.`date_delivery`, '1970-01-01 00:00:00') as `myparcel_date_delivery`, mpdo.`pickup` AS `myparcel_pickup`, UPPER(country.`iso_code`) AS `myparcel_country_iso`, 1 as `myparcel_void_1`, 1 as `myparcel_void_2`";
                if (!isset($params['join'])) {
                    $params['join'] = '';
                }
                $params['join'] .= "\n\t\tLEFT JOIN `"._DB_PREFIX_.bqSQL(MyParcelDeliveryOption::$definition['table'])."` mpdo ON (mpdo.`id_cart` = a.`id_cart`)";
                if (Configuration::get(static::DEV_MODE_SEARCH_TRACKTRACE)) {
                    $params['join'] .= "\n\t\tLEFT JOIN `"._DB_PREFIX_.bqSQL(MyParcelOrder::$definition['table'])."` mpo ON (mpo.`id_order` = a.`id_order`)";
                    $params['group_by'] = 'GROUP BY a.`id_order`';
                }
            }
            if (isset($params['fields'])) {
                $supportedCarrierModules = array_filter(Hook::getHookModuleExecList(Tools::substr(lcfirst(__FUNCTION__), 4, Tools::strlen(__FUNCTION__))), function ($item) {
                    $module = Module::getInstanceByName($item['module']);
                    if (!Validate::isLoadedObject($module)) {
                        return false;
                    }

                    return in_array($item['module'], array('myparcel', 'myparcelbpost', 'postnl'))
                        && version_compare($module->version, '2.2.0', '>=');
                });
                $lastSupportedCarrierModule = end($supportedCarrierModules);
                reset($supportedCarrierModules); // Reset array pointer
                if (!empty($supportedCarrierModules) && $lastSupportedCarrierModule['module'] !== $this->name) {
                    return;
                }
                $carrierNames = array();
                foreach ($supportedCarrierModules as $supportedCarrierModule) {
                    $name = '';
                    switch ($supportedCarrierModule['module']) {
                        case 'myparcel':
                            $name = 'MyParcel';
                            break;
                        case 'myparcelbpost':
                            $name = 'bpost';
                            break;
                        case 'postnl':
                            $name = 'PostNL';
                            break;
                    }
                    if ($name) {
                        $carrierNames[$supportedCarrierModule['module']] = $name;
                    }
                }

                if (!Configuration::get(static::DEV_MODE_HIDE_PREFERRED)) {
                    $params['fields']['myparcel_date_delivery'] = array(
                        'title'           => $this->l('Preferred delivery date'),
                        'class'           => 'fixed-width-lg',
                        'callback'        => 'printOrderGridPreference',
                        'callback_object' => 'MyParcelTools',
                        'filter_key'      => 'mpdo!date_delivery',
                        'type'            => 'date',
                    );
                }
                $params['fields']['myparcel_void_1'] = array(
                    'title'           => implode(' / ', array_values($carrierNames)),
                    'class'           => 'fixed-width-lg',
                    'callback'        => 'printMyParcelTrackTrace',
                    'callback_object' => 'MyParcelTools',
                    'filter_key'      => Configuration::get(static::DEV_MODE_SEARCH_TRACKTRACE) ? 'mpo!tracktrace' : null,
                    'search'          => Configuration::get(static::DEV_MODE_SEARCH_TRACKTRACE) ? true: false,
                    'orderby'         => Configuration::get(static::DEV_MODE_SEARCH_TRACKTRACE) ? true: false,
                    'remove_onclick'  => true,
                );
                $params['fields']['myparcel_void_2'] = array(
                    'title'           => '',
                    'class'           => 'text-nowrap',
                    'callback'        => 'printMyParcelIcon',
                    'callback_object' => 'MyParcelTools',
                    'search'          => false,
                    'orderby'         => false,
                    'remove_onclick'  => true,
                );
            }
        }
    }

    /**
     * Admin logs display
     *
     * @param array $params
     *
     * @since 2.2.0
     */
    public function hookActionAdminLogsListingFieldsModifier($params)
    {
        if (isset($params['fields'])) {
            $params['fields']['message'] = array(
                'title'           => $this->l('Message'),
                'callback'        => 'printLogMessage',
                'callback_object' => 'MyParcelTools',
            );
        }
    }

    /**
     * Decode the base64 log messages on the AdminLogs page
     *
     * @param array $params
     *
     * @since 2.3.0
     */
    public function hookActionLogsGridPresenterModifier($params)
    {
        $all = $params['presented_grid']['data']['records']->all();
        foreach ($all as &$item) {
            if (base64_encode(base64_decode($item['message'])) !== $item['message']
                || mb_strlen($item['message']) < 8
            ) {
                $item['base64'] = false;
                continue;
            }
            $item['message'] = base64_decode($item['message']);
            $item['base64'] = true;
        }
        $params['presented_grid']['data']['records'] = new \PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection($all);
    }
    /**
     * Update the AdminLogs page listing
     *
     * @param array $params
     *
     * @since 2.3.0
     */
    public function hookActionLogsGridDefinitionModifier($params)
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition $def */
        $def = $params['definition'];
        $columns = $def->getColumns();
        $oldColumn = current(array_filter($columns->toArray(), function ($elem) {
            return $elem['id'] === 'message';
        }));
        $newColumn = new MyParcelDataColumn($oldColumn['id']);
        $newColumn->setName($oldColumn['name']);
        $newColumn->setOptions($oldColumn['options']);
        $columns->remove('message');
        $columns->addAfter('severity', $newColumn);
    }

    /**
     * Delete log files for the Customer
     *
     * @param array $email
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
     * @since 2.2.0
     */
    public function hookActionDeleteGDPRCustomer($email)
    {
        if (empty($email['id_customer'])) {
            $customer = new Customer();
            $customer->getByEmail($email['email']);
            if (!Validate::isLoadedObject($customer)) {
                return json_encode($this->l('MyParcel: Unable to delete customer using email.'));
            }
        } else {
            $customer = new Customer($email['id_customer']);
        }

        $success = true;

        $success &= Db::getInstance()->delete('log', '`object_type` = \'Customer\' AND `object_id` = '.(int) $customer->id);

        $sql = new DbQuery();
        $sql->select('mdo.`id_myparcel_delivery_option`');
        $sql->from(bqSQL(Cart::$definition['table']), 'ca');
        $sql->innerJoin(bqSQL(Customer::$definition['table']), 'cu', 'ca.`id_customer` = cu.`id_customer`');
        $sql->innerJoin(bqSQL(MyParcelDeliveryOption::$definition['table']), 'mdo', 'mdo.`id_cart` = ca.`id_cart`');
        $sql->where('cu.`email` = \''.pSQL($customer->email).'\'');

        $idOptions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (is_array($idOptions)) {
            $idOptions = array_column($idOptions, 'id_myparcel_delivery_option');
            foreach ($idOptions as $idOption) {
                $deliveryOption = new MyParcelDeliveryOption($idOption);
                $concept = mypa_dot(@json_decode($deliveryOption->myparcel_delivery_option, true));
                $concept->set('concept.recipient.person', '');
                $concept->set('concept.recipient.street', '');
                $concept->set('concept.recipient.street_additional_info', '');
                $concept->set('concept.recipient.number', '');
                $concept->set('concept.recipient.number_suffix', '');
                $concept->set('concept.recipient.postal_code', '');
                $concept->set('concept.recipient.city', '');
                $concept->set('concept.recipient.region', '');
                $concept->set('concept.recipient.phone', '');
                $concept->set('concept.recipient.email', '');
                $concept->set('extraOptions.gdpr', true);
                $concept->delete('pickup');
                $deliveryOption->myparcel_delivery_option = mypa_json_encode($concept);
                $deliveryOption->save();
            }
        }

        $sql = new DbQuery();
        $sql->select('`id_order`');
        $sql->from(bqSQL(Order::$definition['table']), 'o');
        $sql->innerJoin(bqSQL(Customer::$definition['table']), 'cu', 'o.`id_customer` = cu.`id_customer`');
        $sql->where('cu.`email` = \''.pSQL($customer->email).'\'');
        $idOrders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (is_array($idOrders)) {
            $idOrders = array_column($idOrders, 'id_order');
            if (!empty($idOrders)) {
                Db::getInstance()->update(
                    bqSQL(MyParcelOrder::$definition['table']),
                    array(
                        'shipment' => '',
                    ),
                    '`id_order` IN ('.implode(',', array_map('intval', $idOrders)).')'
                );
            }

        }

        if ($success) {
            return json_encode(true);
        }

        return json_encode($this->l('MyParcel: Unable to delete customer using email.'));
    }

    /**
     * @param array $email
     *
     * @return null|string|string[]
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    public function hookActionExportGDPRData($email)
    {
        if (empty($email['id_customer'])) {
            $customer = new Customer();
            $customer->getByEmail($email['email']);
            if (!Validate::isLoadedObject($customer)) {
                return mypa_json_encode($this->l('No information found for this customer'));
            }
        } else {
            $customer = new Customer($email['id_customer']);
        }
        /** @var Customer $customer */
        $orderSql = new DbQuery();
        $orderSql->select('o.`reference`, mdo.`date_delivery`, mdo.`pickup`, mdo.`myparcel_delivery_option`');
        $orderSql->from(bqSQL(Cart::$definition['table']), 'ca');
        $orderSql->innerJoin(bqSQL(MyParcelDeliveryOption::$definition['table']), 'mdo', 'mdo.`id_cart` = ca.`id_cart`');
        $orderSql->innerJoin(bqSQL(Order::$definition['table']), 'o', 'o.`id_cart` = ca.`id_cart`');
        $orderSql->where('o.`id_customer` = '.(int) $customer->id);
        $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($orderSql);
        if (!is_array($results) || empty($results)) {
            return mypa_json_encode($this->l('No information found for this customer'));
        }

        foreach ($results as &$result) {
            $deliveryOption = mypa_dot(@json_decode($result['myparcel_delivery_option'], true));
            $result = array(
                'order'                    => $result['reference'],
                'date_delivery'            => $result['date_delivery'],
                'pickup'                   => $result['pickup'],
                'recipient_only_preferred' => ($deliveryOption->get('extraOptions.recipientOnly') ? 'true' : 'false'),
                'signature_preferred'      => ($deliveryOption->get('extraOptions.signed') ? 'true' : 'false'),
            );
        }

        return json_encode($results);
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
     * Get columns to display on the back office ordergrid
     *
     * @return array
     *
     * @since 2.2.0
     */
    public function getColumns()
    {
        return array(
            'delivery_date' => array('MyParcelTools', 'printOrderGridPreference'),
            'status'        => array('MyParcelTools', 'printMyParcelTrackTrace'),
            'concept'       => array('MyParcelTools', 'printMyParcelIcon'),
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
        $deliveryOption = mypa_dot(@json_decode(mypa_json_encode(MyParcelDeliveryOption::getRawByCartId($cart->id, false)), true));
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

        if ($deliverySetting->mailbox_package || $deliverySetting->digital_stamp) {
            // Disable if not delivering to the Netherlands
            if ($countryIso !== 'NL' || MyParcelProductSetting::cartHasCooledDelivery($cart) || MyParcelProductSetting::cartHasAgeCheck($cart)) {
                return false;
            }

            $amountOfBoxes = (int) $this->howManyMailboxPackages($cart->getProducts());
            if ($amountOfBoxes < 1) {
                return false;
            }
        }

        if (MyParcelProductSetting::cartHasCooledDelivery($cart)
            && ((!$deliverySetting->morning && !$deliverySetting->evening)
                || $countryIso !== 'NL'
                || ($deliverySetting->pickup && !$deliverySetting->delivery)
            )
        ) {
            return false;
        }

        $extraCosts = 0;
        // Just a check to see if we actually have a delivery option available
        if (!$deliveryOption->isEmpty('extraOptions')) {
            $selectedOptions = mypa_dot($deliveryOption->get('extraOptions'));
            if (in_array($countryIso, array('NL', 'BE'))) {
                if (in_array($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE), array(1, 2, 3))
                  && !MyParcelProductSetting::cartHasAgeCheck($cart)
                ) {
                    if ($selectedOptions->get('signature')
                        && $selectedOptions->get('onlyRecipient')
                        && !in_array($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE), array(1, 3))) {
                        $extraCosts += (float) $deliverySetting->signed_recipient_only_fee_tax_incl;
                    } elseif (in_array($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE), array(1, 3))) {
                        if ($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE) == 1) {
                            $extraCosts += (float) $deliverySetting->morning_fee_tax_incl;
                        } else {
                            $extraCosts += (float) $deliverySetting->evening_fee_tax_incl;
                        }

                        if ($selectedOptions->get('signature')) {
                            $extraCosts += (float) $deliverySetting->signed_fee_tax_incl;
                        }
                    } else {
                        if ($selectedOptions->get('signature')) {
                            $extraCosts += (float) $deliverySetting->signed_fee_tax_incl;
                        }
                        if ($selectedOptions->get('onlyRecipient')) {
                            $extraCosts += (float) $deliverySetting->recipient_only_fee_tax_incl;
                        }
                    }
                } elseif ($deliveryOption->get('data.time.0.type', MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::DEFAULT_DELIVERY_TYPE) == 5) {
                    $extraCosts = (float) $deliverySetting->morning_pickup_fee_tax_incl;
                }
            }
        }
        // Calculate the conversion to make before displaying prices
        // It is comprised of taxes and currency conversions
        /** @var Currency $defaultCurrency */
        $defaultCurrency = Currency::getCurrencyInstance(Configuration::get('PS_CURRENCY_DEFAULT'));
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

        return ($extraCosts * $conversion * $taxRate) + $shippingCost;
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
     * @throws PrestaShopException
     */
    protected function howManyMailboxPackages($products, $multiple = false)
    {
        // Init calculator
        $packer = new \MyParcelModule\BoxPacker\Packer();
        $weightUnit = static::getWeightUnit();
        if ($weightUnit === 'kg') {
            $maxWeight = 2;
        } elseif ($weightUnit === 'g') {
            $maxWeight = 2000;
        } else {
            return 0;
        }

        $dimensionUnit = static::getDimensionUnit();
        if ($dimensionUnit === 'mm') {
            $maxWidth = 380;
            $maxHeight = 32;
            $maxDepth = 265;
        } elseif ($dimensionUnit === 'cm') {
            $maxWidth = 38;
            $maxHeight = 3.2;
            $maxDepth = 26.5;
        } elseif ($dimensionUnit === 'm') {
            $maxWidth = 0.38;
            $maxHeight = 0.32;
            $maxDepth = 0.265;
        } else {
            return 0;
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
                new MyParcelMailboxPackageItem(
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

        if (Configuration::get('PS_TAX_ADDRESS_TYPE') === 'id_address_invoice') {
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
                && isset($cart->id_address_delivery)
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
            $address = Address::initialize((int) $addressId);

            if (Configuration::get('PS_ATCP_SHIPWRAP')) {
                $carrierTax = 0;
            } else {
                $carrierTax = $carrier->getTaxesRate($address);
            }
        }

        $configuration = array(
            'PS_SHIPPING_FREE_PRICE'  => Configuration::get('PS_SHIPPING_FREE_PRICE'),
            'PS_SHIPPING_HANDLING'    => Configuration::get('PS_SHIPPING_HANDLING'),
            'PS_SHIPPING_METHOD'      => Configuration::get('PS_SHIPPING_METHOD'),
            'PS_SHIPPING_FREE_WEIGHT' => Configuration::get('PS_SHIPPING_FREE_WEIGHT'),
        );

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
                } else { // by pricing
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

        if (isset($mdo['type']) && $mdo['type'] === 'timeframe') {
            if (isset($mdo['data']['time']['price_comment'])) {
                switch ($mdo['data']['time']['price_comment']) {
                    case 'morning':
                        return 4;
                    case 'night':
                    case 'avond':
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
     * @throws PrestaShopDatabaseException
     * @since 2.0.0
     */
    protected function getCarriersByReferences($references)
    {
        if (empty($references) || !is_array($references)) {
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
        $carriersDb = Db::getInstance()->executeS($sql);

        $carrierIds = array();
        foreach ($carriersDb as $carrier) {
            $carrierIds[] = (int) $carrier['id_carrier'];
        }

        return $carrierIds;
    }

    /**
     * Get paid order statuses
     *
     * @return int[]
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getIgnoredStatuses()
    {
        $statuses = Configuration::get(static::IGNORE_ORDER_STATUSES);
        if ($statuses === false) {
            return array();
        } else {
            $statuses = @json_decode($statuses);
            if (!is_array($statuses)) {
                return array();
            }
        }

        return array_map('intval', $statuses);
    }

    /**
     * Get all status values from the form.
     *
     * @param $key string The key that is used in the HelperForm
     *
     * @return array Array with statuses
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getStatusesValue($key)
    {
        $statesEnabled = array();
        foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
            if (Tools::isSubmit($key.'_'.$state['id_order_state'])) {
                $statesEnabled[] = $state['id_order_state'];
            }
        }

        return $statesEnabled;
    }

    /**
     * Detect whether the order has a shipping number.
     *
     * @param $order Order The order to check
     *
     * @return bool True if the order has a shipping number
     *
     * @since 2.0.0
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    protected function orderHasShippingNumber($order)
    {
        if (isset($order->shipping_number) && $order->shipping_number) {
            return true;
        }
        $orderCarrier = new OrderCarrier((int) Db::getInstance()->getValue('
				SELECT `id_order_carrier`
				FROM `'._DB_PREFIX_.'order_carrier`
				WHERE `id_order` = '.(int) $order->id));
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
        $sql->where('`'.bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']).'` = '.(int) $idMyParcelCarrierDeliverySetting);

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
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
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    protected function addGroups($carrier)
    {
        $groupsIds = array();
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groupsIds[] = $group['id_group'];
        }

        if (!is_array($groups) || !count($groups)) {
            return true;
        }

        return Db::getInstance()->insert(
            'carrier_group',
            array_map(function ($idGroup) use ($carrier) {
                return array(
                    'id_carrier' => (int) $carrier->id,
                    'id_group'   => (int) $idGroup,
                );
            }, $groupsIds)
        );
    }

    /**
     * @param Carrier $carrier
     *
     * @return RangePrice
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Adapter_Exception
     *
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
     *
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
     *
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
     *
     * @since 2.2.0
     */
    protected function getModuleVersion($moduleCode)
    {
        $sql = new DbQuery();
        $sql->select('`version`');
        $sql->from('module');
        $sql->where('`name` = \''.pSQL($moduleCode).'\'');

        return (string) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * @return bool
     *
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    protected function shouldShowTour()
    {
        $tourStep = (int) Configuration::get(static::TOUR_CURRENT_STEP, null, 0, 0);
        if ($tourStep >= 99) {
            return false;
        }
        if (version_compare(_PS_VERSION_, '1.6.1.0', '<')
            || version_compare(_PS_VERSION_, '1.7.6.0', '>=')
        ) {
            // Older PrestaShop configuration forms are too borked to guide users properly
            Configuration::updateValue(static::TOUR_CURRENT_STEP, 99, false, 0, 0);
            return false;
        }

        if (empty($this->context->employee)) {
            return false;
        }

        if (!in_array(
            Tools::getValue('controller'),
            array(
                'AdminModules',
                'AdminOrders',
                'AdminCarrierWizard',
                'AdminCarriers',
            )
        )) {
            return false;
        }

        if (Tools::getValue('controller') === 'AdminModules' && Tools::getValue('configure') !== $this->name) {
            // Only show for the MyParcel module
            return false;
        }

        return true;
    }

    /**
     * Should the resume tour message be shown?
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.2.0
     */
    protected function shouldResumeTour()
    {
        $tourStep = (int) Configuration::get(static::TOUR_CURRENT_STEP, false, 0, 0);
        switch ($tourStep) {
            case static::TOUR_STEP_MAIN:
            case static::TOUR_STEP_DESIGN:
                $canContinue = $this->context->controller instanceof AdminModulesController
                    && Tools::getValue('configure') === $this->name
                    && (int) Tools::getValue('menu') === static::MENU_MAIN_SETTINGS;
                break;
            case static::TOUR_STEP_DELIVERY_OPTIONS:
                $canContinue = $this->context->controller instanceof AdminModulesController
                    && Tools::getValue('configure') === $this->name
                    && (int) Tools::getValue('menu') === static::MENU_DEFAULT_DELIVERY_OPTIONS
                    && Tools::isSubmit('update'.MyParcelCarrierDeliverySetting::$definition['table']);
                break;
            case static::TOUR_STEP_LABELS_NOTIFICATIONS:
                $canContinue = $this->context->controller instanceof AdminModulesController
                    && Tools::getValue('configure') === $this->name
                    && (int) Tools::getValue('menu') === static::MENU_MAIN_SETTINGS;
                break;
            case static::TOUR_STEP_CARRIER_CONFIG:
                $canContinue = $this->context->controller instanceof AdminCarrierWizardController
                    && (int) Tools::getValue('id_carrier') === (int) Carrier::getCarrierByReference(Configuration::get(static::POSTNL_DEFAULT_CARRIER))->id;
                break;
            case static::TOUR_STEP_START_SHIPPING:
                $canContinue = $this->context->controller instanceof AdminOrdersController
                    && !Tools::isSubmit('id_order');
                break;
            case 0:
                $canContinue = true;
                break;
            case 99:
            default:
                $canContinue = false;
                break;
        }

        return !$canContinue;
    }

    /**
     * Get the webpack chunks for a given entry name
     *
     * @param string $entry Entry name
     *
     * @return array Array with chunk files, should be loaded in the given order
     *
     * @since 2.3.0
     */
    public static function getWebpackChunks($entry)
    {
        static $manifest = null;
        if (!$manifest) {
            $manifest = array();
            foreach (include(_PS_MODULE_DIR_.'myparcel/views/js/dist/manifest.php') as $chunk) {
                $manifest[$chunk['name']] = array_map(function ($chunk) {
                    return MyParcel::getMediaPath(_PS_MODULE_DIR_."myparcel/views/js/dist/{$chunk}");
                }, $chunk['files']);
            }
        }

        return isset($manifest[$entry]) ? $manifest[$entry] : array();
    }

    /**
     * Get the webpack `publicPath` variable
     *
     * @return mixed
     *
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getWebpackPublicPath()
    {
        $module = new static();
        return $module->webpackPublicPath();
    }

    /**
     * Get the webpack `publicPath` variable
     *
     * @return string
     *
     * @since 2.3.0
     */
    public function webpackPublicPath()
    {
        return $this->_path.'views/js/dist/';
    }

    /**
     * @param string      $mediaUri
     * @param string|null $cssMediaType
     *
     * @return array|bool|mixed|string
     *
     * @since   2.3.0
     */
    public static function getMediaPath($mediaUri, $cssMediaType = null)
    {
        if (is_array($mediaUri) || $mediaUri === null || empty($mediaUri)) {
            return false;
        }

        $urlData = parse_url($mediaUri);
        if (!is_array($urlData)) {
            return false;
        }

        if (!array_key_exists('host', $urlData)) {
            $mediaUri = '/'.ltrim(str_replace(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, _PS_ROOT_DIR_), __PS_BASE_URI__, $mediaUri), '/\\');
            // remove PS_BASE_URI on _PS_ROOT_DIR_ for the following
            $fileUri = _PS_ROOT_DIR_.Tools::str_replace_once(__PS_BASE_URI__, DIRECTORY_SEPARATOR, $mediaUri);

            if (!@filemtime($fileUri) || @filesize($fileUri) === 0) {
                return false;
            }

            $mediaUri = str_replace('//', '/', $mediaUri);
        }

        if ($cssMediaType) {
            return array($mediaUri => $cssMediaType);
        }

        return $mediaUri;
    }

    /**
     * Get the frontend number format
     *
     * @param null|Currency $currency
     *
     * @return string
     *
     * @since 2.3.0
     */
    public static function getNumberFormat($currency = null)
    {
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $locale = Context::getContext()->language->language_code;
            $locale = Tools::strtoupper(Tools::substr($locale, 0, 2)).'-'.Tools::strtolower(Tools::substr($locale, 2, 2));
            if (file_exists(_PS_ROOT_DIR_."/translations/cldr/datas/main/{$locale}/numbers.json")) {
                $cldr = @json_decode(_PS_ROOT_DIR_."/translations/cldr/datas/main/{$locale}/numbers.json");
                if (isset($cldr['main'][$locale]['numbers']['decimalFormats-numberSystem-latn']['standard'])
                  && isset($cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['standard'])
                ) {
                    $format = $cldr['main'][$locale]['numbers']['decimalFormats-numberSystem-latn']['standard'];
                    $format = str_replace('.', $cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['decimal'], $format);
                    $format = str_replace(',', $cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['group'], $format);
                    return $format;
                }
            }
        } else {
            if (!$currency instanceof Currency) {
                $currency = Context::getContext()->currency;
            }
            switch ((int) $currency->format) {
                case 1:
                    return '#.##0'.$currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '';
                case 2:
                    return '# ##0'.$currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '';
                case 3:
                    return '#.##0'.$currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '';
                case 4:
                    return '#,##0'.$currency->decimals >= 0 ? '.'.str_repeat('#', $currency->decimals) : '';
                case 5:
                    return '#\'##0'.$currency->decimals >= 0 ? '.'.str_repeat('#', $currency->decimals) : '';
            }
        }

        return '#,##0.###';
    }

    /**
     * Get the frontend currency format
     *
     * @param null|Currency $currency
     *
     * @return string
     *
     * @since 2.3.0
     */
    public static function getCurrencyFormat($currency = null)
    {
        if (!$currency instanceof Currency) {
            $currency = Context::getContext()->currency;
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            $locale = Context::getContext()->language->language_code;
            $locale = Tools::strtoupper(Tools::substr($locale, 0, 2)).'-'.Tools::strtolower(Tools::substr($locale, 2, 2));
            if (file_exists(_PS_ROOT_DIR_."/translations/cldr/datas/main/{$locale}/numbers.json")) {
                $cldr = @json_decode(_PS_ROOT_DIR_."/translations/cldr/datas/main/{$locale}/numbers.json");
                if (isset($cldr['main'][$locale]['numbers']['currencyFormats-numberSystem-latn']['standard'])
                    && isset($cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['standard'])
                ) {
                    $format = $cldr['main'][$locale]['numbers']['currencyFormats-numberSystem-latn']['standard'];
                    $split = explode(';', $format);
                    $format = $split[0];
                    $format = str_replace('.', $cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['decimal'], $format);
                    $format = str_replace(',', $cldr['main'][$locale]['numbers']['symbols-numberSystem-latn']['group'], $format);
                    $format = str_replace('¤', $currency->sign, $format);

                    return $format;
                }
            }
        } else {
            if (!$currency instanceof Currency) {
                $currency = Context::getContext()->currency;
            }

            switch ((int) $currency->format) {
                case 1:
                    return $currency->sign.($currency->blank ? ' ' : '').'#.##0'.($currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '');
                case 2:
                    return '# ##0'.($currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '').($currency->blank ? ' ' : '').$currency->sign;
                case 3:
                    return $currency->sign.($currency->blank ? ' ' : '').'#.##0'.($currency->decimals >= 0 ? ','.str_repeat('#', $currency->decimals) : '');
                case 4:
                    return '#,##0'.($currency->decimals >= 0 ? '.'.str_repeat('#', $currency->decimals) : '').($currency->blank ? ' ' : '').$currency->sign;
                case 5:
                    return '#\'##0'.($currency->decimals >= 0 ? '.'.str_repeat('#', $currency->decimals) : '').($currency->blank ? ' ' : '').$currency->sign;
            }
        }

        return $currency->sign.' #.##0,00';
    }

    /**
     * Finds a valid insurance (ceiled) amount
     *
     * @param int $amount
     *
     * @return int
     *
     * @since 2.3.0
     */
    public static function findValidInsuranceAmount($amount)
    {
        $possibleValues = \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelConsignment::getInsurancePossibilities();
        for ($i = 0; $i < count($possibleValues) - 1; $i++) {
            if ($amount > $possibleValues[$i] && $amount < $possibleValues[$i + 1]) {
                $amount = $possibleValues[$i + 1];
                break;
            }
        }
        if ($amount > $possibleValues[count($possibleValues) - 1]) {
            $amount = $possibleValues[count($possibleValues) - 1];
        }

        return $amount;
    }

    /**
     * Get a list of all store countries (incl. disabled)
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getCountries()
    {
        $countries = array();
        $supportedCountries = MyParcelTools::getSupportedCountries();
        if (isset($supportedCountries['data']['countries'][0])) {
            $euCountries = array_map(function ($item) {
                $values = array_values($item);

                return Tools::strtoupper($values[0]);
            }, MyParcelTools::getEUCountries());
            foreach (array_keys($supportedCountries['data']['countries'][0]) as $iso) {
                if (Tools::strtoupper($iso) === 'NL') {
                    continue;
                }

                if (!in_array(Tools::strtoupper($iso), $euCountries)) {
                    $supportedCountries['data']['countries'][0][$iso]['region'] = 'CD';
                } else {
                    $supportedCountries['data']['countries'][0][$iso]['region'] = 'EU';
                }
            }
            $countryIsos = array_keys($supportedCountries['data']['countries'][0]);
            foreach (Country::getCountries(Context::getContext()->language->id) as $country) {
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
        if (isset($countries['GB'])) {
            $gbAlternative = $countries['GB'];
            $gbAlternative['iso_code'] = 'UK';
            $countries ['UK'] = $gbAlternative;
        }

        return $countries;
    }

    /**
     * Get a list of all countries that support a large format
     *
     * @return array
     *
     * @since 2.3.0
     */
    public static function getLargeFormatCountries()
    {
        return array('NL', 'DE', 'BE', 'UK', 'GB', 'FR', 'ES');
    }

    /**
     * Detect weight unit
     *
     * @return false|string
     *
     * @since 2.3.0
     */
    public static function detectWeightUnit()
    {
        $weightUnit = Tools::strtolower(Configuration::get('PS_WEIGHT_UNIT'));
        if (in_array($weightUnit, array('kg', 'kilo', 'kilogram'))) {
            return 'kg';
        } elseif (in_array($weightUnit, array('g', 'gram', 'grams', 'gramme'))) {
            return 'g';
        }

        return false;
    }

    /**
     * Get weight unit
     *
     * @return false|string
     *
     * @since 2.3.0
     */
    public static function getWeightUnit()
    {
        $weightUnit = static::detectWeightUnit();
        if (!in_array($weightUnit, array('kg', 'g'))) {
            $weightUnit = Configuration::get(static::WEIGHT_UNIT);
            if (is_string($weightUnit)) {
                return $weightUnit;
            }

            return false;
        }

        return $weightUnit;
    }

    /**
     * Detect dimension unit
     *
     * @return false|string
     *
     * @since 2.3.0
     */
    public static function detectDimensionUnit()
    {
        $dimensionUnit = Tools::strtolower(Configuration::get('PS_DIMENSION_UNIT'));
        if (in_array($dimensionUnit, array('mm', 'milli', 'millimeter', 'millimeters', 'millimetre', 'millimetres'))) {
            return 'mm';
        } elseif (in_array($dimensionUnit, array('cm', 'centi', 'centimeter', 'centimeters', 'centimetre', 'centimetres'))) {
            return 'cm';
        } elseif (in_array($dimensionUnit, array('m', 'meter', 'metre', 'meters', 'metres'))) {
            return 'm';
        }

        return false;
    }

    /**
     * Get dimension unit
     *
     * @return false|string
     *
     * @since 2.3.0
     */
    public static function getDimensionUnit()
    {
        $dimensionUnit = static::detectDimensionUnit();
        if (!in_array($dimensionUnit, array('mm', 'cm', 'm'))) {
            $dimensionUnit = Configuration::get(static::DIMENSION_UNIT);
            if (is_string($dimensionUnit)) {
                return $dimensionUnit;
            }

            return false;
        }

        return $dimensionUnit;
    }

    /**
     * Append query array to url string
     *
     * @param string $urlString
     * @param array  $query
     *
     * @return string
     *
     * @since 2.3.0
     */
    public static function appendQueryToUrl($urlString, $query = array())
    {
        $url = mypa_parse_url($urlString);
        $url['query'] = isset($url['query']) ? $url['query'] : '';
        parse_str($url['query'], $oldQuery);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $url['query'] = http_build_query($oldQuery + $query, PHP_QUERY_RFC1738);
        } else {
            $url['query'] = http_build_query($oldQuery + $query);
        }


        return mypa_stringify_url($url);
    }

    /**
     * Get admin link (PS 1.5/1.6 + 1.7 hybrid)
     *
     * @param string $controller
     * @param bool   $withToken
     * @param array  $params
     *
     * @return string
     *
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public function getAdminLink($controller, $withToken = true, $params = array())
    {
        $url = mypa_parse_url($this->context->link->getAdminLink($controller, $withToken));
        $url['query'] = isset($url['query']) ? $url['query'] : '';
        parse_str($url['query'], $query);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $url['query'] = http_build_query($query + $params, PHP_QUERY_RFC1738);
        } else {
            $url['query'] = http_build_query($query + $params);
        }


        return mypa_stringify_url($url);
    }

    /**
     * Get the shop identifier to use in consignment reference IDs
     *
     * @return bool|false|mixed|string|string[]|null
     *
     * @since 2.3.0
     */
    public static function getShopIdentifier()
    {
        return Tools::strtoupper(Tools::substr(Tools::encrypt('MYPARCELNL'), 0, 8));
    }

    /**
     * Ajax process download module update
     *
     * @throws ErrorException
     * @throws PrestaShopException
     * @throws Exception
     *
     * @since 2.3.0
     */
    public function ajaxProcessDownloadUpdate()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $latestVersion = static::checkForUpdates();
            if ($latestVersion === false) {
                die(json_encode(array(
                    'success' => false,
                    'message' => $this->l('Unable to retrieve info about the latest version'),
                )));
            }
        } catch (PrestaShopException $e) {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('Unable to retrieve info about the latest version'),
            )));
        }
        if (version_compare($latestVersion, $this->version, '>')) {
            // Then update
            $success = $this->downloadModuleFromLocation($this->name, "https://github.com/myparcelnl/prestashop/releases/download/v{$latestVersion}/myparcel-v{$latestVersion}.zip");
            $message = isset($this->context->controller->errors[0]) ? $this->context->controller->errors[0] : '';
            die(json_encode(array(
                'success' => $success,
                'message' => $message,
            )));
        } else {
            die(json_encode(array(
                'success' => false,
                'message' => $this->l('You are already running the latest version!'),
            )));
        }
    }

    /**
     * Ajax process install module update
     *
     * @since 2.3.0
     */
    public function ajaxProcessInstallUpdate()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->unzipModule();
        } catch (Adapter_Exception $e) {
            $result = false;
        } catch (PrestaShopDatabaseException $e) {
            $result = false;
        } catch (PrestaShopException $e) {
            $result = false;
        }

        die(json_encode(array(
            'success' => $result,
            'message' => isset($this->context->controller->errors[0]) ? $this->context->controller->errors[0] : '',
        )));
    }

    /**
     * Ajax process run module upgrade
     *
     * @since 2.3.0
     */
    public function ajaxProcessRunUpgrade()
    {
        header('Content-Type: application/json;charset=UTF-8');
        try {
            $result = $this->runUpgradeModule();
        } catch (PrestaShopDatabaseException $e) {
            $error = $e->getMessage();
            $result = false;
        } catch (PrestaShopException $e) {
            $error = $e->getMessage();
            $result = false;
        }
        if (method_exists('Module', 'upgradeModuleVersion')) {
            Module::upgradeModuleVersion($this->name, $this->version);
        }

        die(json_encode(array(
            'success' => $result,
            'message' => isset($error) ? $error : '',
        )));
    }

    /**
     * Download the latest module from the given location
     *
     * @param string $moduleName
     * @param string $location
     *
     * @return bool
     *
     * @throws ErrorException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected function downloadModuleFromLocation($moduleName, $location)
    {
        $zipLocation = _PS_MODULE_DIR_.$moduleName.'.zip';
        if (@!file_exists($zipLocation)) {
            $curl = new \MyParcelModule\MyParcelHttpClient();
            $curl->setOpt(CURLOPT_ENCODING, '');
            $curl->setOpt(CURLOPT_FOLLOWLOCATION, 1);
            if (!$curl->download($location, _PS_MODULE_DIR_.'myparcel-update.zip')) {
                if (!empty($curl->errorMessage)) {
                    @$errorMessage = (string) $curl->errorMessage;
                    $this->context->controller->errors[] = "Download error: $errorMessage";
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Unzip the module
     *
     * @return bool Whether the module has been successfully extracted
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected function unzipModule()
    {
        if (@file_exists(_PS_MODULE_DIR_.'myparcel-update.zip')) {
            return $this->extractModuleArchive($this->name, _PS_MODULE_DIR_.'myparcel-update.zip');
        }

        return false;
    }

    /**
     * Extracts a module archive to the `modules` folder
     *
     * @param string $moduleName Module name
     * @param string $file       File source location
     *
     * @return bool
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected function extractModuleArchive($moduleName, $file)
    {
        $zipFolders = array();
        $tmpFolder = _PS_MODULE_DIR_.$moduleName.md5(time());

        if (@!file_exists($file)) {
            $this->context->controller->errors[] = $this->l('Module archive could not be downloaded');

            return false;
        }

        $success = false;
        if (Tools::substr($file, -4) === '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder.DIRECTORY_SEPARATOR.$moduleName)) {
                if (file_exists(_PS_MODULE_DIR_.$moduleName)) {
                    $report = '';
                    if (!static::testDir(_PS_MODULE_DIR_.$moduleName, true, $report, true)) {
                        $this->context->controller->errors[] = $report;
                        $this->recursiveDeleteOnDisk($tmpFolder);
                        @unlink(_PS_MODULE_DIR_.$moduleName.'.zip');

                        return false;
                    }
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$moduleName);
                }
                if (@rename($tmpFolder.DIRECTORY_SEPARATOR.$moduleName, _PS_MODULE_DIR_.$moduleName)) {
                    $success = true;
                }
            }
        }

        if (!$success) {
            $this->context->controller->errors[] =
                $this->l('There was an error while extracting the module file (file may be corrupted).');
            // Force a new check
        } else {
            //check if it's a real module
            foreach ($zipFolders as $folder) {
                if (!in_array($folder, array('.', '..', '.svn', '.git', '__MACOSX')) && !Module::getInstanceByName($folder)) {
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$folder);
                }
            }
        }

        @unlink($file);
        @unlink(_PS_MODULE_DIR_.$moduleName.'backup');
        $this->recursiveDeleteOnDisk($tmpFolder);

        die(json_encode(array(
            'success' => $success,
        )));
    }

    /**
     * Test if directory is writable
     *
     * @param string $dir      Directory path, absolute or relative
     * @param bool   $recursive
     * @param null   $fullReport
     * @param bool   $absolute Is absolute path to directory
     *
     * @return bool
     *
     * @since 2.3.0
     */
    public static function testDir($dir, $recursive = false, &$fullReport = null, $absolute = false)
    {
        if ($absolute) {
            $absoluteDir = $dir;
        } else {
            $absoluteDir = rtrim(_PS_ROOT_DIR_, '\\/').DIRECTORY_SEPARATOR.trim($dir, '\\/');
        }

        if (!file_exists($absoluteDir)) {
            $fullReport = sprintf('Directory %s does not exist.', $absoluteDir);

            return false;
        }

        if (!is_writable($absoluteDir)) {
            $fullReport = sprintf('Directory %s is not writable.', $absoluteDir);

            return false;
        }

        if ($recursive) {
            foreach (scandir($absoluteDir, SCANDIR_SORT_NONE) as $item) {
                $path = $absoluteDir.DIRECTORY_SEPARATOR.$item;

                if (in_array($item, array('.', '..', '.git'))
                    || is_link($path)) {
                    continue;
                }

                if (is_dir($path)) {
                    if (!static::testDir($path, $recursive, $fullReport, true)) {
                        return false;
                    }
                }

                if (!is_writable($path)) {
                    $fullReport = sprintf('File %s is not writable.', $path);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $url
     *
     * @return string|false
     *
     * @throws Exception
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    protected static function checkForUpdates()
    {
        $curl = new \MyParcelModule\MyParcelHttpClient();
        $updateXml = $curl->get('https://github.com/myparcelnl/prestashop/releases.atom');
        if ($updateXml instanceof SimpleXMLElement) {
            try {
                /** @var SimpleXMLElement $updateXml */
                if (!empty($updateXml) && isset($updateXml->entry, $updateXml->entry[0], $updateXml->entry[0]->id)) {
                    $title = $updateXml->entry[0]->id;
                    $latestVersion = preg_replace("/[^0-9,.]/", '', Tools::substr($title, strrpos($title, '/')));
                    Configuration::updateValue(static::NEW_VERSION_AVAILABLE, $latestVersion);
                    return $latestVersion;
                }
            } catch (Exception $e) {
                Logger::addLog('MyParcel warning: Update xml file from github follows an unexpected format.');
            }
        }

        return false;
    }

    /**
     * Check if template compilation has been set to "never recompile".
     * This is known to cause issues.
     *
     * @return bool
     *
     * @since 2.3.0
     */
    protected function checkTemplateCompilation()
    {
        return !Configuration::get('PS_SMARTY_FORCE_COMPILE');
    }

    /**
     * Check if the Smarty cache has been enabled and revalidates.
     * If it does not, there's a chance it will serve a stale payment method list.
     *
     * @return bool
     *
     * @since 2.3.0
     */
    protected function checkStaleSmartyCache()
    {
        return Configuration::get('PS_SMARTY_CACHE') && Configuration::get('PS_SMARTY_CLEAR_CACHE') === 'never';
    }

    /**
     * Get page location
     *
     * @param string   $class
     * @param int|null $idLang
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getMenuLocation($class, $idLang = null)
    {
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        return implode(' > ', array_reverse(array_unique(array_map(function ($tab) use ($idLang) {
            return $tab->name[$idLang];
        }, static::getTabTreeByClass($class)))));
    }

    /**
     * Get the entire tab tree by tab class name
     *
     * @param string $class
     *
     * @return Tab[]|null
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getTabTreeByClass($class)
    {
        $tabs = array();
        $depth = 10;
        $tab = Tab::getInstanceFromClassName($class);
        while (Validate::isLoadedObject($tab) && $depth > 0) {
            $depth--;
            $tabs[] = $tab;
            $tab = new Tab($tab->id_parent);
        }

        return $tabs;
    }

    /**
     * Get tab name by tab class
     *
     * @param string   $class
     * @param int|null $idLang
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public static function getTabNameByClass($class, $idLang = null)
    {
        $tab = Tab::getInstanceFromClassName($class);
        if (!$tab instanceof Tab) {
            throw new InvalidArgumentException('Tab not found');
        }

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        return $tab->name[$idLang];
    }

    /**
     * Get paper format
     *
     * @return array
     *
     * @since 2.3.0
     */
    public static function getPaperFormat()
    {
        $paperFormat = @json_decode(Configuration::get(static::PAPER_SELECTION), true);

        return array(
            'size'   => isset($paperFormat['size']) && in_array($paperFormat['size'], array('standard', 'label')) ? $paperFormat['size'] : 'standard',
            'labels' => array(
                1 => !empty($paperFormat['labels'][1]),
                2 => !empty($paperFormat['labels'][2]),
                3 => !empty($paperFormat['labels'][3]),
                4 => !empty($paperFormat['labels'][4]),
            ),
        );
    }

    /**
     * @param $params
     *
     * @return void
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.3.0
     */
    public function hookActionProductSave($params)
    {
        $idProduct = (int) $params['id_product'];
        if (!$idProduct || !Tools::isSubmit("{$this->name}-{$idProduct}-item-status")) {
            return;
        }

        MyParcelProductSetting::saveSingle(
            $idProduct,
            Tools::getValue("{$this->name}-{$idProduct}-item-classification"),
            Tools::getValue("{$this->name}-{$idProduct}-item-country"),
            Tools::getValue("{$this->name}-{$idProduct}-item-status"),
            (bool) Tools::isSubmit("{$this->name}-{$idProduct}-item-age-check"),
            (bool) Tools::isSubmit("{$this->name}-{$idProduct}-item-cooled-delivery")
        );
    }

    /**
     * Get the PS theme version
     *
     * @param string $target
     *
     * @return string
     *
     * @since 2.3.0
     */
    public static function getThemeVersion($target = '')
    {
        if (version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            return 'ps15';
        } elseif (version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            return 'ps16';
        } elseif (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
            return 'ps161';
        } elseif (version_compare(_PS_VERSION_, '1.7.1.0', '<')) {
            return $target === 'product' ? 'ps170' : 'ps161';
        } elseif (version_compare(_PS_VERSION_, '1.7.2.0', '<')) {
            return $target === 'product' ? 'ps171' : 'ps161';
        } elseif (version_compare(_PS_VERSION_, '1.7.3.0', '<')) {
            return $target === 'product' ? 'ps172' : 'ps161';
        } elseif (version_compare(_PS_VERSION_, '1.7.4.0', '<')) {
            return $target === 'product' ? 'ps173' : 'ps161';
        }

        return $target === 'product' ? 'ps174' : 'ps161';
    }

    /**
     * @param string $cc
     * @param string $postcode
     * @param string $number
     *
     * @return array
     *
     * @throws ErrorException
     * @throws PrestaShopException
     *
     * @since 2.3.3
     */
    public static function getDeliveryOptions($cc, $postcode, $number)
    {
        $query = array(
            'carrier'         => 1,
            'cutoff_time'     => '23:59:00',
            'monday_delivery' => 1,
            'dropoff_days'    => '0;1;2;3;4;5;6',
            'cc'              => $cc,
            'postal_code'     => $postcode,
            'number'          => $number,
        );

        $curl = new \MyParcelModule\MyParcelHttpClient();
        $url = 'https://api.myparcel.nl/delivery_options?'.http_build_query($query);
        return $curl->get($url);
    }
}

MyParcelModule\MyParcelNL\Sdk\src\Model\Repository\MyParcelConsignmentRepository::$euCountries = array_values(array_map(function ($country) { return $country['iso_code']; }, array_filter(MyParcel::getCountries(), function ($country) { return in_array($country['region'], array('NL', 'BE', 'EU')); })));

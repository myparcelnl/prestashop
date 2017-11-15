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

require_once dirname(__FILE__).'/classes/autoload.php';

/**
 * Class MyParcel
 *
 * @since 1.0.0
 */
class MyParcel extends Module
{
    const AUTO_UPDATE = 'MYPARCEL_AUTO_UPDATE';
    const MENU_MAIN_SETTINGS = 0;
    const MENU_DEFAULT_SETTINGS = 1;
    const MENU_DEFAULT_DELIVERY_OPTIONS = 2;
    const MENU_UPDATES = 3;
    const POSTNL_DEFAULT_CARRIER = 'MYPARCEL_DEFAULT_CARRIER';
    const POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER = 'MYPARCEL_DEFAULT_MAILPACK';
    const MYPARCEL_BASE_URL = 'https://www.myparcel.nl/';
    const SUPPORTED_COUNTRIES_URL = 'https://backoffice.myparcel.nl/api/system_country_codes';
    const LINK_EMAIL = 'MYPARCEL_LINK_EMAIL';
    const LINK_PHONE = 'MYPARCEL_LINK_PHONE';
    const LABEL_DESCRIPTION = 'MYPARCEL_LABEL_DESCRIPTION';
    const API_KEY = 'MYPARCEL_API_KEY';
    const CHECKOUT_LIVE = 'MYPARCEL_LIVE_CHECKOUT';
    const CHECKOUT_FG_COLOR1 = 'MYPARCEL_CHECKOUT_FG_COLOR1';
    const CHECKOUT_FG_COLOR2 = 'MYPARCEL_CHECKOUT_FG_COLOR2';
    const CHECKOUT_BG_COLOR1 = 'MYPARCEL_CHECKOUT_BG_COLOR1';
    const CHECKOUT_BG_COLOR2 = 'MYPARCEL_CHECKOUT_BG_COLOR2';
    const CHECKOUT_BG_COLOR3 = 'MYPARCEL_CHECKOUT_BG_COLOR3';
    const CHECKOUT_HL_COLOR = 'MYPARCEL_CHECKOUT_HL_COLOR';
    const CHECKOUT_FONT = 'MYPARCEL_CHECKOUT_FONT';
    const ENUM_NONE = 0;
    const ENUM_SAMEDAY = 1;
    const ENUM_DELIVERY = 2;
    const ENUM_DELIVERY_SELF_DELAY = 3;
    const DEFAULT_CONCEPT_PARCEL_TYPE = 'MYPARCEL_DEFCON_PT';
    const DEFAULT_CONCEPT_LARGE_PACKAGE = 'MYPARCEL_DEFCON_LP';
    const DEFAULT_CONCEPT_HOME_DELIVERY_ONLY = 'MYPARCEL_DEFCON_HDO';
    const DEFAULT_CONCEPT_HOME_DELIVERY_ONLY_SIGNED = 'MYPARCEL_DEFCON_HDOS';
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
    /**
     * This constant has a confusing name.
     * Webhooks are always processed regardless of this setting.
     * When this setting is enabled, order statuses in PrestaShop itself are updated as well.
     */
    const WEBHOOK_ENABLED = 'MYPARCEL_WEBHOOK_ENABLED';
    const WEBHOOK_CHECK_INTERVAL = 86400;
    const WEBHOOK_LAST_CHECK = 'MYPARCEL_WEBHOOK_UPD';
    const WEBHOOK_ID = 'MYPARCEL_WEBHOOK_ID'; //daily check
    const CONFIG_TOUR = 'config';
    const CONNECTION_ATTEMPTS = 3;
    const LOG_API = 'MYPARCEL_LOG_API';
    const SHIPPED_STATUS = 'MYPARCEL_SHIPPED_STATUS';
    const RECEIVED_STATUS = 'MYPARCEL_RECEIVED_STATUS';

    // @codingStandardsIgnoreStart
    protected static $cachedCarriers = array();
    /** @var int $id_carrier */
    public $id_carrier;
    /** @var array $gitHubRepos */
    public $gitHubRepos = array('myparcel/prestashop', 'firstred/myparcel');
    public $hooks = array(
        'displayCarrierList',
        'displayBeforeCarrier',
        'displayHeader',
        'displayBackOfficeHeader',
        'adminOrder',
        'orderDetail',
        'actionValidateOrder',
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
     */
    public function __construct()
    {
        $this->name = 'myparcel';
        $this->tab = 'shipping_logistics';
        $this->version = '2.0.9';

        $this->author = 'MyParcel';

        $this->module_key = 'c9bb3b85a9726a7eda0de2b54b34918d';

        $this->bootstrap = true;

        $this->controllers = array('myparcelcheckout', 'deliveryoptions', 'hook');

        parent::__construct();

        if (isset(Context::getContext()->employee->id) && Context::getContext()->employee->id) {
            $this->moduleUrlWithoutToken = Context::getContext()->link->getAdminLink('AdminModules', false).'&'.http_build_query(array(
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ));

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
     */
    protected function checkWebhooks()
    {
        $lastCheck = (int) Configuration::get(self::WEBHOOK_LAST_CHECK, (int) Configuration::get('PS_LANG_DEFAULT'), Shop::getGroupFromShop((int) Configuration::get('PS_SHOP_DEFAULT')), (int) Configuration::get('PS_SHOP_DEFAULT'));
        $webHookId = Configuration::get(self::WEBHOOK_ID);

        if (time() > $lastCheck + self::WEBHOOK_CHECK_INTERVAL || !$webHookId || Tools::getValue($this->name.'CheckForUpdates')) {
            // Time to update webhooks
            $opts = array(
                'http' => array(
                    'method'        => 'GET',
                    'header'        => 'Authorization: basic '.base64_encode(Configuration::get(self::API_KEY)),
                    'ignore_errors' => true,
                ),
            );

            $context = stream_context_create($opts);

            $response = Tools::file_get_contents('https://api.myparcel.nl/webhook_subscriptions/'.(string) $webHookId, false, $context);

            if ($response) {
                $found = false;
                $idWebhook = (int) Configuration::get(self::WEBHOOK_ID);
                $data = Tools::jsonDecode($response, true);
                $sslEnabled = (bool) Configuration::get('PS_SSL_ENABLED');
                $webhookUrl = Context::getContext()->link->getModuleLink($this->name, 'hook', array(), $sslEnabled);
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

                if (!$found) {
                    $apiKey = base64_encode(Configuration::get(self::API_KEY));
                    $opts = array(
                        'http' => array(
                            'method'        => 'POST',
                            'header'        => "Authorization: basic $apiKey\r\nContent-Type: application/json; charset=utf-8",
                            'content'       => Tools::jsonEncode(
                                (object) array(
                                    'data' => (object) array(
                                        'webhook_subscriptions' => array(
                                            (object) array(
                                                'hook' => 'shipment_status_change',
                                                'url'  => $webhookUrl,
                                            ),
                                        ),
                                    ),
                                )
                            ),
                            'ignore_errors' => true,
                        ),
                    );

                    $context = stream_context_create($opts);

                    $response = Tools::file_get_contents('https://api.myparcel.nl/webhook_subscriptions', false, $context);

                    if ($response) {
                        $data = Tools::jsonDecode($response, true);
                        if (isset($data['data']['ids'][0]['id'])) {
                            Configuration::updateValue(self::WEBHOOK_ID, (int) $data['data']['ids'][0]['id'], false, 0, 0);
                        }
                    }
                }

                Configuration::updateValue(self::WEBHOOK_LAST_CHECK, time(), false, 0, 0);
            }

            self::retrieveSupportedCountries();
            $this->checkForUpdates();
        }
    }

    /**
     * Retrieve suported countries from MyParcel API
     *
     * @return bool|mixed|string Raw json or false if not found
     *
     * @since 2.0.0
     */
    protected static function retrieveSupportedCountries()
    {
        // Time to update country list
        if ($countries = Tools::file_get_contents(self::SUPPORTED_COUNTRIES_URL)) {
            Configuration::updateValue(self::SUPPORTED_COUNTRIES, $countries, false, 0, 0);
        }

        return $countries;
    }

    /**
     * Check for updates
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function checkForUpdates()
    {
        foreach ($this->gitHubRepos as $repo) {
            try {
                @$status = Tools::file_get_contents("https://api.github.com/repos/{$repo}/releases/latest");
                if ($status
                    && $status = Tools::jsonDecode($status, true)
                        && isset($status['tag_name'])
                        && isset($status['assets'][0]['browser_download_url'])
                ) {
                    if (version_compare($status['tag_name'], $this->version, '>')) {
                        $zipLocation = _PS_MODULE_DIR_.$this->name.'.zip';
                        if (@!file_exists($zipLocation)) {
                            file_put_contents($zipLocation, fopen($status['assets'][0]['browser_download_url'], 'r'));
                        }
                        if (@file_exists($zipLocation)) {
                            $this->extractArchive($zipLocation);
                        }
                    }
                }
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Extract module archive
     *
     * @param string $file     File location
     * @param bool   $redirect Whether there should be a redirection after extracting
     *
     * @return bool
     *
     * @since 2.0.0
     */
    protected function extractArchive($file, $redirect = true)
    {
        $zipFolders = array();
        $tmpFolder = _PS_MODULE_DIR_.'selfupdate'.md5(time());
        if (@!file_exists($file)) {
            $this->addError($this->l('Module archive could not be downloaded'));

            return false;
        }
        $success = false;
        if (Tools::substr($file, -4) == '.zip') {
            if (Tools::ZipExtract($file, $tmpFolder) && file_exists($tmpFolder.DIRECTORY_SEPARATOR.$this->name)) {
                if (@rename(_PS_MODULE_DIR_.$this->name, _PS_MODULE_DIR_.$this->name.'backup') && @rename($tmpFolder.DIRECTORY_SEPARATOR.$this->name, _PS_MODULE_DIR_.$this->name)) {
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$this->name.'backup');
                    $success = true;
                } else {
                    if (file_exists(_PS_MODULE_DIR_.$this->name.'backup')) {
                        $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$this->name);
                        @rename(_PS_MODULE_DIR_.$this->name.'backup', _PS_MODULE_DIR_.$this->name);
                    }
                }
            }
        } elseif (@filemtime(_PS_TOOL_DIR_.'tar/Archive_Tar.php')) {
            if (!class_exists('Archive_Tar')) {
                require_once(_PS_TOOL_DIR_.'tar/Archive_Tar.php');
            }
            $archive = new Archive_Tar($file);
            if ($archive->extract($tmpFolder)) {
                $zipFolders = scandir($tmpFolder);
                if ($archive->extract(_PS_MODULE_DIR_)) {
                    $success = true;
                }
            }
        }
        if (!$success) {
            $this->addError($this->l('There was an error while extracting the update (file may be corrupted).'));
        } else {
            //check if it's a real module
            foreach ($zipFolders as $folder) {
                if (!in_array($folder, array('.', '..', '.svn', '.git', '__MACOSX')) && !Module::getInstanceByName($folder)) {
                    $this->addError(sprintf($this->l('The module %1$s that you uploaded is not a valid module.'), $folder));
                    $this->recursiveDeleteOnDisk(_PS_MODULE_DIR_.$folder);
                }
            }
        }
        @unlink($file);
        $this->recursiveDeleteOnDisk($tmpFolder);
        if ($success) {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            if ($redirect) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules', true).'&doNotAutoUpdate=1');
            }
        }

        return $success;
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
                $this->context->controller->errors[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            // Do not add error in this case
            // It will break execution of ModuleAdminController
            $this->context->controller->warnings[] = $message;
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
                    } elseif (array_key_exists('nodispatch', $cutoffExceptions[$date]) && $cutoffExceptions[$date]['nodispatch']) {
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
     * @since 1.0.0
     */
    public function install()
    {
        if (version_compare(PHP_VERSION, '5.3.3', '<')) {
            $this->addError($this->l('The MyParcel module could not be installed on your system. Please enable PHP 5.3.3 or higher.'), false);

            return false;
        }

        if (parent::install() === false) {
            return false;
        }

        if (!$this->installSql()) {
            parent::uninstall();

            return false;
        }

        $this->addCarrier('PostNL', self::POSTNL_DEFAULT_CARRIER);
        $this->addCarrier('PostNL Brievenbuspakje', self::POSTNL_DEFAULT_MAILBOX_PACKAGE_CARRIER);

        foreach ($this->hooks as $hook) {
            $this->registerHook($hook);
        }

        Configuration::updateGlobalValue(self::CHECKOUT_FG_COLOR1, '#FFFFFF');
        Configuration::updateGlobalValue(self::CHECKOUT_FG_COLOR2, '#000000');
        Configuration::updateGlobalValue(self::CHECKOUT_BG_COLOR1, '#FBFBFB');
        Configuration::updateGlobalValue(self::CHECKOUT_BG_COLOR2, '#01BBC5');
        Configuration::updateGlobalValue(self::CHECKOUT_BG_COLOR3, '#75D3D8');
        Configuration::updateGlobalValue(self::CHECKOUT_HL_COLOR, '#FF8C00');
        Configuration::updateGlobalValue(self::CHECKOUT_FONT, 'Exo');
        Configuration::updateGlobalValue(self::LABEL_DESCRIPTION, '{order.reference}');
        Configuration::updateGlobalValue(self::SHIPPED_STATUS, (int) Configuration::get('PS_OS_SHIPPING'));
        Configuration::updateGlobalValue(self::RECEIVED_STATUS, (int) Configuration::get('PS_OS_DELIVERED'));
        Configuration::updateValue(self::AUTO_UPDATE, true, false, 0, 0);

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
     */
    protected function installSql()
    {
        if (!(MyParcelCarrierDeliverySetting::createDatabase()
            && MyParcelDeliveryOption::createDatabase()
            && MyParcelOrder::createDatabase()
            && MyParcelOrderHistory::createDatabase())
        ) {
            $this->addError(Db::getInstance()->getMsgError());
            $this->uninstallSql();

            return false;
        }
        try {
            Db::getInstance()->execute('ALTER IGNORE TABLE `'._DB_PREFIX_.bqSQL(MyParcelDeliveryOption::$definition['table']).'` ADD CONSTRAINT `id_cart` UNIQUE (`id_cart`)');
        } catch (Exception $e) {
            Logger::addLog("MyParcel installation warning: {$e->getMessage()}");
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
        if (!(MyParcelCarrierDeliverySetting::dropDatabase())) {
            $this->addError(Db::getInstance()->getMsgError());

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
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->name;
        $carrier->shipping_handling = false;
        $carrier->shipping_method = 2;
        $this->setCarrierTaxes($carrier);

        foreach (Language::getLanguages() as $lang) {
            $idLang = (int) $lang['id_lang'];
            $carrier->delay[$idLang] = '-';
        }

        if ($carrier->add()) {
            $this->addGroups($carrier);
            $this->addZones($carrier);
            $this->addRanges($carrier);

            @copy(dirname(__FILE__).'/views/img/postnl-thumb.jpg', _PS_SHIP_IMG_DIR_.DIRECTORY_SEPARATOR.(int) $carrier->id.'.jpg');
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

            if ($key === self::POSTNL_DEFAULT_CARRIER) {
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
            $deliverySetting->add();

            return $carrier;
        }

        return false;
    }

    /**
     * Uninstalls the module
     *
     * @return bool Indicates whether the module has been successfully uninstalled
     *
     * @since 1.0.0
     */
    public function uninstall()
    {
        Configuration::deleteByName(self::API_KEY);

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
     * @param array $params
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function hookDisplayBackOfficeHeader($params)
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
            $supportedCountries = self::getSupportedCountries();
            if (isset($supportedCountries['data']['countries'][0])) {
                $countryIsos = array_keys($supportedCountries['data']['countries'][0]);
                foreach (Country::getCountries($this->context->language->id) as &$country) {
                    if (in_array(Tools::strtoupper($country['iso_code']), $countryIsos)) {
                        $countries[Tools::strtoupper($country['iso_code'])] = array(
                            'iso_code' => Tools::strtoupper($country['iso_code']),
                            'name'     => $country['name'],
                            'region'   => $supportedCountries['data']['countries'][0][Tools::strtoupper($country['iso_code'])]['region'],
                        );
                    }
                }
            }

            $this->context->smarty->assign(
                array(
                    'myParcel'             => 'true',
                    'prestaShopVersion'    => Tools::substr(_PS_VERSION_, 0, 3),
                    'myparcel_process_url' => $this->moduleUrlWithoutToken.'&token='.Tools::getAdminTokenLite('AdminModules').'&ajax=1',
                    'apiKey'               => base64_encode(Configuration::get(self::API_KEY)),
                    'jsCountries'          => Tools::jsonEncode($countries),
                )
            );

            Media::addJsDef(array('myparcel_module_url' => __PS_BASE_URI__."modules/{$this->name}/"));

            $html .= $this->display(__FILE__, 'views/templates/admin/export/adminvars.tpl');

            if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                $this->context->controller->addJquery();
                $this->context->controller->addCSS($this->_path.'views/css/16/myparcel.css', 'all');
            } else {
                $this->context->controller->addJquery('1.11.0');
                $this->context->controller->addCSS($this->_path.'views/css/15/myparcel.css', 'all');
            }
            $this->context->controller->addJS($this->_path.'views/js/myparcelexport/dist/myparcelexport.js');

            $this->context->controller->addCSS($this->_path.'views/css/backoffice.css', 'all');
        } elseif (Tools::getValue('controller') == 'AdminModules' && Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryUI('datepicker-nl');

            $this->context->smarty->assign(
                array(
                    'current_lang_iso' => Tools::strtolower(Language::getIsoById($this->context->language->id)),
                )
            );

            $html .= $this->display(__FILE__, 'views/templates/hooks/initdeliverysettings.tpl');
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
     */
    public static function getSupportedCountries()
    {
        $supportedCountries = Tools::jsonDecode(Configuration::get(self::SUPPORTED_COUNTRIES, null, 0, 0), true);
        if (!$supportedCountries) {
            if ($supportedCountries = self::retrieveSupportedCountries()) {
                $supportedCountries = Tools::jsonDecode($supportedCountries, true);
            }
        }

        return $supportedCountries;
    }

    /**
     * Configuration Page: get content of the form
     *
     * @return string Configuration page HTML
     *
     * @since 1.0.0
     */
    public function getContent()
    {
        if (Tools::getValue('ajax')) {
            return $this->ajaxProcess();
        }

        MyParcelCarrierDeliverySetting::createMissingColumns();
        $this->baseUrl = Context::getContext()->link->getAdminLink('AdminModules', false).'?'.http_build_query(array('configure' => $this->name, 'module_name' => $this->name));
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

        if (Module::isEnabled('onepagecheckoutps')) {
            $this->addWarning($this->l('The `One Page Checkout` by PresTeamShop does not support additional fees on carriers. This functionality has therefore been disabled.'));
        }

        $output = '';

        $this->postProcess();

        $output .= $this->display(__FILE__, 'views/templates/admin/navbar.tpl');

        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/16/back.js');

        switch (Tools::getValue('menu')) {
            case self::MENU_DEFAULT_SETTINGS:
                $this->menu = self::MENU_DEFAULT_SETTINGS;
                $output .= $this->display(__FILE__, 'views/templates/admin/insuredconf.tpl');

                return $output.$this->displayDefaultSettingsForm();
                break;
            case self::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->menu = self::MENU_DEFAULT_DELIVERY_OPTIONS;

                return $output.$this->displayDeliveryOptionsPage();
                break;
            case self::MENU_UPDATES:
                $this->menu = self::MENU_UPDATES;

                return $output.$this->displayUpdatesPage();
                break;
            default:
                $this->menu = self::MENU_MAIN_SETTINGS;

                return $output.$this->displayMainSettingsPage();
                break;
        }
    }

    protected function ajaxProcess()
    {
        if (isset($_SERVER['HTTP_X_PROXY_URL'])) {
            $this->apiProxy();
            exit;
        }

        $action = '';
        if (Tools::isSubmit('action')) {
            $action = Tools::getValue('action');
        } else {
            $input = file_get_contents('php://input');
            if ($input) {
                $input = Tools::jsonDecode($input);
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
            case 'PrintLabel':
                $this->printLabel();
                break;
            case 'createRelatedReturnLabel':
                $this->createRelatedReturnLabel();
                break;
            case 'createUnrelatedReturnLabel':
                $this->createUnrelatedReturnLabel();
                break;
            case 'saveConcept':
                $this->saveConcept();
                break;
            default:
                header('Content-Type: text/plain');
                http_response_code(401);
                die('Unauthorized');
        }
        exit;
    }

    protected function apiProxy()
    {
        $csAjaxFilters = true;

        $csAjaxFilterDomain = false;

        $validRequests = array(
            'https://api.myparcel.nl/shipments',
        );

        $curlOptions = array();

        $apiKey = base64_encode(Configuration::get(self::API_KEY));

        $requestHeaders = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0 || strpos($key, 'CONTENT_') === 0) {
                $headername = str_replace('_', ' ', str_replace('HTTP_', '', $key));
                $headername = str_replace(' ', '-', ucwords(strtolower($headername)));
                if (in_array($headername, array('Content-Type', 'Accept'))) {
                    $requestHeaders[] = "$headername: $value";
                }
            }
        }
        $requestHeaders[] = "Authorization: Basic $apiKey";

        $requestHeaders = array_unique($requestHeaders);

        // identify request method, url and params
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ('GET' == $requestMethod) {
            $requestParams = $_GET;
        } elseif ('POST' == $requestMethod) {
            $requestParams = $_POST;
            if (empty($requestParams)) {
                $data = file_get_contents('php://input');
                if (!empty($data)) {
                    $requestParams = $data;
                }
            }
        } elseif ('PUT' == $requestMethod || 'DELETE' == $requestMethod) {
            $requestParams = file_get_contents('php://input');
        } else {
            $requestParams = null;
        }

        $requestBody = file_get_contents('php://input');

        if (Tools::jsonDecode($requestBody)) {
            $requestBody = Tools::jsonDecode($requestBody);
            $moduleData = new stdClass();
            if (isset($requestBody->moduleData)) {
                $moduleData = $requestBody->moduleData;
                unset($requestBody->moduleData);
            }
            $requestBody = Tools::jsonEncode($requestBody);
        } else {
            $moduleData = null;
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders[] = 'Content-Length: '.strlen($requestBody);
        $requestHeaders = array_values($requestHeaders);

        // Get URL from `csurl` in GET or POST data, before falling back to X-Proxy-URL header.
        if (isset($_REQUEST['csurl'])) {
            $requestUrl = urldecode($_REQUEST['csurl']);
        } elseif (isset($_SERVER['HTTP_X_PROXY_URL'])) {
            $requestUrl = urldecode($_SERVER['HTTP_X_PROXY_URL']);
        } else {
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            header('Status: 404 Not Found');
            $_SERVER['REDIRECT_STATUS'] = 404;
            exit;
        }

        $pRequestUrl = parse_url($requestUrl);
        // csurl may exist in GET request methods
        if (is_array($requestParams) && array_key_exists('csurl', $requestParams)) {
            unset($requestParams['csurl']);
        }

        // ignore requests for proxy :)
        if (preg_match('!'.$_SERVER['SCRIPT_NAME'].'!', $requestUrl) || empty($requestUrl) || count($pRequestUrl) == 1) {
            exit;
        }

        // check against valid requests
        if ($csAjaxFilters) {
            $parsed = $pRequestUrl;
            if ($csAjaxFilterDomain) {
                if (!in_array($parsed['host'], $validRequests)) {
                    exit;
                }
            }
        }

        // append query string for GET requests
        if ($requestMethod == 'GET' && count($requestParams) > 0 && (!array_key_exists('query', $pRequestUrl) || empty($pRequestUrl['query']))) {
            $requestUrl .= '?'.http_build_query($requestParams);
        }

        // let the request begin
        $ch = curl_init($requestUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);   // (re-)send headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // return response
        curl_setopt($ch, CURLOPT_HEADER, true);       // enabled response headers

        // add data for POST, PUT or DELETE requests
        if ('POST' == $requestMethod) {
            $postData = is_array($requestBody) ? http_build_query($requestBody) : $requestBody;
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        } elseif ('PUT' == $requestMethod || 'DELETE' == $requestMethod) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $requestMethod);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
        }

        // Set multiple options for curl according to configuration
        if (is_array($curlOptions) && 0 <= count($curlOptions)) {
            curl_setopt_array($ch, $curlOptions);
        }

        // retrieve response (headers and content)
        $response = curl_exec($ch);
        curl_close($ch);

        // split response to header and content
        list($responseHeaders, $responseContent) = preg_split('/(\r\n){2}/', $response, 2);

        // (re-)send the headers
        $responseHeaders = preg_split('/(\r\n){1}/', $responseHeaders);
        list($responseHeaders, $responseContent) = $this->shipmentApiInterceptor($moduleData, $responseHeaders, $responseContent);
        foreach ($responseHeaders as $key => $responseHeader) {
            // Rewrite the `Location` header, so clients will also use the proxy for redirects.
            if (preg_match('/^Location:/', $responseHeader)) {
                list(, $value) = preg_split('/: /', $responseHeader, 2);
                $responseHeader = 'Location: '.$_SERVER['REQUEST_URI'].'?csurl='.$value;
            }
            if (!preg_match('/^(Transfer-Encoding):/', $responseHeader)) {
                header($responseHeader, false);
            }
        }

        // finally, output the content
        print($responseContent);
    }

    /**
     * Intercept API calls
     *
     * @param stdClass $moduleData
     * @param array    $responseHeaders
     * @param string   $responseContent
     *
     * @return array
     */
    protected function shipmentApiInterceptor($moduleData, $responseHeaders, $responseContent)
    {
        if (!empty($moduleData)) {
            $responseContent = Tools::jsonDecode($responseContent);

            // TODO: validate
            $idOrder = $moduleData->idOrder;

            if (isset($responseContent->data->ids[0]->id) && $responseContent->data->ids[0]->id) {
                $idShipment = $responseContent->data->ids[0]->id;
            } else {
                header('Content-Type: application/json; charset=utf-8');
                die(
                Tools::jsonEncode(
                    (object) array(
                        'success' => false,
                        'error'   => $responseContent,
                    )
                )
                );
            }

            $myparcelOrder = new MyParcelOrder();
            $myparcelOrder->id_order = $idOrder;
            $myparcelOrder->id_shipment = $idShipment;
            $myparcelOrder->postnl_status = '1';
            $myparcelOrder->retour = false;
            $myparcelOrder->postcode = $moduleData->postcode;
            $myparcelOrder->postnl_final = false;
            $myparcelOrder->shipment = Tools::jsonEncode($moduleData->shipment);
            if (isset($moduleData->shipment->pickup)) {
                $myparcelOrder->type = self::TYPE_POST_OFFICE;
            } elseif (isset($moduleData->shipment->option->delivery_type)) {
                $myparcelOrder->type = $moduleData->shipment->option->delivery_type;
            } else {
                $myparcelOrder->type = self::TYPE_PARCEL;
            }

            $myparcelOrder->add();

            $myparcelOrder->shipment = Tools::jsonDecode($myparcelOrder->shipment);

            $responseContent = (array) $responseContent;
            $responseContent['moduleData'] = (array) $myparcelOrder;
            $responseContent = (object) $responseContent;

            $responseContent = Tools::jsonEncode($responseContent);
        }

        return array($responseHeaders, $responseContent);
    }

    /**
     * Retrieve order info
     *
     * @throws Exception
     * @throws SmartyException
     */
    protected function processOrderInfo()
    {
        if (!$this->active) {
            header('Content-Type: text/plain');
            http_response_code(404);
            die('MyParcel module has been disabled');
        }

        header('Content-Type: application/json');
        $orderIds = Tools::getValue('ids');

        // Retrieve customer preferences
        echo Tools::jsonEncode(
            array(
                'preAlerted' => MyParcelOrder::getByOrderIds($orderIds),
                'concepts'   => MyParcelDeliveryOption::getByOrderIds($orderIds),
            )
        );

        // Retrieve delivery addresses

        die();
    }

    protected function getShipmentInfo()
    {
        $this->shipmentGetProxy();
    }

    protected function shipmentGetProxy()
    {
        $apiKey = base64_encode(Configuration::get(self::API_KEY));

        $requestHeaders = array();
        $requestHeaders[] = "Authorization: Basic {$apiKey}";

        $requestBody = file_get_contents('php://input');

        if (Tools::jsonDecode($requestBody)) {
            $requestBody = Tools::jsonDecode($requestBody);
            $moduleData = new stdClass();
            if (isset($requestBody->moduleData)) {
                $moduleData = $requestBody->moduleData;
                unset($requestBody->moduleData);
            }
            $requestBody = Tools::jsonEncode($requestBody);
        } else {
            $moduleData = null;
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders = array_values($requestHeaders);

        $opts = array(
            'http' => array(
                'method'        => 'GET',
                'header'        => implode("\r\n", $requestHeaders),
                'ignore_errors' => true,
            ),
        );

        $context = stream_context_create($opts);

        $responseContent = Tools::file_get_contents("https://api.myparcel.nl/shipments/{$moduleData->shipment}", false, $context);

        $responseContent = $this->getShipmentApiInterceptor($moduleData, $responseContent);

        // finally, output the content
        print($responseContent);
    }

    /**
     * Intercept Get Shipment API calls
     *
     * @param stdClass $moduleData
     * @param string   $responseContent
     *
     * @return string
     */
    protected function getShipmentApiInterceptor($moduleData, $responseContent)
    {
        if ($responseContent) {
            $responseContent = Tools::jsonDecode($responseContent);

            if (isset($responseContent->data->shipments[0])) {
                $shipment = $responseContent->data->shipments[0];

                $myparcelOrder = MyParcelOrder::getByShipmentId($moduleData->shipment);
                if (Validate::isLoadedObject($myparcelOrder)) {
                    $myparcelOrder->shipment = Tools::jsonEncode($shipment);
                    $myparcelOrder->save();

                    if (!$myparcelOrder->tracktrace && isset($shipment->barcode) && $shipment->barcode) {
                        MyParcelOrder::updateStatus($myparcelOrder->id_shipment, $shipment->barcode, $shipment->status, $shipment->modified);
                        $this->updateOrderTrackingNumber($myparcelOrder->id_order, $shipment->barcode);
                    }
                }
            }

            $responseContent = Tools::jsonEncode($responseContent);
        }

        return $responseContent;
    }

    /**
     * Print label
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function printLabel()
    {
        $apiKey = base64_encode(Configuration::get(self::API_KEY));

        $requestHeaders = array();
        $requestHeaders[] = "Authorization: Basic {$apiKey}";
        $requestHeaders[] = 'Accept: application/json; charset=utf-8';

        $requestBody = file_get_contents('php://input');

        $request = Tools::jsonDecode($requestBody, true);
        if (is_array($request) && array_key_exists('idShipments', $request)) {
            $idShipments = $request['idShipments'];
            $shipments = implode(',', $idShipments);
        } else {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        if (is_array($requestBody)) {
            unset($requestBody['controller']);
            unset($requestBody['token']);
            unset($requestBody['controllerUri']);
        }

        $requestHeaders = array_values($requestHeaders);

        $opts = array(
            'http' => array(
                'method'        => 'GET',
                'header'        => implode("\r\n", $requestHeaders),
                'ignore_errors' => true,
            ),
        );

        $context = stream_context_create($opts);

        $responseContent = Tools::file_get_contents("https://api.myparcel.nl/shipment_labels/{$shipments}?format=A6", false, $context);

        if ($response = Tools::jsonDecode($responseContent, true)) {
            $response['success'] = true;
            foreach ($idShipments as $idShipment) {
                $mpo = MyParcelOrder::getByShipmentId($idShipment);
                $response['success'] &= $mpo->printed();
            }

            die(Tools::jsonEncode($response));
        }

        die(Tools::jsonEncode(array(
            'success' => false,
        )));
    }

    /**
     * @return void
     *
     * @since 2.0.0
     */
    protected function createRelatedReturnLabel()
    {
        $request = Tools::jsonDecode(file_get_contents('php://input'), true);
        if (isset($request['moduleData']['parent'])) {
            $parent = (int) $request['moduleData']['parent'];
        } else {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        $sql = new DbQuery();
        $sql->select('c.`firstname`, c.`lastname`, c.`email`, mo.`id_shipment`, mo.`postcode`, o.`id_order`');
        $sql->from(bqSQL(MyParcelOrder::$definition['table']), 'mo');
        $sql->innerJoin(bqSQL(Order::$definition['table']), 'o', 'o.`id_order` = mo.`id_order`');
        $sql->innerJoin(bqSQL(Customer::$definition['table']), 'c', 'c.`id_customer` = o.`id_customer`');
        $sql->where('`id_shipment` = '.$parent);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        if (!$result) {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n", array(
                    'Content-Type: application/vnd.return_shipment+json;charset=utf-8',
                    'Authorization: basic '.base64_encode(Configuration::get(self::API_KEY)),
                )),
                'content' => Tools::jsonEncode(array(
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
                )),
            ),
        );

        $context = stream_context_create($opts);
        $response = Tools::file_get_contents('https://api.myparcel.nl/shipments', false, $context, 20);
        $response = Tools::jsonDecode($response, true);
        header('Content-Type: application/json;charset=utf-8');
        if ($response && isset($response['data'])) {
            die(Tools::jsonEncode(
                array(
                    'success' => true,
                )
            ));
        }

        die(Tools::jsonEncode(array(
            'success' => false,
        )));
    }

    /**
     * @return false|string
     *
     * @since 2.0.0
     */
    protected function createUnrelatedReturnLabel()
    {
        $opts = array(
            'http' => array(
                'method' => 'POST',
                'header' => implode("\r\n", array(
                    'Content-Type: application/vnd.return_shipment+json;charset=utf-8',
                    'Authorization: basic '.base64_encode(Configuration::get(self::API_KEY)),
                )),
            ),
        );

        $context = stream_context_create($opts);

        $response = Tools::file_get_contents('https://api.myparcel.nl/shipments', false, $context, 20);
        $response = json_decode($response, true);
        if ($response && isset($response['data']['download_url']['link'])) {
            die(Tools::jsonEncode(
                array(
                    'success' => true,
                    'data'    => array(
                        'url' => $response['data']['download_url']['link'],
                    ),
                )
            ));
        }

        return die(Tools::jsonEncode(array(
             'success' => false,
        )));
    }

    /**
     * Save concept
     *
     * @return void
     *
     * @since 1.0.0
     */
    protected function saveConcept()
    {
        $data = Tools::jsonDecode(file_get_contents('php://input'));

        header('Content-Type: application/json');
        if (isset($data->data->concept)) {
            die(
            Tools::jsonEncode(array(
                'success' => (bool) MyParcelDeliveryOption::saveConcept((int) $data->data->idOrder, Tools::jsonEncode($data->data->concept)),
            )));
        }

        die(Tools::jsonEncode(array(
            'success' => false,
        )));
    }

    /**
     * Initialize navigation
     *
     * @return array Menu items
     */
    protected function initNavigation()
    {
        $menu = array(
            'main'            => array(
                'short'  => $this->l('Settings'),
                'desc'   => $this->l('Module settings'),
                'href'   => $this->baseUrl.'&menu='.self::MENU_MAIN_SETTINGS.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-gears',
            ),
            'defaultsettings' => array(
                'short'  => $this->l('Shipping settings'),
                'desc'   => $this->l('Default shipping settings'),
                'href'   => $this->baseUrl.'&menu='.self::MENU_DEFAULT_SETTINGS.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-truck',
            ),
            'deliveryoptions' => array(
                'short'  => $this->l('Delivery options'),
                'desc'   => $this->l('Available delivery options'),
                'href'   => $this->baseUrl.'&menu='.self::MENU_DEFAULT_DELIVERY_OPTIONS.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-truck',
            ),
            'updates'         => array(
                'short'  => $this->l('Updates'),
                'desc'   => $this->l('Check for updates'),
                'href'   => $this->baseUrl.'&menu='.self::MENU_UPDATES.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'active' => false,
                'icon'   => 'icon-refresh',
            ),
        );

        switch (Tools::getValue('menu')) {
            case self::MENU_DEFAULT_SETTINGS:
                $this->menu = self::MENU_DEFAULT_SETTINGS;
                $menu['defaultsettings']['active'] = true;
                break;
            case self::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->menu = self::MENU_DEFAULT_DELIVERY_OPTIONS;
                $menu['deliveryoptions']['active'] = true;
                break;
            case self::MENU_UPDATES:
                $this->menu = self::MENU_UPDATES;
                $menu['updates']['active'] = true;
                break;
            default:
                $this->menu = self::MENU_MAIN_SETTINGS;
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
     * @since 2.0.0
     */
    protected function postProcess()
    {
        switch (Tools::getValue('menu')) {
            case self::MENU_DEFAULT_SETTINGS:
                $this->postProcessDefaultSettingsPage();
                break;
            case self::MENU_DEFAULT_DELIVERY_OPTIONS:
                $this->postProcessDeliverySettingsPage();
                break;
            case self::MENU_UPDATES:
                $this->postProcessUpdatesPage();
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
     */
    protected function postProcessDefaultSettingsPage()
    {
        $submitted = false;

        foreach (array_keys($this->getDefaultSettingsFormValues()) as $key) {
            if (Tools::isSubmit($key)) {
                $submitted = true;
                switch ($key) {
                    case self::DEFAULT_CONCEPT_INSURED_AMOUNT:
                        Configuration::updateValue($key, (int) Tools::getValue($key) * 100);
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
     */
    public function getDefaultSettingsFormValues()
    {
        return array(
            self::LINK_EMAIL                                => Configuration::get(self::LINK_EMAIL),
            self::LINK_PHONE                                => Configuration::get(self::LINK_PHONE),
            self::LABEL_DESCRIPTION                         => Configuration::get(self::LABEL_DESCRIPTION),
            self::DEFAULT_CONCEPT_PARCEL_TYPE               => Configuration::get(self::DEFAULT_CONCEPT_PARCEL_TYPE),
            self::DEFAULT_CONCEPT_LARGE_PACKAGE             => Configuration::get(self::DEFAULT_CONCEPT_LARGE_PACKAGE),
            self::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY        => Configuration::get(self::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
            self::DEFAULT_CONCEPT_SIGNED                    => Configuration::get(self::DEFAULT_CONCEPT_SIGNED),
            self::DEFAULT_CONCEPT_RETURN                    => Configuration::get(self::DEFAULT_CONCEPT_RETURN),
            self::DEFAULT_CONCEPT_INSURED                   => Configuration::get(self::DEFAULT_CONCEPT_INSURED),
            self::DEFAULT_CONCEPT_INSURED_TYPE              => Configuration::get(self::DEFAULT_CONCEPT_INSURED_TYPE),
            self::DEFAULT_CONCEPT_INSURED_AMOUNT            => (int) Configuration::get(self::DEFAULT_CONCEPT_INSURED_AMOUNT) / 100,
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
                $this->context->controller->confirmations[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
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
            if (MyParcelCarrierDeliverySetting::toggleDelivery(Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']))) {
                $this->addConfirmation($this->l('The status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle status'));
            }
        } elseif (Tools::isSubmit('pickup'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::togglePickup(Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']))) {
                $this->addConfirmation($this->l('The status has been successfully toggled'));
            } else {
                $this->addError($this->l('Unable to toggle status'));
            }
        } elseif (Tools::isSubmit('mailbox_package'.MyParcelCarrierDeliverySetting::$definition['table'])) {
            if (MyParcelCarrierDeliverySetting::toggleMailboxPackage(Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']))) {
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
        $mss = new MyParcelCarrierDeliverySetting((int) Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary']));
        if (!Validate::isLoadedObject($mss)) {
            $this->addError($this->l('Could not process delivery setting'));

            return;
        }

        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW} = (int) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERYDAYS_WINDOW);
        $mss->{MyParcelCarrierDeliverySetting::DROPOFF_DELAY} = (int) Tools::getValue(MyParcelCarrierDeliverySetting::DROPOFF_DELAY);
        $mss->{MyParcelCarrierDeliverySetting::DELIVERY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::DELIVERY);
        $mss->{MyParcelCarrierDeliverySetting::PICKUP} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MAILBOX_PACKAGE);
        $mss->{MyParcelCarrierDeliverySetting::MORNING} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING);
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP);
        $mss->{MyParcelCarrierDeliverySetting::EVENING} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::EVENING);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED);
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY} = (bool) Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY);

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

        $mss->{MyParcelCarrierDeliverySetting::MORNING_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_FEE);
        $mss->{MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::MORNING_PICKUP_FEE);
        $mss->{MyParcelCarrierDeliverySetting::EVENING_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::EVENING_FEE);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_FEE);
        $mss->{MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::RECIPIENT_ONLY_FEE);
        $mss->{MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE} = (float) Tools::getValue(MyParcelCarrierDeliverySetting::SIGNED_RECIPIENT_ONLY_FEE);

        self::processCarrierDeliverySettingsRestrictions($mss);
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
    public static function isTime($input)
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
     * Process settings on the update page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function postProcessUpdatesPage()
    {
        if (Tools::isSubmit(self::AUTO_UPDATE)) {
            Configuration::updateValue(self::AUTO_UPDATE, (bool) Tools::getValue(self::AUTO_UPDATE));
        }
    }

    /**
     * Post process main settings page
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function postProcessMainSettingsPage()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $validUser = false;
            $validApi = false;

            // api key
            $apiKey = (string) Tools::getValue(self::API_KEY);
            if (!$apiKey
                || empty($apiKey)
                || !Validate::isGenericName($apiKey)
            ) {
                $this->addError($this->l('Invalid Api Key'));
            } else {
                $validApi = true;
                Configuration::updateValue(self::API_KEY, $apiKey);
            }

            if ($validUser && $validApi) {
                $this->addConfirmation($this->l('Settings updated'));
            }

            Configuration::updateValue(self::CHECKOUT_FG_COLOR1, Tools::getValue(self::CHECKOUT_FG_COLOR1));
            Configuration::updateValue(self::CHECKOUT_FG_COLOR2, Tools::getValue(self::CHECKOUT_FG_COLOR2));
            Configuration::updateValue(self::CHECKOUT_BG_COLOR1, Tools::getValue(self::CHECKOUT_BG_COLOR1));
            Configuration::updateValue(self::CHECKOUT_BG_COLOR2, Tools::getValue(self::CHECKOUT_BG_COLOR2));
            Configuration::updateValue(self::CHECKOUT_BG_COLOR3, Tools::getValue(self::CHECKOUT_BG_COLOR3));
            Configuration::updateValue(self::CHECKOUT_HL_COLOR, Tools::getValue(self::CHECKOUT_HL_COLOR));
            Configuration::updateValue(self::CHECKOUT_FONT, Tools::getValue(self::CHECKOUT_FONT));
            Configuration::updateValue(self::WEBHOOK_ENABLED, (bool) Tools::getValue(self::WEBHOOK_ENABLED));
            Configuration::updateValue(self::LOG_API, (bool) Tools::getValue(self::LOG_API));
            Configuration::updateValue(self::SHIPPED_STATUS, (int) Tools::getValue(self::SHIPPED_STATUS));
            Configuration::updateValue(self::RECEIVED_STATUS, (int) Tools::getValue(self::RECEIVED_STATUS));
        }
    }

    /**
     * Everything necessary to display the whole form.
     *
     * @return string HTML for the bo page
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&menu='.self::MENU_DEFAULT_SETTINGS;
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
     */
    protected function getDefaultConceptsForm()
    {
        $currency = Currency::getDefaultCurrency();

        return array(
            'form' => array(
                'legend'      => array(
                    'title' => $this->l('Concepts'),
                    'icon'  => 'icon-paper',
                ),
                'description' => $this->l('These are the default concept settings'),
                'input'       => array(
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Default parcel type'),
                        'name'     => self::DEFAULT_CONCEPT_PARCEL_TYPE,
                        'required' => true,
                        'options'  => array(
                            'query' => array(
                                array(
                                    'id'   => self::TYPE_PARCEL,
                                    'name' => $this->l('Parcel'),
                                ),
                                array(
                                    'id'   => self::TYPE_MAILBOX_PACKAGE,
                                    'name' => $this->l('Brievenbuspakje'),
                                ),
                                array(
                                    'id'   => self::TYPE_UNSTAMPED,
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
                        'name'    => self::DEFAULT_CONCEPT_LARGE_PACKAGE,
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
                        'name'    => self::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY,
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
                        'name'    => self::DEFAULT_CONCEPT_SIGNED,
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
                        'name'    => self::DEFAULT_CONCEPT_RETURN,
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
                        'name'    => self::DEFAULT_CONCEPT_INSURED,
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
                        'name'    => self::DEFAULT_CONCEPT_INSURED_TYPE,
                        'options' => array(
                            'query' => array(
                                array(
                                    'id'   => self::INSURED_TYPE_50,
                                    'name' => $this->l('50'),
                                ),
                                array(
                                    'id'   => self::INSURED_TYPE_250,
                                    'name' => $this->l('250'),
                                ),
                                array(
                                    'id'   => self::INSURED_TYPE_500,
                                    'name' => $this->l('500'),
                                ),
                                array(
                                    'id'   => self::INSURED_TYPE_500_PLUS,
                                    'name' => $this->l('>500'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('> 500'),
                        'name'     => self::DEFAULT_CONCEPT_INSURED_AMOUNT,
                        'size'     => 10,
                        'prefix'   => $currency->sign,
                        'currency' => (version_compare(_PS_VERSION_, '1.6', '<')) ? false : true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Label description'),
                        'name'     => self::LABEL_DESCRIPTION,
                        'size'     => 50,
                        'required' => true,
                        'desc'     => $this->display(__FILE__, 'views/templates/admin/labeldesc.tpl'),
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
                        'desc'    => $this->l('Sharing the customer\'s email address with MyParcel makes sure that MyParcel can send a Track and Trace email. You can configure the email settings in the MyParcel back office.'),
                        'name'    => self::LINK_EMAIL,
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
                        'desc'    => $this->l('When sharing the customer\'s phone number with MyParcel the carrier can use this phone number for delivery. This greatly increases the chance of a successful delivery when sending shipments abroad.'),
                        'name'    => self::LINK_PHONE,
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
     * @since 2.0.0
     */
    protected function displayDeliveryOptionsPage()
    {
        $output = '';

        $this->updateCarriers();

        $this->context->controller->addJS($this->_path.'views/js/forms.js');
        $this->context->controller->addCSS($this->_path.'views/css/forms.css');

        if (Tools::isSubmit(MyParcelCarrierDeliverySetting::$definition['primary'])) {
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
     * @throws PrestaShopDatabaseException
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function updateCarriers()
    {
        $carriers = Carrier::getCarriers(Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
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
                    bqSQL(MyParcelCarrierDeliverySetting::$definition['table']), array(
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
    public function getShopId()
    {
        if (isset(Context::getContext()->employee->id) && Context::getContext()->employee->id && Shop::getContext() == Shop::CONTEXT_SHOP) {
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
    public function removeOldExceptions($idMyParcelDeliveryOption)
    {
        $samedayDeliveryOption = new MyParcelCarrierDeliverySetting($idMyParcelDeliveryOption);
        if (Validate::isLoadedObject($samedayDeliveryOption)) {
            $exceptions = Tools::jsonDecode($samedayDeliveryOption->cutoff_exceptions, true);
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
                    $samedayDeliveryOption->cutoff_exceptions = Tools::jsonEncode($exceptions);
                }
            }

            $samedayDeliveryOption->save();
        }
    }

    /**
     * Display forms
     *
     * @return string Forms HTML
     */
    public function renderDeliveryOptionForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.MyParcelCarrierDeliverySetting::$definition['primary'];
        $helper->currentIndex = $this->baseUrl.'&menu='.self::MENU_DEFAULT_DELIVERY_OPTIONS;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getDeliveryOptionsFormValues((int) Tools::getValue(MyParcelCarrierDeliverySetting::$definition['primary'])),
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
                        'desc'    => $this->l('Guaranteed morning pickup after 8:30'),
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
//                        'disabled' => Module::isEnabled('onepagecheckoutps'),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Morning delivery'),
                        'desc'    => $this->l('Guaranteed morning delivery before 12:00'),
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
                        'disabled' => Module::isEnabled('onepagecheckoutps'),
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
                        'disabled' => Module::isEnabled('onepagecheckoutps'),
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
                        'disabled' => Module::isEnabled('onepagecheckoutps'),
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
                        'disabled' => Module::isEnabled('onepagecheckoutps'),
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
                        'disabled' => Module::isEnabled('onepagecheckoutps'),
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
                    'title' => $this->l('24hr delivery'),
                    'icon'  => 'icon-clock-o',
                ),
                'description' => (date_default_timezone_get() === 'Europe/Amsterdam') ? '' : sprintf($this->l('The module assumes that you are using the following timezone: %s'), ini_get('date.timezone')),
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
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public function renderDeliveryOptionList()
    {
        $list = $this->getDeliveryOptionsList();

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
            'cutoff_array'                                                => array(
                'title'   => $this->l('Cut off times'),
                'type'    => 'cutoff_times',
                'align'   => 'center',
                'orderby' => false,
                'search'  => false,
                'class'   => 'sameday-cutoff-labels',
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = array('edit');
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->listTotal = count($list);
        $helper->identifier = bqSQL(MyParcelCarrierDeliverySetting::$definition['primary']);
        $helper->title = $this->l('Cutoff times');
        $helper->table = MyParcelCarrierDeliverySetting::$definition['table'];
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->baseUrl.'&menu='.self::MENU_DEFAULT_DELIVERY_OPTIONS;
        $helper->colorOnBackground = true;
        $helper->no_link = true;

        foreach ($list as $carrier) {
            if ($carrier['external_module_name']) {
                $helper->tpl_vars = array('moduleWarning' => $this->l('Some carriers are managed by external modules. This might or might not work, depending on the external module. The carriers are marked in orange. Make sure you test these, before going live!'));
                break;
            }
        }

        return $helper->generateList($list, $fieldsList);
    }

    /**
     * Get the current objects' list form the database
     *
     * @throws PrestaShopException
     *
     * @return array
     *
     * @since 2.0.0
     */
    public function getDeliveryOptionsList()
    {
        $sql = new DbQuery();
        $sql->select('mcds.*, c.`name`, c.`external_module_name`');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']), 'mcds');
        $sql->innerJoin('carrier', 'c', 'mcds.`id_reference` = c.`id_reference` AND c.`deleted` = 0');
        $sql->where('mcds.`id_shop` = '.(int) $this->context->shop->id);

        $list = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($list as &$samedaySetting) {
            $cutoffExceptions = Tools::jsonDecode($samedaySetting[MyParcelCarrierDeliverySetting::CUTOFF_EXCEPTIONS], true);
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
                $samedaySetting['color'] = '#FFD8A6';
            }
        }

        return $list;
    }

    /**
     * @return string Updates page
     *
     * @since 2.0.0
     */
    protected function displayUpdatesPage()
    {
        $this->context->smarty->assign(
            array(
                'module_url' => $this->baseUrl.'&token='.Tools::getAdminTokenLite('AdminModules').'&menu='.self::MENU_UPDATES,
            )
        );
        $output = $this->display(__FILE__, 'views/templates/admin/updates.tpl');

        return $output.$this->displayUpdatesForm();
    }

    /**
     * Everything necessary to display the whole form.
     *
     * @return string HTML for the bo page
     *
     * @since 2.0.0
     */
    protected function displayUpdatesForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name.'updates';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&menu='.self::MENU_UPDATES;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                self::AUTO_UPDATE => Configuration::get(self::AUTO_UPDATE, null, 0, 0),
            ),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        $forms = array(
            $this->getUpdatesForm(),
        );

        return $helper->generateForm($forms);
    }

    /**
     * Get the PakjeGemak form
     *
     * @return array Form
     */
    protected function getUpdatesForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Auto update'),
                    'icon'  => 'icon-refresh',
                ),
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Auto update'),
                        'desc'    => $this->l('Enabling this option will make sure that the module will check daily for updates, right from the back office'),
                        'name'    => self::AUTO_UPDATE,
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
     * Display main settings page
     *
     * @return string
     *
     * @since 2.0.0
     */
    protected function displayMainSettingsPage()
    {
        $this->context->controller->addJquery();
        if (version_compare(_PS_VERSION_, '1.6.0.0', '>=')) {
            $this->context->controller->addCSS($this->_path.'views/css/16/fontselect.css', 'all');
            $this->context->controller->addJS($this->_path.'views/js/16/fontselect.js');
        } else {
            $this->context->controller->addCSS($this->_path.'views/css/15/fontselect.css', 'all');
            $this->context->controller->addJS($this->_path.'views/js/15/fontselect.js');
        }

        return $this->displayMainForm();
    }

    /**
     * Configuration Page: display form
     *
     * @return string Main page form HTML
     *
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
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ),
        );
        $helper->fields_value = $this->getMainFormValues();

        return $helper->generateForm(array($this->getApiForm(), $this->getCheckoutForm(), $this->getAdvancedForm()));
    }

    /**
     * Get Main form configuration values
     *
     * @return array Configuration values
     *
     * @since 2.0.0
     */
    protected function getMainFormValues()
    {
        return array(
            self::API_KEY            => Configuration::get(self::API_KEY),
            self::CHECKOUT_FG_COLOR1 => Configuration::get(self::CHECKOUT_FG_COLOR1),
            self::CHECKOUT_FG_COLOR2 => Configuration::get(self::CHECKOUT_FG_COLOR2),
            self::CHECKOUT_BG_COLOR1 => Configuration::get(self::CHECKOUT_BG_COLOR1),
            self::CHECKOUT_BG_COLOR2 => Configuration::get(self::CHECKOUT_BG_COLOR2),
            self::CHECKOUT_BG_COLOR3 => Configuration::get(self::CHECKOUT_BG_COLOR3),
            self::CHECKOUT_HL_COLOR  => Configuration::get(self::CHECKOUT_HL_COLOR),
            self::CHECKOUT_FONT      => Configuration::get(self::CHECKOUT_FONT),
            self::WEBHOOK_ENABLED    => Configuration::get(self::WEBHOOK_ENABLED),
            self::LOG_API            => Configuration::get(self::LOG_API),
            self::SHIPPED_STATUS     => Configuration::get(self::SHIPPED_STATUS),
            self::RECEIVED_STATUS    => Configuration::get(self::RECEIVED_STATUS),
        );
    }

    /**
     * Get the PakjeGemak form
     *
     * @return array Form
     */
    protected function getApiForm()
    {
        $shippedStatus = new OrderState(Configuration::get('PS_OS_SHIPPING'), $this->context->language->id);
        $deliveredStatus = new OrderState(Configuration::get('PS_OS_DELIVERED'), $this->context->language->id);
        if (!Validate::isLoadedObject($shippedStatus)) {
            $shippedStatus = array(
                'name' => 'Verzonden',
            );
        }
        if (!Validate::isLoadedObject($deliveredStatus)) {
            $deliveredStatus = array(
                'name' => 'Afgeleverd',
            );
        }

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
                        'name'      => self::API_KEY,
                        'size'      => 50,
                        'maxlength' => 50,
                        'required'  => true,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Automate order statuses'),
                        'desc'    => sprintf($this->l('By enabling this options the statuses `%s` and `%s` will automatically be set after exporting an order.'), $shippedStatus->name, $deliveredStatus->name),
                        'name'    => self::WEBHOOK_ENABLED,
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
     * Get the PakjeGemak form
     *
     * @return array Form
     */
    protected function getCheckoutForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('MyParcel Checkout'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Header text color'),
                        'name'     => self::CHECKOUT_FG_COLOR1,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Body text color'),
                        'name'     => self::CHECKOUT_FG_COLOR2,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Body background color'),
                        'name'     => self::CHECKOUT_BG_COLOR1,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Selected tab color'),
                        'name'     => self::CHECKOUT_BG_COLOR2,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Deselected tab color'),
                        'name'     => self::CHECKOUT_BG_COLOR3,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'     => 'color',
                        'label'    => $this->l('Highlight color'),
                        'name'     => self::CHECKOUT_HL_COLOR,
                        'lang'     => false,
                        'data-hex' => true,
                        'class'    => 'mColorPicker',
                    ),
                    array(
                        'type'  => 'fontselect',
                        'label' => $this->l('Font family'),
                        'name'  => self::CHECKOUT_FONT,
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

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Advanced'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'     => 'select',
                        'label'    => $this->l('Shipped status'),
                        'desc'     => $this->l('Choose this status when the order has been received by PostNL'),
                        'name'     => self::SHIPPED_STATUS,
                        'required' => true,
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
                        'desc'     => $this->l('Choose this status when the order has been received by the customer'),
                        'name'     => self::RECEIVED_STATUS,
                        'required' => true,
                        'options'  => array(
                            'query' => $orderStatuses,
                            'id'    => 'id_order_state',
                            'name'  => 'name',
                        ),
                        'class'    => 'fixed-width-xxl',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Api logger'),
                        'desc'    => $this->l('By enabling this option, API calls are being logged. They can be found on the page `Advanced Parameters > Logs`.'),
                        'name'    => self::LOG_API,
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
                'myparcel_checkout_link'        => $this->context->link->getModuleLink($this->name, 'myparcelcheckout', array(), true),
                'myparcel_deliveryoptions_link' => $this->context->link->getModuleLink($this->name, 'deliveryoptions', array(), true),
            )
        );

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            return $this->display(__FILE__, 'views/templates/hooks/beforecarrier17.tpl');
        }

        /** @var Cart $cart */
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            if (Configuration::get(self::LOG_API)) {
                Logger::addLog("{$this->displayName}: No valid cart found");
            }

            return '';
        }

        /** @var Currency $currency */
        $currency = Currency::getCurrencyInstance($cart->id_currency);

        $address = new Address((int) $cart->id_address_delivery);
        if (!preg_match('/^(.*?)\s+(\d+)(.*)$/', $address->address1.' '.$address->address2, $m)) {
            // No house number
            if (Configuration::get(self::LOG_API)) {
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
            if (Configuration::get(self::LOG_API)) {
                Logger::addLog("{$this->displayName}: Cannot retrieve settings from the database");
            }

            return '';
        }

        if ($mcds->delivery || $mcds->pickup) {
            return $this->display(__FILE__, 'views/templates/hooks/beforecarrier.tpl');
        }

        return '';
    }

    /**
     * Display before carrier hook
     *
     * @return string Hook HTML
     * @throws PrestaShopDatabaseException
     *
     * @since 2.0.0
     */
    public function hookDisplayCarrierList()
    {
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
     * @since 2.0.0
     */
    public function hookAdminOrder($params)
    {
        $countries = array();
        foreach (Country::getCountries($this->context->language->id) as &$country) {
            $countries[Tools::strtoupper($country['iso_code'])] = array(
                'iso_code' => Tools::strtoupper($country['iso_code']),
                'name'     => $country['name'],
            );
        }

        $this->context->smarty->assign(
            array(
                'idOrder'            => (int) $params['id_order'],
                'concept'            => MyParcelDeliveryOption::getByOrder((int) $params['id_order']),
                'preAlerted'         => Tools::jsonEncode(MyParcelOrder::getByOrderIds(array((int) $params['id_order']))),
                'myparcelProcessUrl' => $this->moduleUrlWithoutToken.'&token='.Tools::getAdminTokenLite('AdminModules').'&ajax=1',
                'jsCountries'        => Tools::jsonEncode($countries),
            )
        );

        return $this->display(__FILE__, 'views/templates/hooks/adminorderdetail.tpl');
    }

    /**
     * Validate order hook
     *
     * @param array $params
     *
     * @return void
     *
     * @since 2.0.0
     */
    public function hookActionValidateOrder($params)
    {
        /** @var Order $order */
        $order = $params['order'];

        /** @var Cart $cart */
        $cart = $params['cart'];
        $address = new Address($order->id_address_delivery);

        $customer = new Customer($cart->id_customer);
        $address->email = $customer->email;

        $deliveryOption = MyParcelDeliveryOption::getRawByCartId($cart->id);
        $mailboxPackage = false;
        if (empty($deliveryOption)) {
            if (!$mailboxPackage = MyParcelDeliveryOption::checkMailboxPackage($cart)) {
                return;
            }
        }

        $concept = MyParcelDeliveryOption::createConcept($order, $deliveryOption, $address, $mailboxPackage);

        if (isset($deliveryOption->data)) {
            $deliveryOption = (object) array(
                'data'         => (object) $deliveryOption->data,
                'type'         => (isset($deliveryOption->type) ? (string) $deliveryOption->type : 'delivery'),
                'extraOptions' => (isset($deliveryOption->extraOptions) ? (object) $deliveryOption->extraOptions : new stdClass()),
                'concept'      => (object) $concept,
            );
        } else {
            $deliveryOption = (object) array(
                'concept' => $concept,
            );
        }

        MyParcelDeliveryOption::saveRawDeliveryOption(Tools::jsonEncode($deliveryOption), $cart->id);
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

        return (strlen($language->language_code) >= 5)
            ? strtolower(substr($language->language_code, 0, 2)).'-'.strtoupper(substr($language->language_code, 3, 2))
            : strtolower(substr($language->language_code, 0, 2)).'-'.strtoupper(substr($language->language_code, 0, 2));
    }

    /**
     * Get order shipping costs external
     *
     * @param array $params Hook parameters
     *
     * @return bool|float
     *
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
        $address = new Address($cart->id_address_delivery);
        $countryIso = (string) Country::getIsoById($address->id_country);
        if (!$countryIso) {
            $countryIso = Context::getContext()->country->iso_code;
        }
        $countryIso = Tools::strtolower($countryIso);

        if ($deliverySetting->mailbox_package) {
            // Disable if not delivering to the Netherlands
            if ($countryIso !== 'nl') {
                return false;
            }

            $amountOfBoxes = (int) $this->howManyMailboxPackages($cart->getProducts(), true);
            if ($amountOfBoxes < 1) {
                return false;
            }
        }

        $extraCosts = 0;
        if (in_array($countryIso, array('nl', 'be')) && isset($deliveryOption->type) && !Module::isEnabled('onepagecheckoutps')) {
            if ($deliveryOption->type === 'delivery') {
                if (isset($deliveryOption->extraOptions->signed) && $deliveryOption->extraOptions->signed === 'true'
                && isset($deliveryOption->extraOptions->recipientOnly) && $deliveryOption->extraOptions->recipientOnly === 'true') {
                    if (isset($deliveryOption->extraOptions->signed)
                        && $deliveryOption->extraOptions->signed === 'true'
                    ) {
                        $extraCosts += (float) $deliverySetting->signed_recipient_only_fee_tax_incl;
                    }
                } elseif (isset($deliveryOption->data->price_comment)
                    && ($deliveryOption->data->price_comment === 'morning' || in_array($deliveryOption->data->price_comment, array('night', 'avond', 'evening')))
                    && (isset($deliveryOption->extraOptions->signed) && $deliveryOption->extraOptions->signed === 'true' || isset($deliveryOption->extraOptions->recipientOnly) && $deliveryOption->extraOptions->recipientOnly === 'true')) {
                    $extraCosts += (float) $deliverySetting->signed_recipient_only_fee_tax_incl;
                } else {
                    if (isset($deliveryOption->extraOptions->signed)
                        && $deliveryOption->extraOptions->signed === 'true'
                    ) {
                        $extraCosts += (float) $deliverySetting->signed_fee_tax_incl;
                    }
                    if (isset($deliveryOption->extraOptions->recipientOnly)
                        && $deliveryOption->extraOptions->recipientOnly === 'true'
                    ) {
                        $extraCosts += (float) $deliverySetting->recipient_only_fee_tax_incl;
                    }
                }

                if (isset($deliveryOption->data->price_comment)
                    && ($deliveryOption->data->price_comment === 'morning' || in_array($deliveryOption->data->price_comment, array('night', 'avond', 'evening')))) {
                    if ($deliveryOption->data->price_comment === 'morning') {
                        $extraCosts += (float) $deliverySetting->morning_fee_tax_incl;
                    } elseif (in_array($deliveryOption->data->price_comment, array('night', 'avond', 'evening'))) {
                        $extraCosts += (float) $deliverySetting->evening_fee_tax_incl;
                    }
                }
            } elseif ($deliveryOption->type === 'pickup' && isset($deliveryOption->data->price_comment) && $deliveryOption->data->price_comment === 'retailexpress') {
                $extraCosts = (float) $deliverySetting->morning_pickup_fee_tax_incl;
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

        // Calculate tax rate
        $useTax = (Group::getPriceDisplayMethod($this->context->customer->id_default_group) == PS_TAX_INC) && Configuration::get('PS_TAX');
        $carrierTax = 1;
        if (Configuration::get('PS_ATCP_SHIPWRAP')) {
            if ($useTax) {
                $carrierTax = (1 + $cart->getAverageProductsTaxRate());
            }
        } else {
            if ($useTax && $carrier->getTaxesRate($address)) {
                $carrierTax = (1 + ($carrier->getTaxesRate($address) / 100));
            }
        }

        return $extraCosts * $conversion * $taxRate + ($this->calcPackageShippingCost($cart, $carrier->id, $useTax, null, null, null, false) / $carrierTax);
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
        $unitConfig = Configuration::getMultiple(
            array(
                'PS_WEIGHT_UNIT',
                'PS_DIMENSION_UNIT',
            )
        );
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
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public function calcPackageShippingCost($cart, $idCarrier, $useTax = true, $defaultCountry = null, $productList = null, $idZone = null, $recursion = true)
    {
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
        } elseif (count($productList)) {
            $prod = current($productList);
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

        $cacheId = $this->name.'MyParcelconfPackageShippingCost_'.(int) $cart->id.'_'.(int) $addressId.'_'.(int) $idCarrier.'_'.(int) $useTax.'_'.(int) $defaultCountry->id;
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
                    $defaultCountry = new Country(Configuration::get('PS_COUNTRY_DEFAULT'), Configuration::get('PS_LANG_DEFAULT'));
                }

                $idZone = (int) $defaultCountry->id_zone;
            }
        }

        if ($idCarrier && !$cart->isCarrierInRange((int) $idCarrier, (int) $idZone)) {
            $idCarrier = '';
        }

        if (empty($idCarrier) && $cart->isCarrierInRange((int) Configuration::get('PS_CARRIER_DEFAULT'), (int) $idZone)) {
            $idCarrier = (int) Configuration::get('PS_CARRIER_DEFAULT');
        }

        $totalPackageWithoutShippingTaxInc = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $productList);

        if (!isset(self::$cachedCarriers[$idCarrier])) {
            self::$cachedCarriers[$idCarrier] = new Carrier((int) $idCarrier);
        }

        /** @var Carrier $carrier */
        $carrier = self::$cachedCarriers[$idCarrier];

        $shippingMethod = $carrier->getShippingMethod();
        // Get only carriers that are compliant with shipping method
        if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT && $carrier->getMaxDeliveryPriceByWeight((int) $idZone) === false)
            || ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE && $carrier->getMaxDeliveryPriceByPrice((int) $idZone) === false)
        ) {
            return false;
        }

        // If out-of-range behavior carrier is set on "Deactivate carrier"
        if ($carrier->range_behavior) {
            $checkDeliveryPriceByWeight = Carrier::checkDeliveryPriceByWeight($idCarrier, $cart->getTotalWeight(), (int) $idZone);

            $totalOrder = $totalPackageWithoutShippingTaxInc;
            $checkDeliveryPriceByPrice = Carrier::checkDeliveryPriceByPrice($idCarrier, $totalOrder, (int) $idZone, (int) $cart->id_currency);

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
         * @global float $minShippingPrice
         */
        if (!isset($minShippingPrice)) {
            $minShippingPrice = $shipping;
        }

        if ($shipping <= $minShippingPrice) {
            $idCarrier = (int) $idCarrier;
            $minShippingPrice = $shipping;
        }

        if (empty($idCarrier)) {
            $idCarrier = '';
        }

        if (!isset(self::$cachedCarriers[$idCarrier])) {
            self::$cachedCarriers[$idCarrier] = new Carrier((int) $idCarrier, Configuration::get('PS_LANG_DEFAULT'));
        }

        $carrier = self::$cachedCarriers[$idCarrier];

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

        $configuration = Configuration::getMultiple(
            array(
                'PS_SHIPPING_FREE_PRICE',
                'PS_SHIPPING_HANDLING',
                'PS_SHIPPING_METHOD',
                'PS_SHIPPING_FREE_WEIGHT',
            )
        );

        // Free fees
        $freeFeesPrice = 0;
        if (isset($configuration['PS_SHIPPING_FREE_PRICE'])) {
            $freeFeesPrice = Tools::convertPrice((float) $configuration['PS_SHIPPING_FREE_PRICE'], Currency::getCurrencyInstance((int) $cart->id_currency));
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

            if (($shippingMethod == Carrier::SHIPPING_METHOD_WEIGHT && !Carrier::checkDeliveryPriceByWeight($carrier->id, $cart->getTotalWeight(), (int) $idZone))
                || ($shippingMethod == Carrier::SHIPPING_METHOD_PRICE && !Carrier::checkDeliveryPriceByPrice($carrier->id, $totalPackageWithoutShippingTaxInc, $idZone, (int) $cart->id_currency)
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
     * @throws PrestaShopDatabaseException
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
        $carriersDb = Db::getInstance()->executeS($sql);

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
                $this->context->controller->informations[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
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
                $this->context->controller->warnings[] = '<a href="'.$this->baseUrl.'">'.$this->displayName.': '.$message.'</a>';
            }
        } else {
            $this->context->controller->warnings[] = $message;
        }
    }

    /**
     * @param Carrier $carrier
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

        $carrier->setGroups($groupsIds);
    }

    /**
     * @param Carrier $carrier
     *
     * @since 2.0.0
     */
    protected function addRanges($carrier)
    {
        $rangePrice = new RangePrice();
        $rangePrice->id_carrier = $carrier->id;
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';
        $rangePrice->add();

        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '10000';
        $rangeWeight->add();
    }

    /**
     * @param Carrier $carrier
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function addZones($carrier)
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * Configure carrier taxes
     *
     * If the EU tax rates haven't been enabled with the
     * Advanced EU Compliance module, then try to apply the NL Standard Rate (21%) tax rules group
     *
     * @param Carrier $carrier
     */
    protected function setCarrierTaxes($carrier)
    {
        $taxRulesGroups = TaxRulesGroup::getTaxRulesGroups();
        if (!empty($taxRulesGroups)) {
            $idTaxRulesGroup = (int) TaxRulesGroup::getIdByName('NL Standard Rate (21%)');
            if (empty($idTaxRulesGroup)) {
                $idTaxRulesGroup = (int) TaxRulesGroup::getIdByName($taxRulesGroups[0]['name']);
            }
        } else {
            $idTaxRulesGroup = 0;
        }

        $carrier->id_tax_rules_group = $idTaxRulesGroup;
    }

    /**
     * Update the tracking number of an order.
     *
     * @param       $idOrder    int Order ID
     * @param       $tracktrace string Track and trace code
     *
     * @return string Error message
     * @throws PrestaShopException
     */
    protected function updateOrderTrackingNumber($idOrder, $tracktrace)
    {
        /* Update shipping number */
        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }
        $orderCarrier = new OrderCarrier($order->getIdOrderCarrier());
        if (!Validate::isLoadedObject($orderCarrier)) {
            return false;
        } elseif (!Validate::isTrackingNumber($tracktrace)) {
            return false;
        } else {
            // Retrocompatibility
            $order->shipping_number = $tracktrace;
            $order->update();

            // Update order_carrier
            $orderCarrier->tracking_number = pSQL($tracktrace);

            return $orderCarrier->update();
        }
    }

    /**
     * Performs a basic check and return an array with errors
     *
     * @return array
     */
    protected function basicCheck()
    {
        $errors = array();
        if (!Country::getByIso('NL') && !Country::getByIso('BE')) {
            $errors[] = $this->l('At least one of the following countries should be enabled: the Netherlands or Belgium.');
        }
        if (!Currency::getIdByIsoCode('EUR')) {
            $errors[] = $this->l('At least this currency has to be enabled: EUR');
        }

        return $errors;
    }
}

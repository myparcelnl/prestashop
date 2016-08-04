<?php
/**
 * MyParcel bootstrap file
 *
 * @copyright Copyright (c) 2013 MyParcel (https://www.myparcel.nl/)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MyParcel extends Module
{
    const CONF_REMOVE_AFTER_UNINSTALL_NAME_V_15 = 'MYPARCEL_REMOVE_ON_UNINSTALL';
    const CONF_REMOVE_AFTER_UNINSTALL_NAME = 'MYPARCEL_REMOVE_DATA_AFTER_UNINSTALL';

    public static $conf_username = 'MYPARCEL_USERNAME';
    public static $conf_api_key = 'MYPARCEL_API_KEY';
    public static $conf_plugin = 'MYPARCEL_FRONTEND_PLUGIN';
    public static $conf_remove_after_uninstall = self::CONF_REMOVE_AFTER_UNINSTALL_NAME;

    /**
     * Inits the main settings of the module
     *
     * @return MyParcel
     *
     */
    public function __construct()
    {
        $this->name = 'myparcel';
        $this->tab = 'shipping_logistics';

        if ('1.5' == substr(_PS_VERSION_, 0, 3)) {
            $this->version = 'v1.1.5';
            self::$conf_remove_after_uninstall = self::CONF_REMOVE_AFTER_UNINSTALL_NAME_V_15;
        } elseif ('1.6' == substr(_PS_VERSION_, 0, 3)) {
            $this->version = '1.1.5';
        }

        $this->author = 'MyParcel';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.6.9.9');
        // NOTE: Prestashop does not validate max version == version due to their invalid implementation of version_compare() >= 0 (should be > 0)

        parent::__construct();

        $this->displayName = $this->l('MyParcel');
        $this->description = $this->l('Assistance with the parcel service through MyParcel.nl');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the MyParcel module?');
    }

    /**
     * Installs the module
     *
     * @return boolean
     */
    public function install()
    {
        $override_admin_dir = _PS_ROOT_DIR_."/override/controllers/admin";

        if (!is_writable($override_admin_dir)) {
            $this->_errors[] = Tools::displayError('Unable to install the module (Folder: /override/controllers/admin is not writeable).');
            return false;
        }

        if (false === parent::install()) {
            return false;
        }

        $this->overrideTemplate();

        Db::getInstance()->Execute("CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "myparcel` (
                                      `myparcel_id` int(11) NOT NULL AUTO_INCREMENT,
                                      `order_id` int(11) NOT NULL,
                                      `consignment_id` bigint(20) NOT NULL,
                                      `retour` tinyint(1) NOT NULL DEFAULT '0',
                                      `tracktrace` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                                      `postcode` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
                                      `tnt_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                                      `tnt_updated_on` datetime NOT NULL,
                                      `tnt_final` tinyint(1) NOT NULL DEFAULT '0',
                                      PRIMARY KEY (`myparcel_id`)
                                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");

        Configuration::updateValue('MYPARCEL_ACTIVE', 'true');

        $this->registerHook('displayBackOfficeHeader');
        // Use hook for prestashop 1.5 only
        if($this->isPrestashop15())
            $this->registerHook('displayFooter');
        return true;
    }

    /**
     *  Override order admin template when installing
     */
    public function overrideTemplate()
    {
        $override_admin_dir = _PS_ROOT_DIR_."/override/controllers/admin";

        $dest_dir = $override_admin_dir . '/templates/orders/helpers/list';

        if (!is_dir($dest_dir)) {
            @mkdir($dest_dir, 0777, true);
        }

        $src_dir = dirname(__FILE__) . '/override/controllers/admin/templates/orders/helpers/list';
        
        copy($src_dir . '/list_content.tpl', $dest_dir . '/list_content.tpl');
        copy($src_dir . '/list_header.tpl', $dest_dir . '/list_header.tpl');
        
        $classIndexCache = _PS_CACHE_DIR_.'class_index.php';
        if (is_file($classIndexCache)) {
            @ unlink($classIndexCache);
        }
    }

    /**
     *  Remove override order admin template when uninstalling
     */
    public function removeOverrideTemplate()
    {
        $override_admin_dir = _PS_ROOT_DIR_."/override/controllers/admin";

        $src_dir = $override_admin_dir . '/templates/orders/helpers/list';

        if (is_file($src_dir . '/list_content.tpl')) {
            unlink($src_dir . '/list_content.tpl');
        }

        if (is_file($src_dir . '/list_header.tpl')) {
            unlink($src_dir . '/list_header.tpl');
        }
        
        $classIndexCache = _PS_CACHE_DIR_.'class_index.php';
        if (is_file($classIndexCache)) {
            @ unlink($classIndexCache);
        }
    }

    /**
     *  Hook the footer for pass pakjegemak url to js (only for version 1.5)
     */
    public function hookDisplayFooter(){
        $url = $this->getMyParcelUrl();
        $script = sprintf('<script>var MYPARCEL_PAKJEGEMAK_URL = "%s"</script>', $url);
        return $script;
    }

    /**
     * Uninstalls the module
     *
     * @return boolean
     */
    public function uninstall()
    {
        if ('1.5' == substr(_PS_VERSION_, 0, 3)) {
            self::$conf_remove_after_uninstall = self::CONF_REMOVE_AFTER_UNINSTALL_NAME_V_15;
        }

        $remove_data = Configuration::get(self::$conf_remove_after_uninstall);

        Configuration::deleteByName('MYPARCEL_ACTIVE');

        if ($remove_data) {
            Db::getInstance()->Execute("DROP TABLE `" . _DB_PREFIX_ . "myparcel`");

            Configuration::deleteByName(self::$conf_username);
            Configuration::deleteByName(self::$conf_api_key);
            Configuration::deleteByName(self::$conf_plugin);
            Configuration::deleteByName(self::$conf_remove_after_uninstall);
        }

        $this->removeOverrideTemplate();

        if (false === parent::uninstall()) {
            return false;
        }

        return true;
    }

    /**
     * Adds JavaScript files
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJS($this->_path . 'js/myparcel.js', 'all');
        $this->context->controller->addCSS($this->_path . 'css/myparcel.css', 'all');
    }

    /**
     * Gets MyParcel order data
     *
     * @param integer $orderId
     * @return array
     */
    static public function getOrderData($orderId)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'myparcel` WHERE `order_id` = ' . $orderId;

        $result = Db::getInstance()->ExecuteS($sql, true, false);

        $items = '';
        $checks = '';

        foreach ($result as &$row)
        {
            $_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'] .= $row['consignment_id'] . '|';

            $row['mypa_tracktrace_link'] = 'https://mijnpakket.postnl.nl/Inbox/Search?' . http_build_query(array(
                'lang' => 'nl',
                'B'    => $row['tracktrace'],
                'P'    => $row['postcode'],
            ));
            $row['mypa_tnt_status'] = empty($row['tnt_status']) ? 'Track&Trace' : $row['tnt_status'];
            $row['mypa_pdf_image'] = ($row['retour'] == 1) ? 'myparcel_retour.png' : 'myparcel_pdf.png';

            // get the order, then address, then country to reach the country ISO code
            $order = new Order(intval($row['order_id']));
            $address = new Address($order->id_address_delivery);
            $country = new Country();
            if($countryId = Country::getIdByName(null, $address->country))
            {
                $country = new Country($countryId);
            }
            if(!empty($country->iso_code) && $country->iso_code != 'NL')
            {
                $row['mypa_tracktrace_link'] = 'https://www.internationalparceltracking.com/Main.aspx#/track/' . implode('/', array(
                    $row['tracktrace'],
                    $country->iso_code,
                    $address->postcode,
                ));
            }

            $items .= '<a href="' . $row['mypa_tracktrace_link'] . '" target="_blank">' . $row['mypa_tnt_status'] . '</a>'
                    . '<a href="#" onclick="return printConsignments(\'' . $row['consignment_id'] . '\');" class="myparcel-pdf">'
                    . '<img border="0" alt="Print" src="/modules/myparcel/images/' . $row['mypa_pdf_image'] . '">'
                    . '</a>'
                    . '<br/>';

            $checks .= '|' . $row['consignment_id'];
        }

        if (!empty($checks)) {
            $checks = substr($checks, 1);
        }

        $myParcelData = array(
            'checks' => $checks,
            'items'  => $items,
        );

        return $myParcelData;
    }

    /**
     *  Configuration Page: get content of the form
     */
    public function getContent()
    {
        $output = null;
     
        if (Tools::isSubmit('submit'.$this->name))
        {
            $username = strval(Tools::getValue(self::$conf_username));
            $validUsername = false;
            $validApi = false;

            if (!$username
              || empty($username)
              || !Validate::isGenericName($username)) {
                $output .= $this->displayError($this->l('Invalid Username'));
              }
            else
            {
                Configuration::updateValue(self::$conf_username, $username);
                $validUsername = true;
            }
            // api key
            $api_key = strval(Tools::getValue(self::$conf_api_key));
            if (!$api_key
              || empty($api_key)
              || !Validate::isGenericName($api_key))
                $output .= $this->displayError($this->l('Invalid Api Key'));
            else
            {
                $validApi = true;
                Configuration::updateValue(self::$conf_api_key, $api_key);
            }
            // api key
            $frontend_plugin = strval(Tools::getValue(self::$conf_plugin));
            Configuration::updateValue(self::$conf_plugin, !empty($frontend_plugin) ? 1 : 0);

            // keep data when uninstall
            $keep_data = strval(Tools::getValue(self::$conf_remove_after_uninstall));
            Configuration::updateValue(self::$conf_remove_after_uninstall, !empty($keep_data) ? 1 : 0);

            if ($validUsername && $validApi) {
              $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }

    /**
     *  Configuration Page: display form
     */
    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
         
        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('My Parcel PakjeGemak'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Username') . '(*)',
                    'name' => self::$conf_username,
                    'size' => 20,
                    'maxlength' => 20,
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('API Key') . '(*)',
                    'name' => self::$conf_api_key,
                    'size' => 50,
                    'maxlength' => 50,
                    'required' => true
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Frontend plugin'),
                    'name' => 'MYPARCEL',
                    'values' => array(
                        'query' => array(
                            array(
                                'id' => 'FRONTEND_PLUGIN',
                                'val' => '1',
                                'checked' => 'checked'
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                ),
            ),
            
        );

$fields_form[1]['form'] = array(
            'legend' => array(
                // 'title' => $this->l('My Parcel Option'),
            ),
            'input' => array(
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Remove MyParcel data after uninstalling'),
                    'name' => 'MYPARCEL',
                    'values' => array(
                        'query' => array(
                            array(
                                'id' => str_replace('MYPARCEL_', '', self::$conf_remove_after_uninstall),
                                'val' => '1',
                                'checked' => 'checked'
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            ));
         
        $helper = new HelperForm();
         
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
         
        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
         
        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );
         
        // Load current value
        $helper->fields_value[self::$conf_username] = Configuration::get(self::$conf_username);
        $helper->fields_value[self::$conf_api_key] = Configuration::get(self::$conf_api_key);
        $helper->fields_value[self::$conf_plugin] = Configuration::get(self::$conf_plugin);
        $helper->fields_value[self::$conf_remove_after_uninstall] = Configuration::get(self::$conf_remove_after_uninstall);
         
        return $helper->generateForm($fields_form);
    }

    /**
     * Gets MyParcel url for frontend plugin
     *
     * @return string
     */
    public function getMyParcelUrl()
    {
        $username = Configuration::get(self::$conf_username);
        $api_key = Configuration::get(self::$conf_api_key);

        $webshop = _PS_BASE_URL_ ."/modules/myparcel/myparcel-passdata.html";
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $webshop = str_replace('http://', 'https://', $webshop);
        }
        $uw_hash = hash_hmac('sha1', $username . 'MyParcel' . $webshop, $api_key);

        $url = "http://www.myparcel.nl/pakjegemak-locatie?hash=" . $uw_hash . "&webshop=" . urlencode($webshop) . "&user=" . $username;;

        return $url;
    }

    public function isPrestashop15(){
        if ('1.5' == substr(_PS_VERSION_, 0, 3)) {
            return true;
        } elseif ('1.6' == substr(_PS_VERSION_, 0, 3)) {
            return false;
        }
    }
}
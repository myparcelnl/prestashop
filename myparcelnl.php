<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class MyParcelNL extends CarrierModule
{
    use \Gett\MyparcelNL\Module\Hooks\DisplayAdminProductsExtra;
    use \Gett\MyparcelNL\Module\Hooks\DisplayBackOfficeHeader;
    use \Gett\MyparcelNL\Module\Hooks\OrdersGridHooks;
    use \Gett\MyparcelNL\Module\Hooks\FrontHooks;
    use \Gett\MyparcelNL\Module\Hooks\LegacyOrderPageHooks;
    use \Gett\MyparcelNL\Module\Hooks\OrderLabelHooks;
    use \Gett\MyparcelNL\Module\Hooks\CarrierHooks;
    use \Gett\MyparcelNL\Module\Hooks\OrderHooks;
    public $baseUrl;
    public $id_carrier;
    public $migrations = [
        \Gett\MyparcelNL\Database\CreateProductConfigurationTableMigration::class,
        \Gett\MyparcelNL\Database\CreateCarrierConfigurationTableMigration::class,
        \Gett\MyparcelNL\Database\CreateOrderLabelTableMigration::class,
        \Gett\MyparcelNL\Database\CreateDeliverySettingTableMigration::class,
    ];
    public $carrierStandardShippingCost = [];
    public $cartCarrierStandardShippingCost = null;

    public $configItems = [
        \Gett\MyparcelNL\Constant::POSTNL_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::BPOST_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::DPD_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::DELIVERED_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::WEBHOOK_ID_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::API_LOGGING_CONFIGURATION_NAME, // Keep the API key

        \Gett\MyparcelNL\Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::AGE_CHECK_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::INSURANCE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::CUSTOMS_CODE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::CUSTOMS_AGE_CHECK_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::LABEL_SIZE_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::LABEL_POSITION_CONFIGURATION_NAME,
        \Gett\MyparcelNL\Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME,

        \Gett\MyparcelNL\Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME,
    ];

    public $hooks = [
        'displayAdminProductsExtra',
        'displayBackOfficeHeader',
        'actionProductUpdate',
        'displayCarrierExtraContent',
        'actionCarrierUpdate',
        'displayHeader',
        'actionCarrierProcess',
        'actionOrderGridDefinitionModifier',
        'actionAdminControllerSetMedia',
        'actionOrderGridQueryBuilderModifier',
        'actionOrderGridPresenterModifier',
        'actionAdminOrdersListingFieldsModifier',
        'displayAdminListBefore',
        'actionAdminControllerSetMedia',
        'displayAdminOrderMainBottom',
        'displayAdminOrderMain',
        'actionObjectGettMyParcelNLOrderLabelAddAfter',
        'actionObjectGettMyParcelNLOrderLabelUpdateAfter',
        'displayInvoice',
        'displayAdminAfterHeader',
        'actionValidateOrder',
    ];
    /** @var string */
    protected $baseUrlWithoutToken;

    public function __construct()
    {
        $this->name = 'myparcelnl';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.7';
        $this->author = 'Gett';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        if (!empty(Context::getContext()->employee->id)) {
            $this->baseUrlWithoutToken = $this->getAdminLink(
                'AdminModules',
                false,
                [
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name,
                ]
            );
            $this->baseUrl = $this->getAdminLink(
                'AdminModules',
                true,
                [
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name,
                ]
            );
        }
        $this->displayName = $this->l('MyParcelNL');
        $this->description = $this->l('PrestaShop module to intergratie with MyParcel NL and MyParcel BE');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->registerHook('displayAdminOrderMain');
    }

    public function getAdminLink(string $controller, bool $withToken = true, array $params = [])
    {
        $url = parse_url($this->context->link->getAdminLink($controller, $withToken));
        $url['query'] = isset($url['query']) ? $url['query'] : '';
        parse_str($url['query'], $query);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $url['query'] = http_build_query($query + $params, PHP_QUERY_RFC1738);
        } else {
            $url['query'] = http_build_query($query + $params);
        }

        return $this->mypa_stringify_url($url);
    }

    public function getContent()
    {
        $configuration = new \Gett\MyparcelNL\Module\Configuration\Configure($this);

        $this->context->smarty->assign([
            'menutabs' => $configuration->initNavigation(),
            'ajaxUrl' => $this->baseUrlWithoutToken,
        ]);

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->display(__FILE__, 'views/templates/admin/navbar.tpl');

        return $output . $configuration(Tools::getValue('menu'));
    }

    public function getOrderShippingCost($cart, $shipping_cost)
    {
        if ($this->id_carrier != $cart->id_carrier) {
            return $shipping_cost;
        }
        if (!empty($this->context->controller->requestOriginalShippingCost)) {
            return $shipping_cost;
        }

        $myParcelCost = 0;
        $deliverySettings = Tools::getValue('myparcel-delivery-options', false);
        if ($deliverySettings) {
            $deliverySettings = json_decode($deliverySettings, true);
        } else {
            $deliverySettings = $this->getDeliverySettingsByCart((int) $cart->id);
        }

        if (empty($deliverySettings)) {
            return $shipping_cost;
        }

        $isPickup = (isset($deliverySettings['isPickup'])) ? $deliverySettings['isPickup'] : false;
        if ($isPickup) {
            $myParcelCost += (float) \Gett\MyparcelNL\Service\CarrierConfigurationProvider::get(
                $cart->id_carrier,
                'pricePickup'
            );
        } else {
            $deliveryType = (isset($deliverySettings['deliveryType'])) ? $deliverySettings['deliveryType'] : 'standard';
            if ($deliveryType !== 'standard') {
                $priceHourInterval = 'price' . ucfirst($deliveryType) . 'Delivery';
                $myParcelCost += (float) \Gett\MyparcelNL\Service\CarrierConfigurationProvider::get(
                    $cart->id_carrier,
                    $priceHourInterval
                );
            }
            if (!empty($deliverySettings['shipmentOptions']['only_recipient'])) {
                $myParcelCost += (float) \Gett\MyparcelNL\Service\CarrierConfigurationProvider::get(
                    $cart->id_carrier,
                    'priceOnlyRecipient'
                );
            }
            if (!empty($deliverySettings['shipmentOptions']['signature'])) {
                $myParcelCost += (float) \Gett\MyparcelNL\Service\CarrierConfigurationProvider::get(
                    $cart->id_carrier,
                    'priceSignature'
                );
            }
        }

        return $shipping_cost + $myParcelCost;
    }

    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    public function install(): bool
    {
        return parent::install()
            && (new \Gett\MyparcelNL\Module\Installer($this))();
    }

    public function uninstall(): bool
    {
        return (new \Gett\MyparcelNL\Module\Uninstaller($this))()
            && parent::uninstall();
    }

    public function appendQueryToUrl($urlString, $query = [])
    {
        $url = parse_url($urlString);
        $url['query'] = isset($url['query']) ? $url['query'] : '';
        parse_str($url['query'], $oldQuery);
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            $url['query'] = http_build_query($oldQuery + $query, PHP_QUERY_RFC1738);
        } else {
            $url['query'] = http_build_query($oldQuery + $query);
        }

        return $this->mypa_stringify_url($url);
    }

    public function getModuleCountry()
    {
        return (strpos($this->name, 'be') !== false) ? 'BE' : 'NL';
    }

    public function isNL()
    {
        return $this->getModuleCountry() === 'NL';
    }

    public function isBE()
    {
        return $this->getModuleCountry() === 'BE';
    }

    private function mypa_stringify_url($parsedUrl)
    {
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
        $pass = ($user || $pass) ? "{$pass}@" : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";
    }

    public function getDeliverySettingsByCart(int $idCart)
    {
        $query = new DbQuery();
        $query->select('delivery_settings');
        $query->from('myparcelnl_delivery_settings');
        $query->where('id_cart = ' . (int) $idCart);
        $query->orderBy('id_delivery_setting DESC');
        $deliverySettings = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
        if (empty($deliverySettings)) {
            return null;
        }

        return json_decode($deliverySettings, true);
    }

    public function getShippingOptions($id_carrier, $address)
    {
        $carrier = new Carrier($id_carrier);

        $taxRate = ($carrier->getTaxesRate($address) / 100) + 1;

        $includeTax = !Product::getTaxCalculationMethod((int) $this->context->cart->id_customer)
                && (int) Configuration::get('PS_TAX');
        $displayTaxLabel = (Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'));

        return [
            'tax_rate' => ($includeTax)? $taxRate : 1,
            'include_tax' => $includeTax,
            'display_tax_label' => $displayTaxLabel,
        ];
    }
}

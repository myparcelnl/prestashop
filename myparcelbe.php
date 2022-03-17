<?php

/** @noinspection PhpFullyQualifiedNameUsageInspection */

declare(strict_types=1);

use Gett\MyparcelBE\Module\Tools\Tools;

if (! defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class MyParcelBE extends CarrierModule
{
    use \Gett\MyparcelBE\Module\Hooks\DisplayAdminProductsExtra;
    use \Gett\MyparcelBE\Module\Hooks\DisplayBackOfficeHeader;
    use \Gett\MyparcelBE\Module\Hooks\OrdersGridHooks;
    use \Gett\MyparcelBE\Module\Hooks\FrontHooks;
    use \Gett\MyparcelBE\Module\Hooks\LegacyOrderPageHooks;
    use \Gett\MyparcelBE\Module\Hooks\CarrierHooks;
    use \Gett\MyparcelBE\Module\Hooks\OrderHooks;

    public const MODULE_NAME = 'myparcelbe';

    public $baseUrl;

    public $configItems = [
        \Gett\MyparcelBE\Constant::POSTNL_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::BPOST_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::DPD_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::DELIVERED_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::WEBHOOK_ID_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::API_LOGGING_CONFIGURATION_NAME, // Keep the API key

        \Gett\MyparcelBE\Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::AGE_CHECK_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::INSURANCE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::CUSTOMS_CODE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::LABEL_SIZE_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::LABEL_POSITION_CONFIGURATION_NAME,
        \Gett\MyparcelBE\Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME,

        \Gett\MyparcelBE\Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME,
    ];

    public $hooks       = [
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
        'actionObjectGettMyParcelBEOrderLabelAddAfter',
        'actionObjectGettMyParcelBEOrderLabelUpdateAfter',
        'displayInvoice',
        'displayAdminAfterHeader',
        'actionValidateOrder',
    ];

    /**
     * @var class-string<\Gett\MyparcelBE\Database\Migration>[]
     */
    public $migrations = [
        \Gett\MyparcelBE\Database\CreateProductConfigurationTableMigration::class,
        \Gett\MyparcelBE\Database\CreateCarrierConfigurationTableMigration::class,
        \Gett\MyparcelBE\Database\CreateOrderLabelTableMigration::class,
        \Gett\MyparcelBE\Database\CreateDeliverySettingTableMigration::class,
    ];

    /**
     * @var \Gett\MyparcelBE\Module\ModuleService
     */
    private $moduleService;

    public function __construct()
    {
        $this->name          = self::MODULE_NAME;
        $this->tab           = 'shipping_logistics';
        $this->version       = $this->getVersion();
        $this->author        = 'MyParcel';
        $this->need_instance = 1;
        $this->bootstrap     = true;

        parent::__construct();
        $this->moduleService = (new \Gett\MyparcelBE\Module\ModuleService($this, $this->context));

        if (! empty(Context::getContext()->employee->id)) {
            $this->baseUrlWithoutToken = $this->getBaseUrl(true);
            $this->baseUrl             = $this->getBaseUrl();
        }

        $this->displayName = $this->l('MyParcelBE');
        $this->description = $this->l('PrestaShop module which integrates with MyParcel NL');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->registerHook('displayAdminOrderMain');
    }

    /**
     * @param  bool $withoutToken
     *
     * @return string
     */
    public function getBaseUrl(bool $withoutToken = false): string
    {
        if (empty(Context::getContext()->employee->id)) {
            \Gett\MyparcelBE\Logger\FileLogger::addLog(
                'Unauthenticated user tried getting base url',
                FileLogger::WARNING
            );
            throw new RuntimeException('Not authenticated');
        }

        return Tools::appendQuery(
            $this->context->link->getAdminLink('AdminModules', ! $withoutToken),
            [
                'configure'   => $this->name,
                'tab_module'  => $this->tab,
                'module_name' => $this->name,
            ]
        );
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->moduleService->getContent($this);
    }

    /**
     * @return self
     */
    public static function getModule(): self
    {
        /**
         * @var self|false $module
         */
        $module = Module::getInstanceByName(self::MODULE_NAME);

        if (! $module) {
            throw new \PrestaShopBundle\Exception\InvalidModuleException('Failed to get module instance');
        }

        return $module;
    }

    /**
     * @return string
     */
    public function getModuleCountry(): string
    {
        return (strpos($this->name, 'be') !== false) ? 'BE' : 'NL';
    }

    /**
     * @param  \Cart $cart
     * @param  \int  $shippingCost
     *
     * @return float|int
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderShippingCost($cart, $shippingCost)
    {
        return $this->moduleService->getOrderShippingCost($cart, $shippingCost);
    }

    /**
     * @param  \Cart $params
     *
     * @return bool
     */
    public function getOrderShippingCostExternal($params): bool
    {
        return true;
    }

    public function getShippingOptions($id_carrier, $address)
    {
        $carrier = new Carrier($id_carrier);

        $taxRate = ($carrier->getTaxesRate($address) / 100) + 1;

        $includeTax      = ! Product::getTaxCalculationMethod((int) $this->context->cart->id_customer)
            && (int) Configuration::get('PS_TAX');
        $displayTaxLabel = (Configuration::get('PS_TAX') && ! Configuration::get('AEUC_LABEL_TAX_INC_EXC'));

        return [
            'tax_rate'          => ($includeTax) ? $taxRate : 1,
            'include_tax'       => $includeTax,
            'display_tax_label' => $displayTaxLabel,
        ];
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function install(): bool
    {
        return parent::install() && (new \Gett\MyparcelBE\Module\Installer())->install();
    }

    /**
     * @return bool
     */
    public function isBE(): bool
    {
        return $this->getModuleCountry() === 'BE';
    }

    /**
     * @return bool
     */
    public function isNL(): bool
    {
        return $this->getModuleCountry() === 'NL';
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall(): bool
    {
        return (new \Gett\MyparcelBE\Module\Uninstaller())->uninstall() && parent::uninstall();
    }

    /**
     * @return string
     */
    public function getModuleCountry(): string
    {
        return (strpos($this->name, 'be') !== false) ? 'BE' : 'NL';
    }

    /**
     * @return bool
     */
    public function isNL(): bool
    {
        return $this->getModuleCountry() === 'NL';
    }

    /**
     * @return bool
     */
    public function isBE(): bool
    {
        return $this->getModuleCountry() === 'BE';
    }

    /**
     * @param  class-string $class
     *
     * @return bool
     */
    public function upgrade(string $class): bool
    {
        /** @var \Gett\MyparcelBE\Module\Upgrade\AbstractUpgrade $upgrade */
        $upgrade = new $class($this);

        return $upgrade->execute();
    }

    /**
     * Get the package version from composer.json.
     *
     * @return string
     */
    private function getVersion(): string
    {
        $filename     = __DIR__ . '/composer.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }
}

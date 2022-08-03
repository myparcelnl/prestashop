<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use Gett\MyparcelBE\Boot;
use Gett\MyparcelBE\Database\CreateCarrierConfigurationTableMigration;
use Gett\MyparcelBE\Database\CreateDeliverySettingTableMigration;
use Gett\MyparcelBE\Database\CreateOrderLabelTableMigration;
use Gett\MyparcelBE\Database\CreateProductConfigurationTableMigration;
use Gett\MyparcelBE\Module\Concern\HasModuleInstall;
use Gett\MyparcelBE\Module\Concern\HasModuleUninstall;
use Gett\MyparcelBE\Module\Facade\ModuleService;
use Gett\MyparcelBE\Module\Hooks\CarrierHooks;
use Gett\MyparcelBE\Module\Hooks\DisplayAdminProductsExtra;
use Gett\MyparcelBE\Module\Hooks\DisplayBackOfficeHeader;
use Gett\MyparcelBE\Module\Hooks\FrontHooks;
use Gett\MyparcelBE\Module\Hooks\HasPdkRenderHooks;
use Gett\MyparcelBE\Module\Hooks\LegacyOrderPageHooks;
use Gett\MyparcelBE\Module\Hooks\OrderHooks;
use Gett\MyparcelBE\Module\Hooks\OrdersGridHooks;
use Gett\MyparcelBE\Module\Tools\Tools;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use PrestaShopBundle\Exception\InvalidModuleException;

defined('_PS_VERSION_') or exit();

require_once __DIR__ . '/vendor/autoload.php';

class MyParcelBE extends CarrierModule
{
    use CarrierHooks;
    use DisplayAdminProductsExtra;
    use DisplayBackOfficeHeader;
    use FrontHooks;
    use LegacyOrderPageHooks;
    use OrderHooks;
    use OrdersGridHooks;

    use HasModuleInstall;
    use HasModuleUninstall;

    use HasPdkRenderHooks;

    public const MODULE_NAME = 'myparcelbe';

    public $baseUrl;

    /**
     * @var int
     */
    public $id_carrier;

    /**
     * @var class-string<\Gett\MyparcelBE\Database\Migration>[]
     */
    public $migrations = [
        CreateProductConfigurationTableMigration::class,
        CreateCarrierConfigurationTableMigration::class,
        CreateOrderLabelTableMigration::class,
        CreateDeliverySettingTableMigration::class,
    ];

    /**
     * @throws \Throwable
     */
    public function __construct()
    {
        $this->name                   = self::MODULE_NAME;
        $this->tab                    = 'shipping_logistics';
        $this->version                = $this->getVersionFromComposer();
        $this->author                 = 'MyParcel';
        $this->need_instance          = 1;
        $this->bootstrap              = true;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        parent::__construct();
        $this->setupPdk();

        if (! empty(Context::getContext()->employee->id)) {
            $this->baseUrl = $this->getBaseUrl();
        }

        $this->displayName = $this->l('MyParcelBE');
        $this->description = $this->l('PrestaShop module which integrates with MyParcel NL');
    }

    /**
     * @return self
     */
    public static function getModule(): self
    {
        /** @var self|false $module */
        $module = Module::getInstanceByName(self::MODULE_NAME);

        if (! $module) {
            throw new InvalidModuleException('Failed to get module instance');
        }

        return $module;
    }

    /**
     * @param  bool $withoutToken
     *
     * @return string
     */
    public function getBaseUrl(bool $withoutToken = false): string
    {
        if (empty(Context::getContext()->employee->id)) {
            DefaultLogger::warning('Unauthenticated user tried getting base url');
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
        return ModuleService::getContent();
    }

    /**
     * @param  \Cart $params
     * @param  \int  $shipping_cost
     *
     * @return float|int
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        return ModuleService::getOrderShippingCost($params, $shipping_cost);
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

    /**
     * @param $carrierId
     * @param $address
     *
     * @return array
     */
    public function getShippingOptions($carrierId, $address): array
    {
        $carrier = new Carrier($carrierId);

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
        return parent::install() && $this->executeInstall();
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function uninstall(): bool
    {
        return $this->executeUninstall() && parent::uninstall();
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
     * @return string
     */
    private function getVersionFromComposer(): string
    {
        $filename     = __DIR__ . '/composer.json';
        $composerData = json_decode(file_get_contents($filename), true);

        return $composerData['version'];
    }

    /**
     * @return void
     * @throws \Throwable
     */
    private function setupPdk(): void
    {
        Boot::setupPdk($this);
    }
}

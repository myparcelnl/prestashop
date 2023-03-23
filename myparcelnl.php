<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Module\Concern\HasModuleInstall;
use MyParcelNL\PrestaShop\Module\Concern\HasModuleUninstall;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Module\Hooks\HasPdkRenderHooks;
use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use PrestaShopBundle\Exception\InvalidModuleException;

defined('_PS_VERSION_') or exit();

require_once __DIR__ . '/vendor/autoload.php';

class MyParcelNL extends CarrierModule
{
    //    use CarrierHooks;
    //    use DisplayAdminProductsExtra;
    //    use HasFrontendHooks;
    //    use LegacyOrderPageHooks;
    //    use OrderHooks;
    //    use OrdersGridHooks;

    use HasModuleInstall;
    use HasModuleUninstall;

    use HasPdkRenderHooks;

    /**
     * @deprecated
     */
    public const MODULE_NAME = 'myparcelnl';
    /**
     * @deprecated
     */
    public const TRANSLATION_DOMAIN = 'Modules.MyParcelNL.Admin';

    /**
     * * @deprecated
     */
    public $baseUrl;

    /**
     * @var int
     * @deprecated
     */
    public $id_carrier;

    /**
     * @var int
     */
    private $installSuccess = 1;

    /**
     * @throws \Throwable
     */
    public function __construct()
    {
        $name        = self::MODULE_NAME;
        $version     = $this->getVersionFromComposer();
        $displayName = $this->l('prestashop_module_name');

        $this->name          = $name;
        $this->version       = $version;
        $this->author        = 'MyParcel';
        $this->author_uri    = 'https://myparcel.nl';
        $this->need_instance = 1;
        $this->bootstrap     = true;
        $this->displayName   = $displayName;
        $this->description   = 'MyParcel';

        parent::__construct();

        PsPdkBootstrapper::boot(
            $name,
            $displayName,
            $version,
            $this->getLocalPath(),
            $this->getBaseUrl()
        );

        $this->tab                    = Pdk::get('moduleTabName');
        $this->ps_versions_compliancy = [
            'min' => Pdk::get('prestaShopVersionMin'),
            'max' => Pdk::get('prestaShopVersionMax'),
        ];
    }

    /**
     * @return self
     * @deprecated
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
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Throwable
     */
    public function install(): bool
    {
        return parent::install() && $this->executeInstall();
    }

    /**
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Throwable
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
        /** @var \MyParcelNL\PrestaShop\Module\Upgrade\AbstractUpgrade $upgrade */
        $upgrade = new $class($this);

        return $upgrade->execute();
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        // todo
        return $this->context->link->getAdminLink('AdminModules');
        //            [
        //                'configure'   => $this->name,
        //                'tab_module'  => $this->tab,
        //                'module_name' => $this->name,
        //            ]
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
}

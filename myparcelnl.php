<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Module\Hooks\HasPdkCheckoutHooks;
use MyParcelNL\PrestaShop\Module\Hooks\HasPdkProductHooks;
use MyParcelNL\PrestaShop\Module\Hooks\HasPdkRenderHooks;
use MyParcelNL\PrestaShop\Module\Hooks\HasPdkScriptHooks;
use MyParcelNL\PrestaShop\Module\Service\ModuleService;
use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use PrestaShopBundle\Exception\InvalidModuleException;

defined('_PS_VERSION_') or exit();

require_once __DIR__ . '/vendor/autoload.php';

final class MyParcelNL extends CarrierModule
{
    use HasPdkCheckoutHooks;
    use HasPdkProductHooks;
    use HasPdkRenderHooks;
    use HasPdkScriptHooks;

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
        $this->name          = 'myparcelnl';
        $this->version       = $this->getVersionFromComposer();
        $this->author        = 'MyParcel';
        $this->author_uri    = 'https://myparcel.nl';
        $this->need_instance = 1;
        $this->bootstrap     = true;
        $this->displayName   = 'MyParcel';
        $this->description   = 'MyParcel';

        parent::__construct();

        PsPdkBootstrapper::boot(
            $this->name,
            $this->displayName,
            $this->version,
            $this->getLocalPath(),
            $this->getBaseUrl()
        );

        $this->tab = Pdk::get('moduleTabName');

        $this->ps_versions_compliancy = [
            'min' => Pdk::get('prestaShopVersionMin'),
            'max' => Pdk::get('prestaShopVersionMax'),
        ];
    }

    /**
     * @return self
     * @deprecated use Pdk::get('moduleInstance')
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
        /** @var \MyParcelNL\PrestaShop\Module\Service\ModuleService $moduleService */
        $moduleService = Pdk::get(ModuleService::class);

        return $moduleService->getOrderShippingCost($params, $shipping_cost);
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
     */
    public function install(): bool
    {
        $success = parent::install();

        try {
            Installer::install();
        } catch (Throwable $e) {
            $this->_errors[] = $e->getMessage();
            $success         = false;
        }

        return $success;
    }

    /**
     * @return bool
     */
    public function uninstall(): bool
    {
        $success = parent::uninstall();

        try {
            Installer::uninstall();
        } catch (Throwable $e) {
            $this->_errors[] = $e->getMessage();
            $success         = false;
        }

        return $success;
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

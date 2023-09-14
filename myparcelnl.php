<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Pdk as PdkInstance;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Hooks\HasPdkCheckoutDeliveryOptionsHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkCheckoutHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkOrderHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkProductHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkRenderHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkScriptHooks;
use MyParcelNL\PrestaShop\Hooks\HasPsCarrierHooks;
use MyParcelNL\PrestaShop\Service\ModuleService;
use PrestaShopBundle\Exception\InvalidModuleException;
use function MyParcelNL\PrestaShop\bootPdk;

defined('_PS_VERSION_') or exit();

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @final
 */
class MyParcelNL extends CarrierModule
{
    use HasPdkCheckoutDeliveryOptionsHooks;
    use HasPdkCheckoutHooks;
    use HasPdkOrderHooks;
    use HasPdkProductHooks;
    use HasPdkRenderHooks;
    use HasPdkScriptHooks;
    use HasPsCarrierHooks;

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
        // Suppress deprecation warning from Pdk HasAttributes
        // todo: find a better solution
        error_reporting(error_reporting() & ~E_DEPRECATED);

        $this->name          = 'myparcelnl';
        $this->version       = $this->getVersionFromComposer();
        $this->author        = 'MyParcel';
        $this->author_uri    = 'https://myparcel.nl';
        $this->need_instance = 1;
        $this->bootstrap     = true;
        $this->displayName   = 'MyParcel';
        $this->description   = 'MyParcel';

        parent::__construct();

        bootPdk(
            $this->name,
            $this->displayName,
            $this->version,
            $this->getLocalPath(),
            $this->getBaseUrl(),
            _PS_MODE_DEV_ ? PdkInstance::MODE_DEVELOPMENT : PdkInstance::MODE_PRODUCTION
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
     * Redirects the "configure" button in the module list to the settings page.
     *
     * @return string
     * @see \MyParcelNL\PrestaShop\Controller\SettingsController
     */
    public function getContent(): string
    {
        $link = $this->context->link->getAdminLink(Pdk::get('legacyControllerSettings'));

        Tools::redirectAdmin($link);

        return '';
    }

    /**
     * @param  \Cart $params
     * @param  \int  $shipping_cost
     *
     * @return float|int
     */
    public function getOrderShippingCost($params, $shipping_cost)
    {
        /** @var \MyParcelNL\PrestaShop\Service\ModuleService $moduleService */
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
            Installer::install($this);
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
        $success = true;

        try {
            Installer::uninstall($this);
        } catch (Throwable $e) {
            $this->_errors[] = $e->getMessage();
            $success         = false;
        }

        return parent::uninstall() && $success;
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

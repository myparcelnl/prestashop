<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Pdk as PdkInstance;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Hooks\HasPdkCheckoutDeliveryOptionsHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkCheckoutHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkOrderGridHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkOrderHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkProductHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkRenderHooks;
use MyParcelNL\PrestaShop\Hooks\HasPdkScriptHooks;
use MyParcelNL\PrestaShop\Hooks\HasPsCarrierHooks;
use MyParcelNL\PrestaShop\Hooks\HasPsShippingCostHooks;
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
    use HasPdkOrderGridHooks;
    use HasPdkOrderHooks;
    use HasPdkProductHooks;
    use HasPdkRenderHooks;
    use HasPdkScriptHooks;
    use HasPsCarrierHooks;
    use HasPsShippingCostHooks;

    /**
     * @var bool
     */
    private $hasPdk;

    /**
     * @throws \Throwable
     */
    public function __construct()
    {
        // Suppress deprecation warning from Pdk HasAttributes
        // todo: find a better solution
        error_reporting(error_reporting() & ~E_DEPRECATED);

        $this->name                   = 'myparcelnl';
        $this->version                = $this->getVersionFromComposer();
        $this->author                 = 'MyParcel';
        $this->author_uri             = 'https://myparcel.nl';
        $this->need_instance          = 1;
        $this->bootstrap              = true;
        $this->displayName            = 'MyParcelNL';
        $this->description            = 'MyParcel';
        $this->tab                    = 'shipping_logistics';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => '8.2.0'];

        parent::__construct();

        $this->withErrorHandling(
            [$this, 'setup'],
            sprintf('Failed to instantiate %s', $this->displayName),
            function () {
                $this->hasPdk = false;

                if ($this->active) {
                    $this->disable();
                }
            }
        );
    }

    /**
     * @param  bool $forceAll
     *
     * @return bool
     */
    public function enable($forceAll = false): bool
    {
        return parent::enable($forceAll) && $this->withErrorHandling([MyParcelModule::class, 'registerHooks']);
    }

    /**
     * Redirects the "configure" button in the module list to the settings page.
     *
     * @return string
     * @see \MyParcelNL\PrestaShop\Controller\SettingsController
     */
    public function getContent(): string
    {
        if (! $this->hasPdk) {
            return '';
        }

        $link = $this->context->link->getAdminLink(Pdk::get('legacyControllerSettings'));

        Tools::redirectAdmin($link);

        return '';
    }

    /**
     * @return bool
     */
    public function install(): bool
    {
        if (! $this->hasPdk) {
            return false;
        }

        return parent::install()
            && $this->withErrorHandling(function () {
                Installer::install($this);
            });
    }

    /**
     * @return bool
     */
    public function uninstall(): bool
    {
        if (! $this->hasPdk) {
            return parent::uninstall();
        }

        return $this->withErrorHandling(function () {
                Installer::uninstall($this);
            })
            && parent::uninstall();
    }

    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return $this->context->link->getAdminLink('AdminModules');
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
     * @throws \Exception
     */
    private function setup(): void
    {
        bootPdk(
            $this->name,
            $this->displayName,
            $this->version,
            $this->getLocalPath(),
            $this->getBaseUrl(),
            _PS_MODE_DEV_ ? PdkInstance::MODE_DEVELOPMENT : PdkInstance::MODE_PRODUCTION
        );

        $this->hasPdk = true;
    }

    /**
     * @param  callable      $callback
     * @param  null|string   $message
     * @param  null|callable $failureCallback
     *
     * @return bool
     */
    private function withErrorHandling(
        callable  $callback,
        ?string   $message = null,
        ?callable $failureCallback = null
    ): bool {
        try {
            $callback();

            return true;
        } catch (Throwable $e) {
            if ($this->hasPdk) {
                Logger::error("An error occurred: {$e->getMessage()}", ['exception' => $e->getTraceAsString()]);
            }

            $formattedMessage = sprintf(
                "%s: %s\n\nStack trace:\n%s",
                $message ?? 'Error',
                $e->getMessage(),
                $e->getTraceAsString()
            );

            PrestaShopLogger::addLog($formattedMessage, PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR);

            $this->_errors[] = str_replace("\n", '<br>', $formattedMessage);

            if ($failureCallback) {
                $failureCallback($e);
            }

            return false;
        }
    }
}

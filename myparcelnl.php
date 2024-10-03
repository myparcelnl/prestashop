<?php
/** @noinspection AutoloadingIssuesInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\FileSystemInterface;
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
     * @throws \JsonException
     */
    public function __construct()
    {
        // Suppress deprecation warning from Pdk HasAttributes
        // todo: find a better solution
        error_reporting(error_reporting() & ~E_DEPRECATED);

        $this->name                   = 'myparcelnl';
        $this->version                = self::getVersionFromComposer();
        $this->author                 = 'MyParcel';
        $this->author_uri             = 'https://myparcel.nl';
        $this->need_instance          = 1;
        $this->bootstrap              = true;
        $this->displayName            = 'MyParcelNL';
        $this->description            = 'MyParcel';
        $this->tab                    = 'shipping_logistics';
        $this->ps_versions_compliancy = ['min' => '1.7.6', 'max' => '8.2.0'];

        $this->registerTabs();

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
     * @return string
     * @throws \JsonException
     */
    protected static function getVersionFromComposer(): string
    {
        $filename     = __DIR__ . '/composer.json';
        $composerData = json_decode(file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);

        return $composerData['version'];
    }

    /**
     * @param  string $moduleName
     * @param  string $moduleVersion
     * @param  string $registeredVersion
     *
     * @return bool
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    protected static function loadUpgradeVersionList($moduleName, $moduleVersion, $registeredVersion)
    {
        try {
            // Trigger pdk setup to use facades
            new MyParcelNL();

            self::writeUpgradeFile();
        } catch (Throwable $e) {
            Logger::error("Failed to write upgrade file: {$e->getMessage()}", [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);

            return false;
        }

        return parent::loadUpgradeVersionList($moduleName, $moduleVersion, $registeredVersion);
    }

    /**
     * When the module is upgraded, PrestaShop checks to see if upgrade files exist. We need every update ever to
     * trigger MyParcelModule::install(). So, whenever PrestaShop checks our module for upgrade files, write a new
     * upgrade file for the current version to trigger the install method.
     *
     * @return void
     * @throws \JsonException
     */
    private static function writeUpgradeFile(): void
    {
        /** @var \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);

        $version = str_replace('-', '_', static::getVersionFromComposer());
        $content = '<?php function upgrade_module___VERSION__($module): bool { return \\MyParcelNL\\PrestaShop\\Facade\\MyParcelModule::install($module); }';

        $upgradeDir = sprintf('%s/upgrade', __DIR__);

        $fileSystem->mkdir($upgradeDir, true);

        $fileSystem->put(
            sprintf('%s/upgrade-%s.php', $upgradeDir, $version),
            strtr($content, [
                '__VERSION__' => str_replace(['.', '-'], '_', $version),
            ])
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
     * For some reason the cache is cleared halfway throughout the upgrade process when running it via the CLI.
     * PrestaShop then proceeds to throw errors because these properties are not set.
     *
     * @return array
     */
    public function runUpgradeModule(): array
    {
        $upgrade = &static::$modules_cache[$this->name]['upgrade'];

        $upgrade['success']             ??= false;
        $upgrade['available_upgrade']   ??= 0;
        $upgrade['number_upgraded']     ??= 0;
        $upgrade['number_upgrade_left'] ??= 0;
        $upgrade['upgrade_file_left']   ??= [];
        $upgrade['version_fail']        ??= 0;
        $upgrade['upgraded_from']       ??= 0;
        $upgrade['upgraded_to']         ??= 0;

        return parent::runUpgradeModule();
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
     * @return void
     */
    private function registerTabs(): void
    {
        $translatedName = [];

        foreach (Language::getLanguages() as $lang) {
            $translatedName[$lang['locale']] = $this->displayName;
        }

        $this->tabs = [
            [
                'name'              => $translatedName,
                'route_name'        => "{$this->name}_settings",
                'class_name'        => MyParcelNLAdminSettingsController::class,
                'visible'           => true,
                'parent_class_name' => $this->tab,
            ],
        ];
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
                $previous = $e->getPrevious();

                $context = [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTrace(),
                ];

                if ($previous) {
                    $context['previous'] = [
                        'message' => $previous->getMessage(),
                        'file'    => $previous->getFile(),
                        'line'    => $previous->getLine(),
                        'trace'   => $previous->getTrace(),
                    ];
                }

                Logger::error($e->getMessage(), $context);
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

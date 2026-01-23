<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use Carrier as PsCarrier;
use Module;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException;
use Tools;

final class PsInstallerService extends InstallerService
{
    private const VERSION_PRE_PDK = '1.999.0';

    /**
     * @var \MyParcelNL
     */
    private $module;

    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface
     */
    private $psCarrierService;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface $migrationService
     * @param  \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface        $psCarrierService
     */
    public function __construct(
        PdkSettingsRepositoryInterface $settingsRepository,
        MigrationServiceInterface      $migrationService,
        PsCarrierServiceInterface      $psCarrierService
    ) {
        parent::__construct($settingsRepository, $migrationService);
        $this->psCarrierService = $psCarrierService;
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function install(...$args): void
    {
        $this->setModule($args);
        parent::install($args);
    }

    /**
     * @param  array $args
     *
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function setModule(array $args): void
    {
        if (! $args[0] instanceof Module) {
            throw new InstallationException('Invalid module instance');
        }

        $this->module = $args[0];
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function uninstall(...$args): void
    {
        $this->setModule($args);
        parent::uninstall($args);
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    protected function executeInstallation(...$args): void
    {
        MyParcelModule::registerHooks();

        $this->installDatabase();

        parent::executeInstallation();
        Tools::clearSf2Cache();
    }

    /**
     * @param ...$args
     *
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    protected function executeUninstallation(...$args): void
    {
        parent::executeUninstallation($args);

        $this->uninstallCarriers();
        $this->uninstallHooks();

        // Delete account manually because prestashop removes config values on uninstall
        /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $accountRepository */
        $accountRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
        $accountRepository->storeSettings(new AccountSettings());

        Actions::execute(PdkBackendActions::UPDATE_ACCOUNT, ['mode' => Pdk::get('updateAccountModeUninstall')]);
    }

    /**
     * Get the pdk installed version. If it does not exist yet, check if the api key is set. This is necessary because PrestaShop does not properly keep track of the installed version. Not even when upgrading, so we have to do it ourselves.
     *
     * @return null|string
     */
    protected function getInstalledVersion(): ?string
    {
        $installedVersion = parent::getInstalledVersion();

        if ($installedVersion) {
            return $installedVersion;
        }

        /** @var PsConfigurationServiceInterface $configuration */
        $configuration = Pdk::get(PsConfigurationServiceInterface::class);

        if ($configuration->get('MYPARCELNL_API_KEY')) {
            return self::VERSION_PRE_PDK;
        }

        return null;
    }

    /**
     * @param  string $version
     */
    protected function migrateUp(string $version): void
    {
        parent::migrateUp($version);

        /**
         * Always register hooks, since the methods may have changed. PrestaShop checks if hook is already registered.
         */
        MyParcelModule::registerHooks();
    }

    private function installDatabase(): void
    {
        foreach (Pdk::get('databaseMigrationClasses') as $migration) {
            /** @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $instance */
            $instance = Pdk::get($migration);
            $instance->up();
        }
    }

    /**
     * @return void
     */
    private function uninstallCarriers(): void
    {
        $result = $this->psCarrierService
            ->getPsCarriers()
            ->filter(function (PsCarrier $carrier) {
                return $carrier->external_module_name === $this->module->name;
            });

        $this->psCarrierService->deleteMany($result, true);
    }

    /**
     * @return void
     */
    private function uninstallHooks(): void
    {
        foreach (Pdk::get('moduleHooks') as $hook) {
            if ($this->module->unregisterHook($hook)) {
                continue;
            }

            Logger::error(sprintf('Hook %s could not be unregistered.', $hook));
        }
    }
}

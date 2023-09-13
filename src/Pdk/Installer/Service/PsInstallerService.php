<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use Carrier;
use Context;
use Db;
use Language;
use Module;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Database\DatabaseMigrations;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException;
use Tab;

final class PsInstallerService extends InstallerService
{
    /**
     * @var \MyParcelNL
     */
    private $module;

    /**
     * @param ...$args
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
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    protected function executeInstallation(...$args): void
    {
        MyParcelModule::registerHooks();
        $this->installDatabase();
        $this->installTabs();

        parent::executeInstallation();
    }

    /**
     * @param ...$args
     *
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function executeUninstallation(...$args): void
    {
        parent::executeUninstallation($args);

        $this->uninstallCarriers();
        $this->uninstallHooks();
        $this->uninstallTabs();

        // Delete account manually because prestashop removes config values on uninstall
        /** @var PdkAccountRepositoryInterface $accountRepository */
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);
        $accountRepository->store(null);
    }

    /**
     * @return null|string
     */
    protected function getInstalledVersion(): ?string
    {
        return $this->module->database_version ?? parent::getInstalledVersion();
    }

    /**
     * @param  string $version
     */
    protected function migrateUp(string $version): void
    {
        parent::migrateUp($version);

        /**
         * Always register hooks, since the methods may have changed. PrestaShops checks if hook is already registered.
         */
        MyParcelModule::registerHooks();
    }

    private function installDatabase(): void
    {
        /** @var \MyParcelNL\PrestaShop\Database\DatabaseMigrations $migrations */
        $migrations = Pdk::get(DatabaseMigrations::class);

        foreach ($migrations->get() as $migration) {
            /** @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $instance */
            $instance = Pdk::get($migration);

            $instance->up();
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    private function installTabs(): void
    {
        $languages = array_fill_keys(array_column(Language::getLanguages(), 'id_lang'), Pdk::getAppInfo()->title);

        /** @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
        $tabRepository = Pdk::get('ps.tabRepository');

        $tab = new Tab();

        $tab->active     = 1;
        $tab->class_name = 'AdminMyParcelNL';
        $tab->name       = $languages;
        $tab->id_parent  = $tabRepository->findOneIdByClassName('AdminParentShipping');
        $tab->module     = $this->module->name;

        if (! $tab->add()) {
            throw new InstallationException('Failed to add tab');
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function uninstallCarriers(): void
    {
        $carriers = Carrier::getCarriers(Context::getContext()->language->id);
        $result   = true;

        /** @var \Carrier $carrier */
        foreach ($carriers as $carrier) {
            if ($carrier['external_module_name'] !== $this->module->name) {
                continue;
            }

            $result &= $carrier->softDelete();
        }

        if (! $result) {
            throw new InstallationException('Failed to delete carriers');
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    private function uninstallHooks(): void
    {
        foreach (Pdk::get('moduleHooks') as $hook) {
            if ($this->module->unregisterHook($hook)) {
                continue;
            }

            throw new InstallationException(sprintf('Hook %s could not be unregistered.', $hook));
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    private function uninstallTabs(): void
    {
        $result = Db::getInstance()
            ->delete('tab', sprintf('module = "%s"', pSQL($this->module->name)));

        if (! $result) {
            throw new InstallationException('Failed to delete module tabs');
        }
    }
}

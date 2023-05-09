<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Installer;

use _HumbugBoxcbe25c660cef\Symfony\Component\Console\Exception\RuntimeException;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Installer\InstallerService;
use MyParcelNL\PrestaShop\Database\DatabaseMigrations;

final class PsInstallerService extends InstallerService
{
    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    protected function executeInstallation(): void
    {
        $this->executeDatabaseMigrations();
        $this->installCarriers();

        $this->registerHooks();
        // TODO
        // $this->installTabs();

        parent::executeInstallation();
    }

    private function executeDatabaseMigrations(): void
    {
        /** @var \MyParcelNL\PrestaShop\Database\DatabaseMigrations $migrations */
        $migrations = Pdk::get(DatabaseMigrations::class);

        foreach ($migrations as $migration) {
            /** @var \MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationInterface $instance */
            $instance = Pdk::get($migration);

            $instance->up();
            DefaultLogger::debug('Executed migration', ['migration' => $migration]);
        }
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    private function installCarriers(): void
    {
        /** @var \MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService $service */
        $service = Pdk::get(PsPdkUpgradeService::class);

        $service->createPsCarriers();
    }

    private function registerHooks(): void
    {
        /** @var  \MyParcelNL $module */
        $module = Pdk::get('moduleInstance');

        foreach (Pdk::get('moduleHooks') as $hook) {
            $result = $module->registerHook($hook);

            if (! $result) {
                throw new RuntimeException(sprintf('Hook %s could not be registered.', $hook));
            }
        }
    }
}

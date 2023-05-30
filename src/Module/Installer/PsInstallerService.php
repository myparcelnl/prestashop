<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Installer;

use Db;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Database\DatabaseMigrations;
use RuntimeException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PsInstallerService extends InstallerService
{
    /**
     * @return void
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     * @throws \PrestaShopException|\Doctrine\Common\Annotations\AnnotationException
     */
    protected function executeInstallation(): void
    {
        $this->prepareEntityManager();

        $this->executeDatabaseMigrations();
        $this->installCarriers();

        $this->registerHooks();
        // TODO
        // $this->installTabs();

        parent::executeInstallation();
    }

    /**
     * @return string
     */
    protected function getInstalledVersion(): string
    {
        $table = _DB_PREFIX_ . 'module';
        $name  = Pdk::getAppInfo()->name;

        return Db::getInstance()
            ->getValue("SELECT version from $table WHERE name='$name';") ?: '';
    }

    /**
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function migrateUp(string $version): void
    {
        $this->prepareEntityManager();

        parent::migrateUp($version);

        /**
         * Always register hooks, since the methods may have changed. PrestaShops checks if hook is already registered.
         */
        $this->registerHooks();
    }

    private function executeDatabaseMigrations(): void
    {
        /** @var \MyParcelNL\PrestaShop\Database\DatabaseMigrations $migrations */
        $migrations = Pdk::get(DatabaseMigrations::class)
            ->get();

        foreach ($migrations as $migration) {
            /** @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $instance */
            $instance = Pdk::get($migration);

            $instance->up();
            Logger::debug('Executed migration', ['migration' => $migration]);
        }
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     * @throws \PrestaShopException
     */
    private function installCarriers(): void
    {
        /** @var \MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService $service */
        $service = Pdk::get(PsPdkUpgradeService::class);

        $service->createPsCarriers();
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function prepareEntityManager(): void
    {
        $appInfo = Pdk::getAppInfo();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = Pdk::get('ps.entityManager');

        $driverChain = $entityManager
            ->getConfiguration()
            ->getMetadataDriverImpl();

        $docParser = new DocParser();
        $reader    = new AnnotationReader($docParser);
        $reader    = new PsrCachedReader($reader, new ArrayAdapter());

        $driver = new AnnotationDriver($reader, ["{$appInfo->path}src/Entity"]);

        if ($driverChain instanceof MappingDriverChain) {
            $driverChain->addDriver($driver, 'MyParcelNL\PrestaShop\Entity');
        }
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

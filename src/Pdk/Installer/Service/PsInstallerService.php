<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use Context;
use Currency;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Language;
use Module;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tab;
use Tools;

final class PsInstallerService extends InstallerService
{
    /**
     * @var \MyParcelNL
     */
    private $module;

    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface
     */
    private $psCarrierService;

    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface
     */
    private $psObjectModelService;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface    $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface $migrationService
     * @param  \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface        $psCarrierService
     * @param  \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface    $psObjectModelService
     */
    public function __construct(
        SettingsRepositoryInterface   $settingsRepository,
        MigrationServiceInterface     $migrationService,
        PsCarrierServiceInterface     $psCarrierService,
        PsObjectModelServiceInterface $psObjectModelService
    ) {
        parent::__construct($settingsRepository, $migrationService);
        $this->psCarrierService     = $psCarrierService;
        $this->psObjectModelService = $psObjectModelService;
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function install(...$args): void
    {
        $this->setModule($args);
        $this->preparePrestaShop();
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function uninstall(...$args): void
    {
        $this->setModule($args);
        $this->preparePrestaShop();
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
        $this->installTabs();

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

        Tools::clearSf2Cache();
    }

    /**
     * @param  null|string $version
     *
     * @return void
     */
    protected function updateInstalledVersion(?string $version): void
    {
        // do nothing
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
    private function installTabs(): void
    {
        /** @var \PrestaShopBundle\Entity\Repository\TabRepository $tabRepository */
        $tabRepository = Pdk::get('ps.tabRepository');

        $existing = $tabRepository->findOneByClassName(Pdk::get('legacyControllerSettings'));
        $tab      = $existing ?? $this->psObjectModelService->create(Tab::class);

        $tab->active     = 1;
        $tab->class_name = Pdk::get('legacyControllerSettings');
        $tab->route_name = Pdk::get('routeNameSettings');
        $tab->name       = array_fill_keys(array_column(Language::getLanguages(), 'id_lang'), Pdk::getAppInfo()->title);
        $tab->id_parent  = $tabRepository->findOneIdByClassName(Pdk::get('sidebarParentClass'));
        $tab->module     = $this->module->name;

        $this->psObjectModelService->updateOrAdd($tab);
    }

    /**
     * PrestaShop throws an error during install because context->currency is undefined.
     *
     * @return void
     * @todo See if this can be done in a better way (preferably not at all)
     */
    private function prepareContext(): void
    {
        /** @var \Context $context */
        $context = Context::getContext();

        $context->currency = $context->currency ?? $this->psObjectModelService->create(Currency::class, 1);
    }

    /**
     * @return void
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

    /**
     * Do some preparations that are missing in the installation flow of PrestaShop.
     *
     * @return void
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function preparePrestaShop(): void
    {
        $this->prepareContext();
        $this->prepareEntityManager();
    }

    /**
     * @return void
     */
    private function uninstallCarriers(): void
    {
        $result = $this->psCarrierService
            ->getPsCarriers()
            ->filter(function (array $carrier) {
                return $carrier['external_module_name'] === $this->module->name;
            });

        $this->psCarrierService->deleteMany($result);
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
     */
    private function uninstallTabs(): void
    {
        $moduleTabs = Tab::getCollectionFromModule($this->module->name);

        $this->psObjectModelService->deleteMany(Tab::class, $moduleTabs);
    }
}

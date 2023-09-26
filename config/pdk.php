<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Configuration\Service\Ps17ConfigurationService;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Database\CreateCarrierMappingTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateCartDeliveryOptionsTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateOrderShipmentTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateProductSettingsTableDatabaseMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkDeliveryOptionsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkProductSettingsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkSettingsMigration;
use MyParcelNL\PrestaShop\Pdk\Account\Repository\PsPdkAccountRepository;
use MyParcelNL\PrestaShop\Pdk\Action\Backend\Account\PsUpdateAccountAction;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Api\Service\PsBackendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Api\Service\PsFrontendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsCronService;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use MyParcelNL\PrestaShop\Pdk\Cart\Repository\PsPdkCartRepository;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsFrontendRenderService;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsScriptService;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsViewService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsInstallerService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsMigrationService;
use MyParcelNL\PrestaShop\Pdk\Language\Service\PsLanguageService;
use MyParcelNL\PrestaShop\Pdk\Logger\PsLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderNoteRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Service\PsOrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Pdk\ShippingMethod\Repository\PsShippingMethodRepository;
use MyParcelNL\PrestaShop\Pdk\Tax\Service\PsTaxService;
use MyParcelNL\PrestaShop\Pdk\Webhook\Repository\PsWebhooksRepository;
use MyParcelNL\PrestaShop\Pdk\Webhook\Service\PsWebhookService;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;
use MyParcelNL\PrestaShop\Router\Service\PsRouterService;
use MyParcelNL\PrestaShop\Service\PsCarrierService;
use MyParcelNL\PrestaShop\Service\PsOrderService;
use Psr\Log\LoggerInterface;
use function DI\get;
use function DI\value;

return [
    'defaultCutoffTime'        => value('17:00'),
    'defaultCutoffTimeSameDay' => value('10:00'),

    /**
     * Migrations
     */

    'pdkMigrationVersion' => value('2.0.0-alpha.0'),

    'databaseMigrationClasses' => value([
        CreateCarrierMappingTableDatabaseMigration::class,
        CreateCartDeliveryOptionsTableDatabaseMigration::class,
        CreateOrderDataTableDatabaseMigration::class,
        CreateOrderShipmentTableDatabaseMigration::class,
        CreateProductSettingsTableDatabaseMigration::class,
    ]),

    'pdkMigrationClasses'                       => value([
        PdkSettingsMigration::class,
        PdkProductSettingsMigration::class,
        PdkDeliveryOptionsMigration::class,
        PdkOrderShipmentsMigration::class,
    ]),

    /**
     * Repositories
     */
    PdkAccountRepositoryInterface::class        => get(PsPdkAccountRepository::class),
    PdkCartRepositoryInterface::class           => get(PsPdkCartRepository::class),
    PdkOrderNoteRepositoryInterface::class      => get(PsPdkOrderNoteRepository::class),
    PdkOrderRepositoryInterface::class          => get(PsPdkOrderRepository::class),
    PdkProductRepositoryInterface::class        => get(PdkProductRepository::class),
    PdkShippingMethodRepositoryInterface::class => get(PsShippingMethodRepository::class),
    SettingsRepositoryInterface::class          => get(PdkSettingsRepository::class),

    /**
     * Services
     */
    CronServiceInterface::class                 => get(PsCronService::class),
    FrontendRenderServiceInterface::class       => get(PsFrontendRenderService::class),
    LanguageServiceInterface::class             => get(PsLanguageService::class),
    OrderStatusServiceInterface::class          => get(PsOrderStatusService::class),
    TaxServiceInterface::class                  => get(PsTaxService::class),
    ViewServiceInterface::class                 => get(PsViewService::class),
    WeightServiceInterface::class               => get(PsWeightService::class),

    /**
     * Endpoints
     */
    FrontendEndpointServiceInterface::class     => get(PsFrontendEndpointService::class),
    BackendEndpointServiceInterface::class      => get(PsBackendEndpointService::class),

    ConfigurationServiceInterface::class  => get(Ps17ConfigurationService::class),

    /**
     * Webhooks
     */
    PdkWebhookServiceInterface::class     => get(PsWebhookService::class),
    PdkWebhooksRepositoryInterface::class => get(PsWebhooksRepository::class),

    /**
     * Actions
     */
    UpdateAccountAction::class            => get(PsUpdateAccountAction::class),

    /**
     * Miscellaneous
     */
    ClientAdapterInterface::class         => get(Guzzle7ClientAdapter::class),
    InstallerServiceInterface::class      => get(PsInstallerService::class),
    LoggerInterface::class                => get(PsLogger::class),
    MigrationServiceInterface::class      => get(PsMigrationService::class),
    ScriptServiceInterface::class         => get(PsScriptService::class),

    /**
     * Custom services
     */
    PsCarrierServiceInterface::class      => get(PsCarrierService::class),
    PsOrderServiceInterface::class        => get(PsOrderService::class),
    PsRouterServiceInterface::class       => get(PsRouterService::class),
];

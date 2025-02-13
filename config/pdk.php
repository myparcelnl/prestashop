<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Platform;
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
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Service\AuditService;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Configuration\Service\Ps17PsConfigurationService;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsCountryServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Database\CreateCarrierMappingTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateCartDeliveryOptionsTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateOrderShipmentTableDatabaseMigration;
use MyParcelNL\PrestaShop\Database\CreateProductSettingsTableDatabaseMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkCarrierMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkDeliveryOptionsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkProductSettingsMigration;
use MyParcelNL\PrestaShop\Migration\Pdk\PdkSettingsMigration;
use MyParcelNL\PrestaShop\Pdk\Account\Repository\PsPdkAccountRepository;
use MyParcelNL\PrestaShop\Pdk\Action\Backend\Account\PsUpdateAccountAction;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle5ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Api\Service\PsBackendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Api\Service\PsFrontendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Audit\Repository\PsPdkAuditRepository;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsCronService;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use MyParcelNL\PrestaShop\Pdk\Cart\Repository\PsPdkCartRepository;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsFrontendRenderService;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsViewService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsInstallerService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsMigrationService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsPreInstallService;
use MyParcelNL\PrestaShop\Pdk\Language\Service\PsLanguageService;
use MyParcelNL\PrestaShop\Pdk\Logger\PsLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderNoteRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Service\PsOrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PsPdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository;
use MyParcelNL\PrestaShop\Pdk\ShippingMethod\Repository\PsShippingMethodRepository;
use MyParcelNL\PrestaShop\Pdk\Tax\Service\PsTaxService;
use MyParcelNL\PrestaShop\Pdk\Webhook\Repository\PsWebhooksRepository;
use MyParcelNL\PrestaShop\Pdk\Webhook\Service\PsWebhookService;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;
use MyParcelNL\PrestaShop\Router\Service\Ps17RouterService;
use MyParcelNL\PrestaShop\Router\Service\Ps8RouterService;
use MyParcelNL\PrestaShop\Script\Service\PsScriptService;
use MyParcelNL\PrestaShop\Service\PsCarrierService;
use MyParcelNL\PrestaShop\Service\PsCountryService;
use MyParcelNL\PrestaShop\Service\PsObjectModelService;
use MyParcelNL\PrestaShop\Service\PsOrderService;
use Psr\Log\LoggerInterface;
use function DI\factory;
use function DI\get;
use function DI\value;
use function MyParcelNL\PrestaShop\psVersionFactory;
use MyParcelNL\PrestaShop\Migration\Pdk\RemoveAuditTableMigration;

return [
    'defaultCutoffTime'        => value('17:00'),
    'defaultCutoffTimeSameDay' => value('10:00'),

    /**
     * Migrations
     */

    'pdkMigrationVersion' => value('4.0.0-alpha.0'),

    'databaseMigrationClasses' => value([
        CreateCarrierMappingTableDatabaseMigration::class,
        CreateCartDeliveryOptionsTableDatabaseMigration::class,
        CreateOrderDataTableDatabaseMigration::class,
        CreateOrderShipmentTableDatabaseMigration::class,
        CreateProductSettingsTableDatabaseMigration::class,
        RemoveAuditTableMigration::class,
    ]),

    'pdkMigrationClasses'                       => value([
        PdkCarrierMigration::class,
        PdkSettingsMigration::class,
        PdkProductSettingsMigration::class,
        PdkDeliveryOptionsMigration::class,
        PdkOrderShipmentsMigration::class,
    ]),

    /**
     * Repositories
     */
    PdkAccountRepositoryInterface::class        => get(PsPdkAccountRepository::class),
    PdkAuditRepositoryInterface::class          => get(PsPdkAuditRepository::class),
    PdkCartRepositoryInterface::class           => get(PsPdkCartRepository::class),
    PdkOrderNoteRepositoryInterface::class      => get(PsPdkOrderNoteRepository::class),
    PdkOrderRepositoryInterface::class          => get(PsPdkOrderRepository::class),
    PdkProductRepositoryInterface::class        => get(PsPdkProductRepository::class),
    PdkShippingMethodRepositoryInterface::class => get(PsShippingMethodRepository::class),
    PdkSettingsRepositoryInterface::class       => get(PsPdkSettingsRepository::class),

    /**
     * Services
     */
    AuditServiceInterface::class                => get(AuditService::class),
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

    /**
     * Webhooks
     */
    PdkWebhookServiceInterface::class           => get(PsWebhookService::class),
    PdkWebhooksRepositoryInterface::class       => get(PsWebhooksRepository::class),

    /**
     * Actions
     */
    UpdateAccountAction::class                  => get(PsUpdateAccountAction::class),

    /**
     * Miscellaneous
     */
    ClientAdapterInterface::class               => psVersionFactory([
        ['class' => Guzzle7ClientAdapter::class, 'version' => 8],
        ['class' => Guzzle5ClientAdapter::class],
    ]),

    LoggerInterface::class           => get(PsLogger::class),
    MigrationServiceInterface::class => get(PsMigrationService::class),
    ScriptServiceInterface::class    => get(PsScriptService::class),

    InstallerServiceInterface::class       => factory(function () {
        /** @var \MyParcelNL\PrestaShop\Pdk\Installer\Service\PsPreInstallService $preInstallService */
        $preInstallService = Pdk::get(PsPreInstallService::class);

        $preInstallService->prepare();

        return Pdk::get(PsInstallerService::class);
    }),

    /**
     * Custom services
     */
    PsConfigurationServiceInterface::class => get(Ps17PsConfigurationService::class),
    PsRouterServiceInterface::class        => psVersionFactory([
        ['class' => Ps8RouterService::class, 'version' => 8],
        ['class' => Ps17RouterService::class],
    ]),

    /**
     * Object model services
     */
    PsObjectModelServiceInterface::class   => get(PsObjectModelService::class),

    PsCarrierServiceInterface::class => get(PsCarrierService::class),
    PsCountryServiceInterface::class => get(PsCountryService::class),
    PsOrderServiceInterface::class   => get(PsOrderService::class),

    /**
     * Countries per platform and carrier.
     * This is intended as a temporary solution until we can use the Carrier Capabilities service. This is copied from the Delivery Options.
     *
     * @TODO Replace when Carrier Capabilities is available.
     */
    'countriesPerPlatformAndCarrier' => value([
        Platform::MYPARCEL_NAME => [
            Carrier::CARRIER_POSTNL_NAME             => [
                'deliveryCountries' => [
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_BE, // Belgium
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_SE, // Sweden
                    CountryCodes::CC_DE, // Germany
                ],
                'fakeDelivery'      => true,
            ],
            Carrier::CARRIER_DHL_FOR_YOU_NAME        => [
                'deliveryCountries' => [
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_BE, // Belgium
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_NL, // Netherlands
                ],
            ],
            Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME => [
                'deliveryCountries' => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_HR, // Croatia
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IE, // Ireland
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_HR, // Croatia
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_SE, // Sweden
                ],
            ],
            Carrier::CARRIER_DHL_EUROPLUS_NAME       => [
                'deliveryCountries' => [
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IE, // Ireland
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_HR, // Croatia
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_SE, // Sweden
                    CountryCodes::CC_GB, // United Kingdom
                ],
                'pickupCountries'   => [],
            ],
            Carrier::CARRIER_UPS_NAME                => [
                'deliveryCountries' => [
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IE, // Ireland
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_HR, // Croatia
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_CZ, // Czech Republic
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_DE, // Germany
                ],
                'fakeDelivery'      => true,
            ],
            Carrier::CARRIER_DPD_NAME                => [
                'deliveryCountries' => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IE, // Ireland
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LI, // Liechtenstein
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_SE, // Sweden
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_GB, // United Kingdom
                ],
            ],
        ],

        Platform::SENDMYPARCEL_NAME => [
            Carrier::CARRIER_BPOST_NAME  => [
                'deliveryCountries' => [
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_NL, // Netherlands
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_NL, // Netherlands
                ],
                'fakeDelivery'      => true,
            ],
            Carrier::CARRIER_POSTNL_NAME => [
                'deliveryCountries' => [
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_NL, // Netherlands
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_NL, // Netherlands
                ],
                'fakeDelivery'      => true,
            ],
            Carrier::CARRIER_DPD_NAME    => [
                'deliveryCountries' => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_BG, // Bulgaria
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_GR, // Greece
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_IE, // Ireland
                    CountryCodes::CC_IT, // Italy
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LI, // Liechtenstein
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_RO, // Romania
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_SE, // Sweden
                ],
                'pickupCountries'   => [
                    CountryCodes::CC_AT, // Austria
                    CountryCodes::CC_BE, // Belgium
                    CountryCodes::CC_CZ, // Czech Republic
                    CountryCodes::CC_DK, // Denmark
                    CountryCodes::CC_EE, // Estonia
                    CountryCodes::CC_FI, // Finland
                    CountryCodes::CC_FR, // France
                    CountryCodes::CC_DE, // Germany
                    CountryCodes::CC_HU, // Hungary
                    CountryCodes::CC_LV, // Latvia
                    CountryCodes::CC_LT, // Lithuania
                    CountryCodes::CC_LU, // Luxembourg
                    CountryCodes::CC_NL, // Netherlands
                    CountryCodes::CC_PL, // Poland
                    CountryCodes::CC_PT, // Portugal
                    CountryCodes::CC_SK, // Slovakia
                    CountryCodes::CC_SI, // Slovenia
                    CountryCodes::CC_ES, // Spain
                    CountryCodes::CC_GB, // United Kingdom
                ],
            ],
        ],
    ]),
];

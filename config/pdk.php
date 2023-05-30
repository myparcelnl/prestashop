<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Module\Installer\PsInstallerService;
use MyParcelNL\PrestaShop\Module\Installer\PsMigrationService;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle5ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Logger\PdkLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsCartRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Api\PsBackendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PdkAccountRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsShippingMethodRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsWebhooksRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\OrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsCronService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsFrontendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsScriptService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsViewService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsWebhookService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Service\LanguageService;
use MyParcelNL\PrestaShop\Pdk\Service\PsDeliveryOptionsService;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Service\Configuration\Ps17ConfigurationService;
use MyParcelNL\PrestaShop\Service\PsFrontendRenderService;
use MyParcelNL\PrestaShop\Service\PsTaxService;
use MyParcelNL\PrestaShop\Service\PsWeightService;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

return [
    'mode'                   => value(
        _PS_MODE_DEV_ ? Pdk::MODE_DEVELOPMENT : Pdk::MODE_PRODUCTION
    ),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion' => value('5.7.3'),

    'routeBackend'                              => value(Context::getContext()->link->getAdminBaseLink()),
    'routeBackendPdk'                           => value('pdk'),
    'routeBackendWebhook'                       => value('webhook'),

    /**
     * Repositories
     */
    AccountRepositoryInterface::class           => autowire(PdkAccountRepository::class),
    PdkCartRepositoryInterface::class           => autowire(PsCartRepository::class),
    PdkOrderRepositoryInterface::class          => autowire(PdkOrderRepository::class),
    PdkProductRepositoryInterface::class        => autowire(PdkProductRepository::class),
    PdkShippingMethodRepositoryInterface::class => autowire(PsShippingMethodRepository::class),
    SettingsRepositoryInterface::class          => autowire(PdkSettingsRepository::class),

    /**
     * Services
     */
    CronServiceInterface::class                 => autowire(PsCronService::class),
    DeliveryOptionsServiceInterface::class      => autowire(PsDeliveryOptionsService::class),
    FrontendRenderServiceInterface::class       => autowire(PsFrontendRenderService::class),
    LanguageServiceInterface::class             => autowire(LanguageService::class),
    OrderStatusServiceInterface::class          => autowire(OrderStatusService::class),
    TaxServiceInterface::class                  => autowire(PsTaxService::class),
    ViewServiceInterface::class                 => autowire(PsViewService::class),
    WeightServiceInterface::class               => autowire(PsWeightService::class),

    /**
     * Endpoints
     */
    FrontendEndpointServiceInterface::class     => autowire(PsFrontendEndpointService::class),
    BackendEndpointServiceInterface::class      => autowire(PsBackendEndpointService::class),

    ConfigurationServiceInterface::class  => autowire(Ps17ConfigurationService::class),

    /**
     * Webhooks
     */
    PdkWebhookServiceInterface::class     => autowire(PsWebhookService::class),
    PdkWebhooksRepositoryInterface::class => autowire(PsWebhooksRepository::class),

    /**
     * Miscellaneous
     */
    ClientAdapterInterface::class         => autowire(Guzzle5ClientAdapter::class),
    InstallerServiceInterface::class      => autowire(PsInstallerService::class),
    LoggerInterface::class                => autowire(PdkLogger::class),
    MigrationServiceInterface::class      => autowire(PsMigrationService::class),
    ScriptServiceInterface::class         => autowire(PsScriptService::class),
];

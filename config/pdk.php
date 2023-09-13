<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
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
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use MyParcelNL\PrestaShop\Pdk\Cart\Repository\PsPdkCartRepository;
use MyParcelNL\PrestaShop\Pdk\DeliveryOptions\Service\PsDeliveryOptionsService;
use MyParcelNL\PrestaShop\Pdk\Frontend\Service\PsFrontendRenderService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsInstallerService;
use MyParcelNL\PrestaShop\Pdk\Installer\Service\PsMigrationService;
use MyParcelNL\PrestaShop\Pdk\Language\Service\LanguageService;
use MyParcelNL\PrestaShop\Pdk\Logger\PsLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderNoteRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Api\PsBackendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsPdkAccountRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsShippingMethodRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsWebhooksRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\OrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsCronService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsFrontendEndpointService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsScriptService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsViewService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsWebhookService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Pdk\Tax\Service\PsTaxService;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;
use MyParcelNL\PrestaShop\Router\Service\PsRouterService;
use MyParcelNL\PrestaShop\Service\PsCarrierService;
use MyParcelNL\PrestaShop\Service\PsOrderService;
use Psr\Log\LoggerInterface;
use function DI\get;
use function DI\value;

return [
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
    DeliveryOptionsServiceInterface::class      => get(PsDeliveryOptionsService::class),
    FrontendRenderServiceInterface::class       => get(PsFrontendRenderService::class),
    LanguageServiceInterface::class             => get(LanguageService::class),
    OrderStatusServiceInterface::class          => get(OrderStatusService::class),
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

<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
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
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsViewService;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\PsWebhookService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Service\LanguageService;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Service\Configuration\Ps17ConfigurationService;
use MyParcelNL\PrestaShop\Service\PsRenderService;
use MyParcelNL\PrestaShop\Service\PsTaxService;
use MyParcelNL\PrestaShop\Service\PsWeightService;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

return [
    'mode'                                      => value(
        _PS_MODE_DEV_ ? Pdk::MODE_DEVELOPMENT : Pdk::MODE_PRODUCTION
    ),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion'                    => value('5.7.0'),

    /**
     * Only use carriers that we tested and we have a schema for, at the moment
     */
    'allowedCarriers'                           => value([
        'dhlforyou',
        'postnl',
    ]),

    /**
     * Carrier used when fallback is required
     */
    'defaultCarrier'                            => value('postnl'),

    /**
     * Repositories
     */
    AccountRepositoryInterface::class           => autowire(PdkAccountRepository::class),
    PdkOrderRepositoryInterface::class          => autowire(PdkOrderRepository::class),
    ProductRepositoryInterface::class           => autowire(PdkProductRepository::class),
    SettingsRepositoryInterface::class          => autowire(PdkSettingsRepository::class),
    PdkCartRepositoryInterface::class           => autowire(PsCartRepository::class),
    PdkShippingMethodRepositoryInterface::class => autowire(PsShippingMethodRepository::class),

    /**
     * Services
     */
    CronServiceInterface::class                 => autowire(PsCronService::class),
    LanguageServiceInterface::class             => autowire(LanguageService::class),
    OrderStatusServiceInterface::class          => autowire(OrderStatusService::class),
    ViewServiceInterface::class                 => autowire(PsViewService::class),
    WeightServiceInterface::class               => autowire(PsWeightService::class),
    TaxServiceInterface::class                  => autowire(PsTaxService::class),
    RenderServiceInterface::class               => autowire(PsRenderService::class),

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
    LoggerInterface::class                => autowire(PdkLogger::class),
    //    ScriptServiceInterface::class         => autowire(PsScriptService::class), //TODO
];


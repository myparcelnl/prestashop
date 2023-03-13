<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\Concern\WeightServiceInterface;
use MyParcelNL\Pdk\Base\CronServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Backend\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Frontend\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Repository\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle5ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Logger\PdkLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsCartRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\OrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Pdk\Service\LanguageService;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Service\Configuration\Ps17ConfigurationService;
use MyParcelNL\PrestaShop\Service\PsWeightService;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

return [
    'mode'                             => value(_PS_MODE_DEV_ ? Pdk::MODE_DEVELOPMENT : Pdk::MODE_PRODUCTION),

    /**
     * The version of the delivery options in the checkout.
     *
     * @see https://github.com/myparcelnl/delivery-options/releases
     */
    'deliveryOptionsVersion'           => value('5.7.0'),

    /**
     * Only use carriers that we tested and we have a schema for, at the moment
     */
    'allowedCarriers'                  => value([
        'dhlforyou',
        'postnl',
    ]),

    /**
     * Repositories
     */
    //AccountRepositoryInterface::class           => autowire(),
    PdkOrderRepositoryInterface::class => autowire(PdkOrderRepository::class),
    ProductRepositoryInterface::class  => autowire(PdkProductRepository::class),
    SettingsRepositoryInterface::class => autowire(PdkSettingsRepository::class),
    PdkCartRepositoryInterface::class  => autowire(PsCartRepository::class),
    //PdkShippingMethodRepositoryInterface::class => autowire(),

    /**
     * Services
     */
    //CronServiceInterface::class        => autowire(),
    LanguageServiceInterface::class    => autowire(LanguageService::class),
    OrderStatusServiceInterface::class => autowire(OrderStatusService::class),
    //RenderServiceInterface::class      => autowire(RenderService::class),
    ViewServiceInterface::class        => autowire(),
    WeightServiceInterface::class      => autowire(PsWeightService::class),

    /**
     * Endpoints
     */
    FrontendEndpointServiceInterface::class => autowire(),
    BackendEndpointServiceInterface::class  => autowire(),

    ConfigurationServiceInterface::class => autowire(Ps17ConfigurationService::class),

    /**
     * Webhooks
     */
    PdkWebhookServiceInterface::class      => autowire(),
    PdkWebhooksRepositoryInterface::class  => autowire(),

    DeliveryOptionsServiceInterface::class => autowire(),

    ClientAdapterInterface::class => autowire(Guzzle5ClientAdapter::class),
    LoggerInterface::class        => autowire(PdkLogger::class),
];

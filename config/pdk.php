<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Product\Repository\AbstractProductRepository;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\PrestaShop\Pdk\Api\Adapter\Guzzle5ClientAdapter;
use MyParcelNL\PrestaShop\Pdk\Base\Storage\PrestaShopCacheStorage;
use MyParcelNL\PrestaShop\Pdk\Config\PsEndpointActions;
use MyParcelNL\PrestaShop\Pdk\Logger\PdkLogger;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Service\OrderStatusService;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\ProductRepository;
use MyParcelNL\PrestaShop\Pdk\Service\LanguageService;
use MyParcelNL\PrestaShop\Pdk\Service\RenderService;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Service\Configuration\Ps17ConfigurationService;
use PrestaShop\CircuitBreaker\Contract\StorageInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

return [
    'platform'  => MyParcelNL::MODULE_NAME === 'myparcelnl' ? Platform::MYPARCEL_NAME : Platform::SENDMYPARCEL_NAME,
    'mode'      => value(_PS_MODE_DEV_ ? Pdk::MODE_DEVELOPMENT : Pdk::MODE_PRODUCTION),
    'userAgent' => value([
        'MyParcel-PrestaShop' => MyParcelNL::getModule()->version,
        'PrestaShop'          => _PS_VERSION_,
    ]),

    AbstractPdkOrderRepository::class  => autowire(PdkOrderRepository::class),
    AbstractProductRepository::class   => autowire(ProductRepository::class),
    AbstractSettingsRepository::class  => autowire(PdkSettingsRepository::class),
    ClientAdapterInterface::class      => autowire(Guzzle5ClientAdapter::class),
    EndpointActionsInterface::class    => autowire(PsEndpointActions::class),
    LanguageServiceInterface::class    => autowire(LanguageService::class),
    LoggerInterface::class             => autowire(PdkLogger::class),
    OrderStatusServiceInterface::class => autowire(OrderStatusService::class),
    RenderServiceInterface::class      => autowire(RenderService::class),
    StorageInterface::class            => autowire(PrestaShopCacheStorage::class),

    ConfigurationServiceInterface::class => autowire(Ps17ConfigurationService::class),
];

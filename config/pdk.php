<?php

declare(strict_types=1);

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Pdk\Api\Adapter\Guzzle5ClientAdapter;
use Gett\MyparcelBE\Pdk\Config\PsEndpointActions;
use Gett\MyparcelBE\Pdk\Logger\PdkLogger;
use Gett\MyparcelBE\Pdk\Order\Repository\PdkOrderRepository;
use Gett\MyparcelBE\Pdk\Service\LanguageService;
use Gett\MyparcelBE\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\value;

return [
    'platform' => MyParcelBE::MODULE_NAME,
    'mode'     => value(_PS_MODE_DEV_ ? Pdk::MODE_DEVELOPMENT : Pdk::MODE_PRODUCTION),

    ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor(
        [
            'userAgent' => ['PrestaShop', _PS_VERSION_],
            'apiKey'    => Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
        ]
    ),

    AbstractPdkOrderRepository::class => autowire(PdkOrderRepository::class),
    ClientAdapterInterface::class     => autowire(Guzzle5ClientAdapter::class),
    EndpointActionsInterface::class   => autowire(PsEndpointActions::class),
    LanguageServiceInterface::class   => autowire(LanguageService::class),
    LoggerInterface::class            => autowire(PdkLogger::class),
    AbstractSettingsRepository::class => autowire(PdkSettingsRepository::class),
];

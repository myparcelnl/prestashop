<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Webhook\AbstractPdkWebhookService;
use Tools;

class PsWebhookService extends AbstractPdkWebhookService
{
    public function getBaseUrl(): string
    {
        return sprintf(
            '%/%/%',
            Tools::getHttpHost(),
            Pdk::get('routeBackend'),
            Pdk::get('routeBackendWebhook')
        );
    }
}

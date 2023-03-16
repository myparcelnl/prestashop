<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Webhook\AbstractPdkWebhookService;

class PsWebhookService extends AbstractPdkWebhookService
{
    public function getBaseUrl(): string
    {
        // TODO: Implement getBaseUrl() method.
        return 'https://prestashop.dev.myparcel.nl/api';
    }
}

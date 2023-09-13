<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Webhook\Service;

use Context;
use MyParcelNL\Pdk\App\Webhook\Service\AbstractPdkWebhookService;

class PsWebhookService extends AbstractPdkWebhookService
{
    /**
     * @return string
     */
    public function createUrl(): string
    {
        return sprintf('%s?hash=%s', $this->getBaseUrl(), $this->generateHash());
    }

    public function getBaseUrl(): string
    {
        return Context::getContext()->link->getModuleLink('myparcelnl', 'webhook');
    }
}

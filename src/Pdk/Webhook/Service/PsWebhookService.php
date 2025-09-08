<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Webhook\Service;

use Context;
use MyParcelNL\Pdk\App\Webhook\Service\AbstractPdkWebhookService;
use MyParcelNL\Pdk\Base\PdkBootstrapper;

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
        return Context::getContext()->link->getModuleLink(PdkBootstrapper::PLUGIN_NAMESPACE, 'webhook');
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Webhook\AbstractPdkWebhookService;
use MyParcelNL\PrestaShop\Module\Concern\NeedsModuleUrl;
use Tools;

class PsWebhookService extends AbstractPdkWebhookService
{
    use NeedsModuleUrl;

    public function getBaseUrl(): string
    {
        return sprintf(
            '%s%s',
            Tools::getAdminUrl(),
            strtok($this->getUrl('myparcelnl_webhook'), '?')
        );
    }
}

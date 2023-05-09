<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Webhook\AbstractPdkWebhookService;
use MyParcelNL\PrestaShop\Module\Concern\NeedsModuleUrl;
use Tools;

class PsWebhookService extends AbstractPdkWebhookService
{
    use NeedsModuleUrl;

    public function getBaseUrl(): string
    {
        /** @var \PrestaShopBundle\Service\Routing\Router $router */
        $router = Pdk::get('ps.router');

        return sprintf(
            '%s%s',
            Tools::getAdminUrl(),
            // TODO move route names to config
            strtok($router->generate('myparcelnl_webhook'), '?')
        );
    }
}

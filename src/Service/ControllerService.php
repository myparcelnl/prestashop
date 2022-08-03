<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class ControllerService
{
    public const BASE = 'myparcelbe';
    public const PDK  = 'myparcelbe_pdk';

    /**
     * @param  string $route
     *
     * @return null|string
     */
    public function generateUri(string $route): ?string
    {
        $container = SymfonyContainer::getInstance();

        if (! $container) {
            return null;
        }

        /** @var \PrestaShopBundle\Service\Routing\Router $router */
        $router = $container->get('router');

        if (! $router) {
            return null;
        }

        return $router->generate($route);
    }
}

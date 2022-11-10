<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class ControllerService
{
    public const BASE = 'myparcelnl';
    public const PDK  = 'myparcelnl_pdk';

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

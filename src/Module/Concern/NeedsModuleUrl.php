<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Concern;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use RuntimeException;

trait NeedsModuleUrl
{
    /**
     * @param  string $routeId as defined in config/routes.yml
     *
     * @return string
     */
    private function getUrl(string $routeId): string
    {
        $container = SymfonyContainer::getInstance();

        if (! $container) {
            throw new RuntimeException('Container not found');
        }

        /** @var \PrestaShopBundle\Service\Routing\Router $router */
        $router = $container->get('router');

        if (! $router) {
            throw new RuntimeException('Router not found');
        }

        return $router->generate($routeId);
    }
}

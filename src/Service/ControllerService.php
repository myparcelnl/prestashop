<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class ControllerService
{
    public const LABEL   = 'myparcelbe_label';
    public const LOADING = 'myparcelbe_loading';
    public const ORDER   = 'myparcelbe_order';

    /**
     * @param  string $route
     *
     * @return null|string
     */
    public static function generateUri(string $route): ?string
    {
        $container = SymfonyContainer::getInstance();
        if (! $container) {
            return null;
        }

        $router = $container->get('router');
        if (! $router) {
            return null;
        }

        return $router->generate($route);
    }
}

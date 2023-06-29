<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Contract;

interface PsRouterServiceInterface
{
    /**
     * @param  string $route
     *
     * @return string
     */
    public function getBaseUrl(string $route): string;

    /**
     * @param  string $route
     *
     * @return string
     */
    public function getRouteToken(string $route): string;
}

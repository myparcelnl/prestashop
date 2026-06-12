<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Router\Service;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;
use MyParcelNL\Sdk\Support\Str;

abstract class PsRouterService extends Repository implements PsRouterServiceInterface
{
    /**
     * @param  string $route
     *
     * @return mixed
     */
    abstract protected function generateRoute(string $route): string;

    /**
     * @param  string $route
     *
     * @return string
     */
    public function getBaseUrl(string $route): string
    {
        return Str::before($this->generateRouteCached($route), '?');
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    public function getRouteToken(string $route): string
    {
        return $this->getRouteParameters($route)['_token'] ?? '';
    }

    /**
     * @param  string $route
     *
     * @return array<string, string>
     */
    public function getRouteParameters(string $route): array
    {
        $query = [];

        parse_str(parse_url($this->generateRouteCached($route), PHP_URL_QUERY) ?? '', $query);

        return $query;
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    private function generateRouteCached(string $route): string
    {
        return $this->retrieve("route_$route", function () use ($route) {
            return $this->generateRoute($route);
        });
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Sdk\src\Support\Str;

final class PsRouterService extends Repository implements PsRouterServiceInterface
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage
     */
    public function __construct(MemoryCacheStorage $storage)
    {
        parent::__construct($storage);
        $this->router = Pdk::get('ps.router');
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    public function getBaseUrl(string $route): string
    {
        return Str::before($this->generateRoute($route), '?');
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    public function getRouteToken(string $route): string
    {
        $query = [];

        parse_str(parse_url($this->generateRoute($route), PHP_URL_QUERY), $query);

        return $query['_token'] ?? '';
    }

    /**
     * @param  string $route
     *
     * @return string
     */
    private function generateRoute(string $route): string
    {
        return $this->retrieve("route_$route", function () use ($route) {
            return $this->router->generate($route);
        });
    }
}

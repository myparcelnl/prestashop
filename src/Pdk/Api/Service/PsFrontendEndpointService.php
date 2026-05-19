<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Api\Service;

use MyParcelNL\Pdk\App\Api\Frontend\AbstractFrontendEndpointService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;

final class PsFrontendEndpointService extends AbstractFrontendEndpointService
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param  \MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface $psRouterService
     */
    public function __construct(PsRouterServiceInterface $psRouterService)
    {
        $route = Pdk::get('routeNameFrontend');

        $this->baseUrl    = $psRouterService->getBaseUrl($route);
        $this->parameters = array_filter(array_merge($this->parameters, $psRouterService->getRouteParameters($route)));
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

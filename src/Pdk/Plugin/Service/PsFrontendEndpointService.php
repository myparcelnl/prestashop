<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\App\Api\Frontend\AbstractFrontendEndpointService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Service\PsRouterServiceInterface;

final class PsFrontendEndpointService extends AbstractFrontendEndpointService
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @param  \MyParcelNL\PrestaShop\Service\PsRouterServiceInterface $psRouterService
     */
    public function __construct(PsRouterServiceInterface $psRouterService)
    {
        $route = Pdk::get('routeNameFrontend');

        $this->baseUrl              = $psRouterService->getBaseUrl($route);
        $this->parameters['_token'] = $psRouterService->getRouteToken($route);
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}

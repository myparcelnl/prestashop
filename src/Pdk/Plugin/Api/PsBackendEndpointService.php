<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Api;

use MyParcelNL\Pdk\App\Api\Backend\AbstractPdkBackendEndpointService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Service\PsRouterServiceInterface;

final class PsBackendEndpointService extends AbstractPdkBackendEndpointService
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
        $route = Pdk::get('routeNamePdk');

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

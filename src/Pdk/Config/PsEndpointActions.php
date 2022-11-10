<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Config;

use Context;
use MyParcelNL\PrestaShop\Service\ControllerService;
use MyParcelNL\Pdk\Plugin\Action\PdkEndpointActions;

class PsEndpointActions extends PdkEndpointActions
{
    /**
     * @var \Context
     */
    private $context;

    /**
     * @var \MyParcelNL\PrestaShop\Service\ControllerService
     */
    private $controllerService;

    /**
     * @param  \MyParcelNL\PrestaShop\Service\ControllerService $controllerService
     */
    public function __construct(ControllerService $controllerService)
    {
        $this->controllerService = $controllerService;
        $this->context           = Context::getContext();

        $this->setParameters();
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $path          = ltrim($this->createActionPath(ControllerService::PDK), '/');
        $adminBaseLink = $this->context->link->getAdminBaseLink();

        $parts = parse_url("$adminBaseLink$path");

        return sprintf("%s://%s%s", $parts['scheme'], $parts['host'], $parts['path']);
    }

    /**
     * Fixes route urls for sites that are hosted in a subfolder instead of the root. In the frontend, we create urls
     * to routers like this: <adminUrl> + <path>. In the case of a site hosted at the root, the parts are "site.com/"
     * and "<adminFolder>/path/to/controller". This will work when concatenated, but when the site is in a folder we
     * get this: "site.com/<subfolder>/" + "<subfolder>/<adminFolder>/path/to/controller". A more robust fix would
     * be to use absolute urls, but PrestaShop made it impossible in their router to generate these. So now we just
     * remove one of the subfolder paths.
     *
     * @param  null|string $route
     *
     * @return string
     */
    private function createActionPath(string $route = null): string
    {
        $adminBaseLink = $this->context->link->getAdminBaseLink();
        $baseUrlParts  = parse_url($adminBaseLink);
        $routePath     = $this->controllerService->generateUri($route ?? ControllerService::BASE);

        return str_replace($baseUrlParts['path'], '/', $routePath);
    }

    /**
     * @return void
     */
    private function setParameters(): void
    {
        $parsed = parse_url($this->createActionPath());
        parse_str($parsed['query'], $params);

        $this->parameters['_token'] = $params['_token'];
    }
}

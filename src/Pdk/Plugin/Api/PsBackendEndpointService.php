<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Api;

use MyParcelNL\Pdk\Plugin\Api\Backend\AbstractPdkBackendEndpointService;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use RuntimeException;

class PsBackendEndpointService extends AbstractPdkBackendEndpointService
{
    private $baseUrl;

    public function __construct()
    {
        $url           = $this->getUrl();
        $parts         = explode('?', $url);
        $this->baseUrl = $parts[0];

        if (! isset($parts[1])) {
            return;
        }

        array_map(function ($part) {
            $kv                       = explode('=', $part);
            $this->parameters[$kv[0]] = $kv[1];
        },
            explode('&', $parts[1])
        );
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    private function getUrl(): string
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

        return $router->generate('myparcelnl_pdk');
    }
}

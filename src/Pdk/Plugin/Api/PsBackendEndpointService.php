<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Api;

use MyParcelNL\Pdk\Plugin\Api\Backend\AbstractPdkBackendEndpointService;
use MyParcelNL\PrestaShop\Module\Concern\NeedsModuleUrl;

class PsBackendEndpointService extends AbstractPdkBackendEndpointService
{
    use NeedsModuleUrl;

    private $baseUrl;

    public function __construct()
    {
        $url           = $this->getUrl('myparcelnl_pdk');
        $parts         = explode('?', $url);
        $this->baseUrl = $parts[0];

        array_map(function ($part) {
            $kv = explode('=', $part);

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
}

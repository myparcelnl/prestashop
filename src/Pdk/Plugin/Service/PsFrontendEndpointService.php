<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Api\Frontend\AbstractFrontendEndpointService;

class PsFrontendEndpointService extends AbstractFrontendEndpointService
{
    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return '/admin-dev/index.php/modules/myparcelnl/pdk';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use MyParcelNL\Pdk\App\Api\Frontend\AbstractFrontendEndpointService;
use MyParcelNL\PrestaShop\Module\Concern\NeedsModuleUrl;

class PsFrontendEndpointService extends AbstractFrontendEndpointService
{
    use NeedsModuleUrl;

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        //        return $this->getUrl('myparcelnl_pdk');
        return '/admin-dev/index.php/modules/myparcelnl/pdk';
    }
}

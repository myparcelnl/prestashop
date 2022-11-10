<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\PrestaShop\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Model\MyParcelRequest;

class AbstractEndpoint
{
    /**
     * @return \MyParcelNL\Sdk\src\Model\MyParcelRequest
     * @throws \Exception
     */
    protected function createRequest(): MyParcelRequest
    {
        return (new MyParcelRequest())
            ->setUserAgents(
                PlatformServiceFactory::create()
                    ->getUserAgents()
            );
    }
}

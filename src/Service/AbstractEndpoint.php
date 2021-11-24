<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
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

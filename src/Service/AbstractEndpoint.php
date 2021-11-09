<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
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
            ->setUserAgents(ConsignmentFactory::getUserAgent());
    }
}

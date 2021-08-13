<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use MyParcelNL\Sdk\src\Model\MyParcelRequest;

class AbstractEndpoint
{
    /**
     * @return \MyParcelNL\Sdk\src\Model\MyParcelRequest
     */
    protected function createRequest(): MyParcelRequest
    {
        return (new MyParcelRequest())
            ->setUserAgent('prestashop' . '/' . _PS_VERSION_);
    }
}

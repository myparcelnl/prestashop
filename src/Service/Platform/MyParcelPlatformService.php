<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

class MyParcelPlatformService extends AbstractPlatformService
{
    /**
     * @return class-string[]
     */
    public function getCarriers(): array
    {
        return [
            CarrierPostNL::class,
        ];
    }
}

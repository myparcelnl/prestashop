<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service\Platform;

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

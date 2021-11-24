<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

class SendMyParcelPlatformService extends AbstractPlatformService
{
    /**
     * @return class-string[]
     */
    public function getCarriers(): array
    {
        return [
            CarrierBpost::class,
            CarrierDPD::class,
            CarrierPostNL::class,
        ];
    }
}

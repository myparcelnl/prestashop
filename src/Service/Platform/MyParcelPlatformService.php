<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Platform;

use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

class MyParcelPlatformService extends AbstractPlatformService
{
    /**
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     */
    public function getDefaultCarrier(): AbstractCarrier
    {
        return new CarrierPostNL();
    }
}

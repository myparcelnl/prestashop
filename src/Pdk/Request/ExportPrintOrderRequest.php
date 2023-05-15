<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Request;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

class ExportPrintOrderRequest extends AbstractEndpointRequest
{
    public const ACTION = 'exportPrintOrder';

    public function getAction(): string
    {
        return self::ACTION;
    }
}

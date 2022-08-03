<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Request;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class ExportPrintOrderRequest extends AbstractEndpointRequest
{
    public const ACTION = 'exportPrintOrder';

    public function getAction(): string
    {
        return self::ACTION;
    }
}

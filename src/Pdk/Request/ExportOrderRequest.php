<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Request;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class ExportOrderRequest extends AbstractEndpointRequest
{
    public const ACTION = 'exportOrder';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'POST';
    }
}

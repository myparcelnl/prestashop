<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Request;

use MyParcelNL\Pdk\App\Request\AbstractEndpointRequest;

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

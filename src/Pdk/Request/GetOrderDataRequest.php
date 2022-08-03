<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Request;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class GetOrderDataRequest extends AbstractEndpointRequest
{
    public const ACTION = 'getOrderData';

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }
}

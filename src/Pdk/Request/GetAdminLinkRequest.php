<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Request;

use MyParcelNL\Pdk\Plugin\Request\AbstractEndpointRequest;

class GetAdminLinkRequest extends AbstractEndpointRequest
{
    public function getAction(): string
    {
        return 'admin';
    }
}
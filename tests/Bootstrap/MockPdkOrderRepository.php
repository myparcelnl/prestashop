<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository;
use Order;

class MockPdkOrderRepository extends PsPdkOrderRepository
{
    /**
     * @param  \Order $order
     *
     * @return string[]
     */
    protected function getRecipient(Order $order): array
    {
        return [
            'cc'      => 'NL',
            'city'    => 'Amsterdam',
            'company' => 'MyParcel',
            'email'   => '',
        ];
    }
}

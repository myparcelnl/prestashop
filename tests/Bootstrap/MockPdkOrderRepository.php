<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use Order;

class MockPdkOrderRepository extends PdkOrderRepository
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

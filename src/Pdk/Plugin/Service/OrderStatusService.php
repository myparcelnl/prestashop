<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Service;

use Context;
use MyParcelNL\Pdk\Plugin\Contract\OrderStatusServiceInterface;
use OrderState;

class OrderStatusService implements OrderStatusServiceInterface
{
    /**
     * @return array
     */
    public function all(): array
    {
        $orderStates = OrderState::getOrderStates((int) Context::getContext()->language->id);

        $array = [];

        foreach ($orderStates as $orderState) {
            $array[$orderState['id_order_state']] = $orderState['name'];
        }

        return $array;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Service;

use Context;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use OrderState;

class PsOrderStatusService implements OrderStatusServiceInterface
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

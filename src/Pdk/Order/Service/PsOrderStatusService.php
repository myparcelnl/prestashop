<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Service;

use Context;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use OrderState;

final class PsOrderStatusService implements OrderStatusServiceInterface
{
    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface
     */
    private $psOrderService;

    /**
     * @param  \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface $psOrderService
     */
    public function __construct(PsOrderServiceInterface $psOrderService)
    {
        $this->psOrderService = $psOrderService;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        $orderStates = OrderState::getOrderStates(Context::getContext()->language->id);

        $array = [];

        foreach ($orderStates as $orderState) {
            $array[$orderState['id_order_state']] = $orderState['name'];
        }

        return $array;
    }

    /**
     * @param  array  $orderIds
     * @param  string $status
     *
     * @return void
     */
    public function updateStatus(array $orderIds, string $status): void
    {
        foreach ($orderIds as $orderId) {
            $psOrder = $this->psOrderService->get($orderId);

            $psOrder->setCurrentState((int) $status);
        }
    }
}

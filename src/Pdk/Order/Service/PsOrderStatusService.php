<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Service;

use Context;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;
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

        return array_reduce($orderStates, static function ($carry, $orderState) {
            $key         = sprintf('status_%s', $orderState['id_order_state']);
            $carry[$key] = $orderState['name'];

            return $carry;
        }, []);
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
            /** @var \Order|null $psOrder */
            $psOrder = $this->psOrderService->get($orderId);

            if (! $psOrder) {
                Logger::error(sprintf('Order with id %s not found', $orderId));
                continue;
            }

            $psOrder->setCurrentState((int) str_replace('status_', '', $status));
        }
    }
}

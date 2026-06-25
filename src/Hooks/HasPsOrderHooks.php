<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use Order;
use Throwable;

trait HasPsOrderHooks
{
    /**
     * Eagerly transfers delivery options from the cart to the order when the order is validated,
     * so the admin order grid shows the correct carrier and options on first render instead of
     * relying on the lazy fallback in PsOrderService::getFromCart().
     *
     * An order data record is always persisted — empty when the cart has no delivery options — to
     * mark the order as processed, so PsOrderService::getOrderData() returns the stored value
     * instead of re-querying the cart on every read.
     *
     * This hook fires exactly once per order, dispatched from PaymentModule::validateOrder() during
     * the checkout payment flow — before the order is ever accessible in the back-office. Admin order
     * modifications go through different hooks (hookActionUpdateOrder etc.), so there is no risk of
     * overwriting manually changed order data.
     *
     * @param  array{order?: Order} $params
     *
     * @return void
     */
    public function hookActionValidateOrder(array $params): void
    {
        /** @var Order|null $order */
        $order = $params['order'] ?? null;

        if (! $order instanceof Order) {
            return;
        }

        try {
            /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
            $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);

            $fromCart = $cartDeliveryOptionsRepo->findOneBy(['cartId' => $order->id_cart]);

            $orderData = $fromCart ? ['deliveryOptions' => $fromCart->getData()] : [];

            /** @var PsOrderDataRepository $orderDataRepo */
            $orderDataRepo = Pdk::get(PsOrderDataRepository::class);

            $orderDataRepo->updateOrCreate(
                ['orderId' => $order->id],
                ['data'    => json_encode($orderData)]
            );

            EntityManager::flush();

            Logger::debug("[Order {$order->id}] Delivery options transferred from cart on validate");
        } catch (Throwable $e) {
            Logger::error('Failed to transfer delivery options on order validate', [
                'exception' => $e,
                'orderId'   => $order->id ?? null,
            ]);
        }
    }
}

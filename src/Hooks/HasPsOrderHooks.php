<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
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
     * The cart delivery options are only copied when the order's carrier is MyParcel-linked
     * (INT-1682): when the customer selects a MyParcel method in checkout (persisting options to
     * the cart) and then switches to a non-MyParcel method before completing the order, those
     * stale options must not end up on the order. The mapping check happens here, at
     * order-creation time, because that is the only moment it is guaranteed correct: PrestaShop
     * versions carriers on edit (new id_carrier) and hookActionCarrierUpdate() moves the mapping
     * row along, so a lazy check later could misjudge the order's original carrier. The shared
     * cart row is intentionally never deleted: a split cart produces multiple orders (one per
     * carrier package, in unspecified order), and each order decides for itself.
     *
     * An order data record is always persisted — empty when the cart has no delivery options or
     * the carrier is not MyParcel-linked — to mark the order as processed, so
     * PsOrderService::getOrderData() returns the stored value instead of re-querying the cart on
     * every read.
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
            /** @var PsCarrierServiceInterface $carrierService */
            $carrierService = Pdk::get(PsCarrierServiceInterface::class);

            /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
            $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);

            $fromCart = $carrierService->isMyParcelCarrier((int) $order->id_carrier)
                ? $cartDeliveryOptionsRepo->findOneBy(['cartId' => $order->id_cart])
                : null;

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

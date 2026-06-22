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
     * Eagerly transfers delivery options from the cart to the order when the order is validated.
     * Prevents the race condition where the lazy fallback in PsOrderService::getFromCart() runs
     * before cart delivery options are available and persists an empty record.
     *
     * @param  array{order: Order} $params
     *
     * @return void
     */
    public function hookActionValidateOrder(array $params): void
    {
        /** @var Order $order */
        $order = $params['order'] ?? null;

        if (! $order instanceof Order) {
            return;
        }

        try {
            /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
            $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);

            $fromCart = $cartDeliveryOptionsRepo->findOneBy(['cartId' => $order->id_cart]);

            if (! $fromCart) {
                Logger::debug("[Order {$order->id}] No cart delivery options to transfer on validate");

                return;
            }

            /** @var PsOrderDataRepository $orderDataRepo */
            $orderDataRepo = Pdk::get(PsOrderDataRepository::class);

            $orderDataRepo->updateOrCreate(
                ['orderId' => $order->id],
                ['data'    => json_encode(['deliveryOptions' => $fromCart->getData()])]
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

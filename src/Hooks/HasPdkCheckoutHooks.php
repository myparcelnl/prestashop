<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Cart;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use Order;
use Throwable;
use Tools;

/**
 * @property \Context $context
 */
trait HasPdkCheckoutHooks
{
    /**
     * @param  array{cart: \Cart} $params
     *
     * @return void
     * @throws \Exception
     */
    public function hookActionCarrierProcess(array $params): void
    {
        $action = Tools::getValue('action');
        $carrierId = Tools::getValue('delivery_option');

        if (('selectDeliveryOption' !== $action || empty($carrierId)) && ! Tools::isSubmit('confirmDeliveryOption')) {
            return;
        }

        $this->saveDeliveryOptionsToCart($params['cart']);
    }

    /**
     * Fires when an order is created from a cart. The carrier <-> MyParcel mapping is only
     * guaranteed to be correct at order-creation time: PrestaShop versions carriers (editing a
     * carrier creates a new id_carrier, and hookActionCarrierUpdate() moves the mapping row to
     * that new id), so resolving the mapping lazily later can look at a carrier id that has since
     * been re-mapped. To avoid silently losing delivery options because of that drift, and
     * because a single cart can produce multiple orders (one per carrier package, in unspecified
     * order) when the cart is split, this hook persists the order's delivery-option state
     * eagerly and per-order, and never mutates the shared cart row:
     * - MyParcel carrier: if a cart row exists, copy it to the order now. If it does not (yet)
     *   exist, do nothing and let the lazy fallback in PsOrderService::getFromCart() handle it.
     * - Non-MyParcel carrier: write an explicit empty order data row so stale cart delivery
     *   options are never later copied to this order by the lazy fallback.
     * The cart row itself is intentionally left untouched in both cases: deleting it here could
     * remove it before a sibling order (from the same split cart) has had a chance to copy it,
     * and there is currently no cleanup mechanism for cart rows in general (e.g. abandoned carts)
     * so leaving it linger here is consistent with that.
     *
     * @param  array{cart: \Cart, order: \Order} $params
     *
     * @return void
     */
    public function hookActionValidateOrder(array $params): void
    {
        $order = $params['order'] ?? null;

        if (! $order instanceof Order) {
            return;
        }

        /** @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface $carrierService */
        $carrierService = Pdk::get(PsCarrierServiceInterface::class);

        /** @var \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface $orderService */
        $orderService = Pdk::get(PsOrderServiceInterface::class);

        try {
            if ($carrierService->isMyParcelCarrier((int) $order->id_carrier)) {
                /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
                $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

                $cartDeliveryOptions = $cartDeliveryOptionsRepository->findOneBy(['cartId' => (int) $order->id_cart]);

                if (! $cartDeliveryOptions) {
                    return;
                }

                $orderService->updateOrderData($order, ['deliveryOptions' => $cartDeliveryOptions->getData()]);
            } else {
                $orderService->updateOrderData($order, []);
            }

            EntityManager::flush();
        } catch (Throwable $e) {
            Logger::error('Failed to persist order delivery options on order validation', [
                'exception' => $e,
                'cartId'    => (int) $order->id_cart,
                'orderId'   => (int) $order->id,
            ]);
        }
    }

    /**
     * @param  \Cart $cart
     *
     * @return void
     */
    private function saveDeliveryOptionsToCart(Cart $cart): void
    {
        $options = Tools::getValue(Pdk::get('checkoutHiddenInputName'));

        if (! $options || '[]' === $options) {
            return;
        }

        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        try {
            $deliveryOptions = new DeliveryOptions(json_decode($options, true));

            $cartDeliveryOptionsRepository->updateOrCreate(
                [
                    'cartId' => $cart->id,
                ],
                [
                    'data' => json_encode($deliveryOptions->toStorableArray()),
                ]
            );

            EntityManager::flush();
        } catch (Throwable $e) {
            Logger::error('Failed to save delivery options to cart', [
                'exception' => $e,
                'options'   => $options,
                'cartId'    => $cart->id,
            ]);
        }
    }
}

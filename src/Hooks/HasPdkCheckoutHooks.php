<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Cart;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
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
     * Fires when an order is created from a cart. When the customer completed checkout with a
     * carrier that is not linked to MyParcel, delivery options saved to the cart earlier in the
     * checkout (while a MyParcel carrier was selected) are stale and must be removed, so they
     * are never copied to the order.
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

        if ($carrierService->isMyParcelCarrier((int) $order->id_carrier)) {
            return;
        }

        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        $cartDeliveryOptions = $cartDeliveryOptionsRepository->findOneBy(['cartId' => (int) $order->id_cart]);

        if (! $cartDeliveryOptions) {
            return;
        }

        try {
            $cartDeliveryOptionsRepository->delete($cartDeliveryOptions);
            EntityManager::flush();
        } catch (Throwable $e) {
            Logger::error('Failed to delete stale cart delivery options', [
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

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Cart;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use Tools;

trait HasPsShippingCostHooks
{
    /**
     * @param $cart
     * @param $shippingCost
     *
     * @return mixed
     */
    public function getOrderShippingCost($cart, $shippingCost)
    {
        return $shippingCost;
        //        if (! $this->hasPdk || ! empty($this->context->controller->requestOriginalShippingCost)) {
        //            return $shippingCost;
        //        }
        //
        //        $carrier = Pdk::get(PsCarrierServiceInterface::class)
        //            ->getMyParcelCarrier($cart->id_carrier);
        //
        //        if (! $carrier) {
        //            return $shippingCost;
        //        }
        //
        //        $deliveryOptions = $this->getDeliveryOptions($cart);
        //
        //        if (! $deliveryOptions) {
        //            return $shippingCost;
        //        }
        //
        //        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface $service */
        //        $service = Pdk::get(DeliveryOptionsFeesServiceInterface::class);
        //
        //        $fees = $service->getFees(new DeliveryOptions(['carrier' => $carrier]));
        //
        //        $totalPrice = $fees
        //            ->reduce(function ($carry, $fee) {
        //                return $carry + $fee->getAmount();
        //            }, $shippingCost);
        //
        //        return $totalPrice;
    }

    /**
     * @param  \Cart $params
     *
     * @return bool
     */
    public function getOrderShippingCostExternal($params)
    {
        return true;
    }

    private function getDeliveryOptions(Cart $cart): ?array
    {
        $deliveryOptions = Tools::getValue(Pdk::get('checkoutHiddenInputName'), null);

        if ($deliveryOptions) {
            return json_decode($deliveryOptions, true);
        }

        /** @var \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository $repository */
        $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

        $options = $repository->findOneBy(['cartId' => $cart->id]);

        if (! $options) {
            return null;
        }

        return $options->getData();
    }
}

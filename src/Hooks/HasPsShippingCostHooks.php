<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Cart\Model\PdkCartFee;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use Tools;

trait HasPsShippingCostHooks
{
    /**
     * Will be filled automatically during the checkout process with the id of the current carrier.
     *
     * @var int
     */
    public $id_carrier;

    /**
     * @var array
     */
    private $cachedPrices = [];

    /**
     * @param  \Cart|mixed $cart
     * @param  int|float   $shippingCost
     *
     * @return int|float
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getOrderShippingCost($cart, $shippingCost)
    {
        if (! $this->hasPdk || ! $this->context->controller) {
            return $shippingCost;
        }

        /** @var \MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface $carrierService */
        $carrierService = Pdk::get(PsCarrierServiceInterface::class);

        $carrier = $carrierService->getMyParcelCarrier($this->id_carrier);

        if (! $carrier) {
            return $shippingCost;
        }

        $deliveryOptions = $this->getDeliveryOptions($carrier);
        $cacheKey        = md5(json_encode($deliveryOptions->toArrayWithoutNull()));

        if (! isset($this->cachedPrices[$cacheKey])) {
            $this->cachedPrices[$cacheKey] = $this->calculateShippingCost($deliveryOptions, (float) $shippingCost);
        }

        return $this->cachedPrices[$cacheKey];
    }

    /**
     * @param  \Cart $params
     *
     * @return bool
     */
    public function getOrderShippingCostExternal($params): bool
    {
        return true;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     * @param  float                                          $shippingCost
     *
     * @return float
     */
    private function calculateShippingCost(DeliveryOptions $deliveryOptions, float $shippingCost): float
    {
        /** @var \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface $service */
        $service = Pdk::get(DeliveryOptionsFeesServiceInterface::class);

        return $service
            ->getFees($deliveryOptions)
            ->reduce(function (float $carry, PdkCartFee $fee) {
                return $carry + $fee->getAmount();
            }, $shippingCost);
    }

    /**
     * Get the actual delivery options from the checkout hidden input, or get the default delivery options for the current carrier, without any options.
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     */
    private function getDeliveryOptions(Carrier $carrier): DeliveryOptions
    {
        $deliveryOptions = Tools::getValue(Pdk::get('checkoutHiddenInputName'), null);

        if ($deliveryOptions) {
            return new DeliveryOptions(json_decode($deliveryOptions, true));
        }

        //Delivery options are not in hidden input, fetch them from the database.
        /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
        $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);
        $deliveryOptions = $cartDeliveryOptionsRepository->findOneBy(['cartId' => $this->context->cart->id]);

        if ($deliveryOptions && property_exists($deliveryOptions, 'data')) {
            return new DeliveryOptions($deliveryOptions->getData());
        }

        return new DeliveryOptions(['carrier' => $carrier]);
    }
}

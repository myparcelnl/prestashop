<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Cart;

final class ModuleService
{
    /**
     * Adds prices of MyParcel delivery options to the shipping cost.
     *
     * @param  \Cart     $cart
     * @param  float|int $shippingCost
     *
     * @return float|int
     * @todo Implement this method correctly using pdk stuff
     * @see  \MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface
     */
    public function getOrderShippingCost(Cart $cart, $shippingCost)
    {
        return $shippingCost;

        //        $module = Pdk::get('moduleInstance');
        //
        //        /** @noinspection PhpCastIsUnnecessaryInspection */
        //        $carrierId = (int) $cart->id_carrier;
        //
        //        if ($module->id_carrier !== $carrierId || ! empty($this->context->controller->requestOriginalShippingCost)) {
        //            return $shippingCost;
        //        }
        //
        //        $myParcelCost    = 0;
        //        $deliveryOptions = Tools::getValue('myparcel-delivery-options', false);
        //
        //        if ($deliveryOptions) {
        //            $deliveryOptions = json_decode($deliveryOptions, true);
        //        } else {
        //            $deliveryOptions = DeliveryOptionsManager::getFromCart((int) $cart->id);
        //
        //            if ($deliveryOptions) {
        //                $deliveryOptions = $deliveryOptions->toArray();
        //            }
        //        }
        //
        //        if (empty($deliveryOptions)) {
        //            return $shippingCost;
        //        }
        //
        //        $isPickup = $deliveryOptions['isPickup'] ?? false;
        //
        //        if ($isPickup) {
        //            $myParcelCost += (float) CarrierConfigurationProvider::get(
        //                $carrierId,
        //                'pricePickup'
        //            );
        //        } else {
        //            $deliveryType = $deliveryOptions['deliveryType'] ?? AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME;
        //
        //            if ($deliveryType !== AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME) {
        //                $priceHourInterval = 'price' . ucfirst($deliveryType) . 'Delivery';
        //                $myParcelCost      += (float) CarrierConfigurationProvider::get(
        //                    $carrierId,
        //                    $priceHourInterval
        //                );
        //            }
        //
        //            if (! empty($deliveryOptions['shipmentOptions']['only_recipient'])) {
        //                $myParcelCost += (float) CarrierConfigurationProvider::get(
        //                    $carrierId,
        //                    'priceOnlyRecipient'
        //                );
        //            }
        //
        //            if (! empty($deliveryOptions['shipmentOptions']['signature'])) {
        //                $myParcelCost += (float) CarrierConfigurationProvider::get(
        //                    $carrierId,
        //                    'priceSignature'
        //                );
        //            }
        //        }
        //
        //        return $shippingCost + $myParcelCost;
    }
}

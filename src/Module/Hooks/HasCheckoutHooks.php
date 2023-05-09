<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

trait HasCheckoutHooks
{
    /**
     * Run on choosing shipping method in checkout.
     *
     * @param  array $params
     *
     * @return void
     * @noinspection PhpUnused
     * @TODO         update this to modern code. This saves delivery options to the cart, so the order repository can
     *               get it.
     */
    public function hookActionCarrierProcess(array $params): void
    {
        //        $options = Tools::getValue('myparcel-delivery-options');
        //
        //        if (! $options || '[]' === $options) {
        //            return;
        //        }
        //
        //        $action    = Tools::getValue('action');
        //        $carrierId = Tools::getValue('delivery_option');
        //
        //        if (('selectDeliveryOption' === $action && ! empty($carrierId)) || Tools::isSubmit('confirmDeliveryOption')) {
        //            /** @var \PrestaShop\PrestaShop\Adapter\Entity\Cart $cart */
        //            $cart = $params['cart'];
        //
        //            /** @var PsCartDeliveryOptionsRepository $repository */
        //            $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);
        //
        //            $entity = $repository->createEntity();
        //
        //            $repository->save($entity);
        //
        //            $optionsArray    = json_decode($options, true);
        //            $deliveryOptions = new DeliveryOptions();
        //            $deliveryOptions->fill(Arr::only($optionsArray, array_keys($deliveryOptions->getAttributes())));
        //
        //            DeliveryOptionsManager::save($cart->id, $deliveryOptions);
        //        }
    }
}

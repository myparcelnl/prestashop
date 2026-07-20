<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Carrier as PsCarrier;
use Cart;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Order;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function MyParcelNL\PrestaShop\setupAccountAndCarriers;

final class CheckoutHooksTestClass
{
    use HasPdkCheckoutHooks;
}

usesShared(new UsesMockPsPdkInstance());

function storeCheckoutCartDeliveryOptions(int $cartId): void
{
    /** @var PsCartDeliveryOptionsRepository $repository */
    $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    $deliveryOptions = new DeliveryOptions([
        'carrier'      => RefCapabilitiesSharedCarrierV2::POSTNL,
        'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
    ]);

    $repository->updateOrCreate(
        ['cartId' => $cartId],
        ['data' => json_encode($deliveryOptions->toStorableArray())]
    );
}

it('deletes cart delivery options when order is validated with a non-myparcel carrier', function () {
    // Needed so DeliveryOptions can resolve the PostNL carrier when serializing the stale
    // cart data below; this does NOT create a myparcelnl_carrier_mapping row.
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
    );

    // Carrier 94 has no myparcel carrier mapping.
    $psCarrier = psFactory(PsCarrier::class)
        ->withId(94)
        ->store();

    /** @var Cart $cart */
    $cart = psFactory(Cart::class)
        ->withCarrier($psCarrier)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(94)
        ->withIdCart((int) $cart->id)
        ->store();

    storeCheckoutCartDeliveryOptions((int) $cart->id);

    (new CheckoutHooksTestClass())->hookActionValidateOrder([
        'cart'  => $cart,
        'order' => $order,
    ]);

    /** @var PsCartDeliveryOptionsRepository $repository */
    $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    expect($repository->findOneBy(['cartId' => (int) $cart->id]))->toBeNull();
});

it('keeps cart delivery options when order is validated with a myparcel carrier', function () {
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
    );

    $psCarrier = psFactory(PsCarrier::class)
        ->withId(93)
        ->store();

    psFactory(MyparcelnlCarrierMapping::class)
        ->withCarrierId(93)
        ->withMyparcelCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->store();

    /** @var Cart $cart */
    $cart = psFactory(Cart::class)
        ->withCarrier($psCarrier)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(93)
        ->withIdCart((int) $cart->id)
        ->store();

    storeCheckoutCartDeliveryOptions((int) $cart->id);

    (new CheckoutHooksTestClass())->hookActionValidateOrder([
        'cart'  => $cart,
        'order' => $order,
    ]);

    /** @var PsCartDeliveryOptionsRepository $repository */
    $repository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    expect($repository->findOneBy(['cartId' => (int) $cart->id]))->not->toBeNull();
});

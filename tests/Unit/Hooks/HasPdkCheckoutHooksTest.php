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
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
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

beforeEach(function () {
    // Needed so DeliveryOptions can resolve the PostNL carrier when serializing the stale
    // cart data used below; this does NOT by itself create a myparcelnl_carrier_mapping row.
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
    );
});

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

it('writes an empty order data row and keeps the cart row when order is validated with a non-myparcel carrier', function () {
    // Carrier 94 deliberately has no myparcel carrier mapping.
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

    // Stale options from a MyParcel carrier that was selected earlier in checkout.
    storeCheckoutCartDeliveryOptions((int) $cart->id);

    (new CheckoutHooksTestClass())->hookActionValidateOrder([
        'cart'  => $cart,
        'order' => $order,
    ]);

    /** @var PsOrderDataRepository $orderDataRepository */
    $orderDataRepository = Pdk::get(PsOrderDataRepository::class);
    $orderData           = $orderDataRepository->findOneBy(['orderId' => (int) $order->id]);

    expect($orderData)->not->toBeNull();
    expect($orderData->getData())->toBe([]);

    // The cart row must NOT be deleted: another order created from the same (split) cart may
    // still need to copy it, and deleting it here would race with that sibling order.
    /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
    $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    expect($cartDeliveryOptionsRepository->findOneBy(['cartId' => (int) $cart->id]))->not->toBeNull();
});

it('eagerly copies cart delivery options to the order data row when order is validated with a myparcel carrier and a cart row exists', function () {
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

    /** @var PsOrderDataRepository $orderDataRepository */
    $orderDataRepository = Pdk::get(PsOrderDataRepository::class);
    $orderData           = $orderDataRepository->findOneBy(['orderId' => (int) $order->id]);

    expect($orderData)->not->toBeNull();
    expect($orderData->getData())->toHaveKey('deliveryOptions');
    expect($orderData->getData()['deliveryOptions'])->not->toBeEmpty();
    expect($orderData->getData()['deliveryOptions']['carrier'] ?? null)->toBe(RefCapabilitiesSharedCarrierV2::POSTNL);

    // The cart row is left in place; there is no cleanup mechanism for cart rows and the lazy
    // fallback in PsOrderService::getFromCart() may still rely on it being there.
    /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepository */
    $cartDeliveryOptionsRepository = Pdk::get(PsCartDeliveryOptionsRepository::class);

    expect($cartDeliveryOptionsRepository->findOneBy(['cartId' => (int) $cart->id]))->not->toBeNull();
});

it('does not write an order data row when order is validated with a myparcel carrier and no cart row exists', function () {
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

    // No cart row was ever saved for this cart, e.g. the customer picked the MyParcel carrier
    // without ever opening the delivery options widget.

    (new CheckoutHooksTestClass())->hookActionValidateOrder([
        'cart'  => $cart,
        'order' => $order,
    ]);

    /** @var PsOrderDataRepository $orderDataRepository */
    $orderDataRepository = Pdk::get(PsOrderDataRepository::class);

    // Nothing to copy; the lazy guard in PsOrderService::getFromCart() remains the fallback.
    expect($orderDataRepository->findOneBy(['orderId' => (int) $order->id]))->toBeNull();
});

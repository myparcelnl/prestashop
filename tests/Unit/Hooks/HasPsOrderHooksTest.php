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

final class ClassWithPsOrderHooks
{
    use HasPsOrderHooks;
}

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    // Needed so DeliveryOptions can resolve the PostNL carrier when serializing cart data below;
    // this does NOT by itself create a myparcelnl_carrier_mapping row.
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
    );
});

/**
 * Creates a PS carrier with a myparcelnl_carrier_mapping row, so the carrier counts as
 * MyParcel-linked for the carrier check in hookActionValidateOrder().
 */
function createOrderHooksMappedCarrier(int $carrierId): PsCarrier
{
    $psCarrier = psFactory(PsCarrier::class)
        ->withId($carrierId)
        ->store();

    psFactory(MyparcelnlCarrierMapping::class)
        ->withCarrierId($carrierId)
        ->withMyparcelCarrier(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->store();

    return $psCarrier;
}

it('transfers delivery options from cart to order on validate when order carrier is a myparcel carrier', function () {
    $psCarrier = createOrderHooksMappedCarrier(93);

    $cart  = psFactory(Cart::class)->withCarrier($psCarrier)->store();
    $order = psFactory(Order::class)
        ->withIdCarrier(93)
        ->withIdCart((int) $cart->id)
        ->store();

    // Deliberately non-default values (not postnl / standard) so the test proves the actual
    // stored options are round-tripped, and cannot pass on coincidentally-correct defaults.
    $rawDeliveryOptions = [
        DeliveryOptions::CARRIER       => 'dpd',
        DeliveryOptions::DELIVERY_TYPE => 'morning',
    ];

    /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
    $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);
    $cartDeliveryOptionsRepo->updateOrCreate(
        ['cartId' => (int) $cart->id],
        ['data'   => json_encode($rawDeliveryOptions)]
    );

    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => (int) $order->id]);

    expect($record)->not->toBeNull();
    $data = $record->getData();
    expect($data)->toHaveKey('deliveryOptions');
    expect($data['deliveryOptions'])->toMatchArray($rawDeliveryOptions);

    // The cart row is left in place: a split cart produces multiple orders (one per carrier
    // package, in unspecified order) and a sibling order may still need to copy it.
    expect($cartDeliveryOptionsRepo->findOneBy(['cartId' => (int) $cart->id]))->not->toBeNull();
});

it('persists an empty order data record when order carrier is not a myparcel carrier', function () {
    // Carrier 94 deliberately has NO row in myparcelnl_carrier_mapping.
    $psCarrier = psFactory(PsCarrier::class)
        ->withId(94)
        ->store();

    $cart  = psFactory(Cart::class)->withCarrier($psCarrier)->store();
    $order = psFactory(Order::class)
        ->withIdCarrier(94)
        ->withIdCart((int) $cart->id)
        ->store();

    // Stale options from a MyParcel carrier that was selected earlier in checkout (INT-1682).
    /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
    $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);
    $cartDeliveryOptionsRepo->updateOrCreate(
        ['cartId' => (int) $cart->id],
        ['data'   => json_encode([
            DeliveryOptions::CARRIER       => RefCapabilitiesSharedCarrierV2::POSTNL,
            DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
        ])]
    );

    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => (int) $order->id]);

    // The stale cart options must NOT be copied; an empty record marks the order as processed.
    expect($record)->not->toBeNull();
    expect($record->getData())->toBe([]);

    // The cart row must NOT be deleted: another order created from the same (split) cart may
    // still need to copy it, and deleting it here would race with that sibling order.
    expect($cartDeliveryOptionsRepo->findOneBy(['cartId' => (int) $cart->id]))->not->toBeNull();
});

it('persists an empty order data record when no cart delivery options exist', function () {
    $cart  = psFactory(Cart::class)->store();
    $order = psFactory(Order::class)->withIdCart($cart->id)->store();

    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    // Marks the order as "processed" even without delivery options, so getOrderData does
    // not have to re-query the cart on every read.
    expect($record)->not->toBeNull();
    expect($record->getData())->toBe([]);
});

it('persists an empty order data record when order carrier is a myparcel carrier without cart delivery options', function () {
    $psCarrier = createOrderHooksMappedCarrier(93);

    $cart  = psFactory(Cart::class)->withCarrier($psCarrier)->store();
    $order = psFactory(Order::class)
        ->withIdCarrier(93)
        ->withIdCart((int) $cart->id)
        ->store();

    // No cart row was ever saved, e.g. the customer picked the MyParcel carrier without ever
    // opening the delivery options widget.
    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => (int) $order->id]);

    expect($record)->not->toBeNull();
    expect($record->getData())->toBe([]);
});

it('does nothing when order param is missing', function () {
    (new ClassWithPsOrderHooks())->hookActionValidateOrder([]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);

    expect($orderDataRepo->all())->toBeEmpty();
});

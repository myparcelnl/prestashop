<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use Cart;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
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
 * MyParcel-linked for the guard in PsOrderService::getFromCart().
 */
function createMappedPsCarrier(int $carrierId): PsCarrier
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

function storeCartDeliveryOptions(int $cartId): void
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

it('persists an empty order data record when no cart delivery options exist', function () {
    $order = psFactory(Order::class)->store();

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $result = $orderService->getOrderData($order->id);

    expect($result)->toBe([]);

    // The order is marked "processed" by persisting an empty record, so subsequent reads
    // return the stored value instead of re-querying the cart on every getOrderData call.
    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    expect($record)->not->toBeNull();
    expect($record->getData())->toBe([]);
});

it('returns delivery options and persists order data when order carrier is a myparcel carrier', function () {
    $psCarrier = createMappedPsCarrier(93);

    /** @var Cart $cart */
    $cart = psFactory(Cart::class)
        ->withCarrier($psCarrier)
        ->store();

    /** @var Order $order */
    $order = psFactory(Order::class)
        ->withIdCarrier(93)
        ->withIdCart((int) $cart->id)
        ->store();

    // A plain array literal is used instead of DeliveryOptions::toStorableArray(): the code
    // under test stores the cart's raw data verbatim, so the exact shape here is what matters.
    //
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

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $result = $orderService->getOrderData($order->id);

    expect($result)->toHaveKey('deliveryOptions');
    expect($result['deliveryOptions'])->toMatchArray($rawDeliveryOptions);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => (int) $order->id]);

    expect($record)->not->toBeNull();
});

it('does not copy delivery options from cart when order carrier is not a myparcel carrier', function () {
    // Carrier 94 deliberately has NO row in myparcelnl_carrier_mapping.
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
    storeCartDeliveryOptions((int) $cart->id);

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $orderData = $orderService->getOrderData($order);

    expect($orderData)->toBe([]);

    /** @var PsOrderDataRepository $orderDataRepository */
    $orderDataRepository = Pdk::get(PsOrderDataRepository::class);
    $stored              = $orderDataRepository->findOneBy(['orderId' => (int) $order->id]);

    expect($stored)
        ->not->toBeNull()
        ->and($stored->getData())
        ->toBe([]);
});

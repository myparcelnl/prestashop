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

it('copies delivery options from cart to order when order carrier is a myparcel carrier', function () {
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

    storeCartDeliveryOptions((int) $cart->id);

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $orderData = $orderService->getOrderData($order);

    expect($orderData)->toHaveKey('deliveryOptions');
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

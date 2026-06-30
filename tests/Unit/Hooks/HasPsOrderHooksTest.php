<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

final class ClassWithPsOrderHooks
{
    use HasPsOrderHooks;
}

usesShared(new UsesMockPsPdkInstance());

it('transfers delivery options from cart to order on validate', function () {
    $cart  = psFactory(\Cart::class)->store();
    $order = psFactory(Order::class)->withIdCart($cart->id)->store();

    // Deliberately non-default values (not postnl / standard) so the test proves the actual
    // stored options are round-tripped, and cannot pass on coincidentally-correct defaults.
    $rawDeliveryOptions = [
        DeliveryOptions::CARRIER       => 'dpd',
        DeliveryOptions::DELIVERY_TYPE => 'morning',
    ];

    /** @var PsCartDeliveryOptionsRepository $cartDeliveryOptionsRepo */
    $cartDeliveryOptionsRepo = Pdk::get(PsCartDeliveryOptionsRepository::class);
    $cartDeliveryOptionsRepo->updateOrCreate(
        ['cartId' => $cart->id],
        ['data'   => json_encode($rawDeliveryOptions)]
    );

    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    expect($record)->not->toBeNull();
    $data = $record->getData();
    expect($data)->toHaveKey('deliveryOptions');
    expect($data['deliveryOptions'])->toMatchArray($rawDeliveryOptions);
});

it('persists an empty order data record when no cart delivery options exist', function () {
    $cart  = psFactory(\Cart::class)->store();
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

it('does nothing when order param is missing', function () {
    (new ClassWithPsOrderHooks())->hookActionValidateOrder([]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);

    expect($orderDataRepo->all())->toBeEmpty();
});

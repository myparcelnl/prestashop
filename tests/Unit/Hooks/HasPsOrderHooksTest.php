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

    $rawDeliveryOptions = [
        DeliveryOptions::CARRIER       => 'postnl',
        DeliveryOptions::DELIVERY_TYPE => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
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

it('does not create order data record when no cart delivery options exist', function () {
    $cart  = psFactory(\Cart::class)->store();
    $order = psFactory(Order::class)->withIdCart($cart->id)->store();

    (new ClassWithPsOrderHooks())->hookActionValidateOrder(['order' => $order]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record        = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    expect($record)->toBeNull();
});

it('does nothing when order param is missing', function () {
    (new ClassWithPsOrderHooks())->hookActionValidateOrder([]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);

    expect($orderDataRepo->all())->toBeEmpty();
});

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('returns empty array and does NOT persist order data when no cart delivery options exist', function () {
    $order = psFactory(\Order::class)->store();

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $result = $orderService->getOrderData($order->id);

    expect($result)->toBe([]);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    expect($record)->toBeNull();
});

it('returns delivery options and persists order data when cart delivery options exist', function () {
    $cart  = psFactory(\Cart::class)->store();
    $order = psFactory(\Order::class)->withIdCart($cart->id)->store();

    // A plain array literal is used instead of DeliveryOptions::toStorableArray():
    // the latter resolves the carrier from the repository and throws
    // ModelNotFoundException in the PDK 4.0.1 mock environment. The code under test
    // stores the cart's raw data verbatim, so the exact shape here is what matters.
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

    /** @var PsOrderServiceInterface $orderService */
    $orderService = Pdk::get(PsOrderServiceInterface::class);

    $result = $orderService->getOrderData($order->id);

    expect($result)->toHaveKey('deliveryOptions');
    expect($result['deliveryOptions'])->toMatchArray($rawDeliveryOptions);

    /** @var PsOrderDataRepository $orderDataRepo */
    $orderDataRepo = Pdk::get(PsOrderDataRepository::class);
    $record = $orderDataRepo->findOneBy(['orderId' => $order->id]);

    expect($record)->not->toBeNull();
});

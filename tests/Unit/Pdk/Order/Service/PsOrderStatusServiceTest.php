<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Service;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order as PsOrder;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('retrieves all order statuses', function () {
    /** @var OrderStatusServiceInterface $service */
    $service = Pdk::get(OrderStatusServiceInterface::class);

    /** @see UsesMockPsPdkInstance::createOrderStates() */
    expect($service->all())->toBe([
        1  => 'Awaiting check payment',
        2  => 'Payment accepted',
        3  => 'Processing in progress',
        4  => 'Shipped',
        5  => 'Delivered',
        6  => 'Canceled',
        7  => 'Refunded',
        8  => 'Payment error',
        9  => 'On backorder (paid)',
        10 => 'Awaiting bank wire payment',
        11 => 'Remote payment accepted',
        12 => 'On backorder (not paid)',
        13 => 'Awaiting Cash On Delivery validation',
    ]);
});

it('updates order status', function () {
    psFactory(PsOrder::class)
        ->withId(14)
        ->store();
    psFactory(PsOrder::class)
        ->withId(16)
        ->store();

    /** @var OrderStatusServiceInterface $service */
    $service = Pdk::get(OrderStatusServiceInterface::class);

    $service->updateStatus([14, 16], '4');

    $order14 = new PsOrder(14);
    $order16 = new PsOrder(16);

    expect($order14->current_state)
        ->toBe(4)
        ->and($order16->current_state)
        ->toBe(4);
});

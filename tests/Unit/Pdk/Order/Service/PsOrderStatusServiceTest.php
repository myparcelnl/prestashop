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
        'status_1'  => 'Awaiting check payment',
        'status_2'  => 'Payment accepted',
        'status_3'  => 'Processing in progress',
        'status_4'  => 'Shipped',
        'status_5'  => 'Delivered',
        'status_6'  => 'Canceled',
        'status_7'  => 'Refunded',
        'status_8'  => 'Payment error',
        'status_9'  => 'On backorder (paid)',
        'status_10' => 'Awaiting bank wire payment',
        'status_11' => 'Remote payment accepted',
        'status_12' => 'On backorder (not paid)',
        'status_13' => 'Awaiting Cash On Delivery validation',
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

    $service->updateStatus([14, 16], 'status_4');

    // Retrieve the orders again to check if the status has been updated
    $order14 = new PsOrder(14);
    $order16 = new PsOrder(16);

    expect($order14->current_state)
        ->toBe(4)
        ->and($order16->current_state)
        ->toBe(4);
});

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use OrderFactory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

it('creates a valid pdk order', function (OrderFactory $orderFactory) {
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    $psOrder  = $orderFactory->store();
    $pdkOrder = $orderRepository->get($psOrder);

    $array = $pdkOrder->toArrayWithoutNull();

    assertMatchesJsonSnapshot(
        json_encode(
            Arr::except($array, ['deliveryOptions.carrier.capabilities', 'deliveryOptions.carrier.returnCapabilities']),
            JSON_PRETTY_PRINT
        )
    );
})->with([
    'simple order' => function () {
        return psFactory(Order::class);
    },
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Order;
use OrderFactory;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function MyParcelNL\PrestaShop\setupAccountAndCarriers;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

beforeEach(function () {
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)->fromPostNL()
        )
    );
});

it('creates a valid pdk order', function (OrderFactory $orderFactory) {
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    /** @var Order $psOrder */
    $psOrder = $orderFactory->withReference('PrestaShop: 1')->store();

    $pdkOrder = $orderRepository->get($psOrder);

    assertMatchesJsonSnapshot(
        json_encode(
            Arr::except(
                $pdkOrder->toArrayWithoutNull(),
                [
                    'deliveryOptions.carrier.capabilities',
                    'deliveryOptions.carrier.returnCapabilities',
                    'deliveryOptions.carrier.inboundFeatures',
                    'deliveryOptions.carrier.outboundFeatures',
                ]
            )
        )
    );
})->with([
    'simple order' => function () {
        return psFactory(Order::class);
    },
]);

it('creates a pdk order from order id', function () {
    /** @var Order $psOrder */
    $psOrder = psFactory(Order::class)->store();

    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $orderRepository */
    $orderRepository = Pdk::get(PdkOrderRepositoryInterface::class);

    $pdkOrder = $orderRepository->get($psOrder->id);

    expect($pdkOrder)->toBeInstanceOf(PdkOrder::class);
});

it('finds a pdk order by api identifier', function () {
    /** @var Order $psOrder */
    $psOrder = psFactory(Order::class)->store();

    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId((int) $psOrder->id)
            ->withData(json_encode(['apiIdentifier' => 'api-uuid-string'])),
    ]))->store();

    /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PsPdkOrderRepository::class);

    $pdkOrder = $orderRepository->getByApiIdentifier('api-uuid-string');

    expect($pdkOrder)
        ->toBeInstanceOf(PdkOrder::class)
        ->and((int) $pdkOrder->externalIdentifier)
        ->toBe((int) $psOrder->id);
});

it('returns null when api identifier is unknown', function () {
    /** @var Order $psOrder */
    $psOrder = psFactory(Order::class)->store();

    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId((int) $psOrder->id)
            ->withData(json_encode(['apiIdentifier' => 'another-api-uuid'])),
    ]))->store();

    /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PsPdkOrderRepository::class);

    expect($orderRepository->getByApiIdentifier('missing-api-uuid'))->toBeNull();
});

it('returns null when api identifier belongs to a missing order', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(404)
            ->withData(json_encode(['apiIdentifier' => 'api-uuid-string'])),
    ]))->store();

    /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PsPdkOrderRepository $orderRepository */
    $orderRepository = Pdk::get(PsPdkOrderRepository::class);

    expect($orderRepository->getByApiIdentifier('api-uuid-string'))->toBeNull();
});

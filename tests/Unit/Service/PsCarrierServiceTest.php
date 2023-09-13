<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use RangePrice;
use RangeWeight;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

function createCarrierMappingsArray(Collection $carrierMappings): array
{
    expect($carrierMappings->all())->each->toHaveKeys(['created', 'updated']);

    return (new Collection($carrierMappings->toArray()))
        ->map(function (array $item) {
            return Arr::except($item, ['created', 'updated']);
        })
        ->toArray();
}

function doSnapshotTest(Collection $carrierMappings, Collection $psCarriers): void
{
    assertMatchesJsonSnapshot(
        json_encode([
            'psCarriers'      => $psCarriers
                ->map(function (array $data) {
                    $id        = $data['id'];
                    $psCarrier = new PsCarrier($id);

                    return array_merge($data, [
                        'zones'        => $psCarrier->getZones(),
                        'rangeWeights' => RangeWeight::getRanges($id),
                        'rangePrices'  => RangePrice::getRanges($id),
                    ]);
                })
                ->toArray(),
            'carrierMappings' => createCarrierMappingsArray($carrierMappings),
        ])
    );
}

it('creates carriers on account update', function () {
    TestBootstrapper::hasAccount();

    /** @var PsCarrierMappingRepository $repository */
    $repository      = Pdk::get(PsCarrierMappingRepository::class);
    $carrierMappings = $repository->all();
    $psCarriers      = new Collection(PsCarrier::getCarriers(0));

    doSnapshotTest($carrierMappings, $psCarriers);
});

it('does not create duplicate carriers', function () {
    TestBootstrapper::hasAccount();

    factory(Shop::class)
        ->withCarriers(
            factory(CarrierCollection::class)->push(
                ['name' => Carrier::CARRIER_POSTNL_NAME],
                ['name' => Carrier::CARRIER_DHL_FOR_YOU_NAME],
                ['name' => Carrier::CARRIER_DHL_EUROPLUS_NAME]
            )
        )
        ->store();

    /** @var PsCarrierServiceInterface $service */
    $service = Pdk::get(PsCarrierServiceInterface::class);
    $service->updateCarriers();
    $service->updateCarriers();

    /** @var PsCarrierMappingRepository $repository */
    $repository      = Pdk::get(PsCarrierMappingRepository::class);
    $carrierMappings = $repository->all();

    expect($carrierMappings->count())->toBe(3);

    $psCarriers = new Collection(PsCarrier::getCarriers(0));

    doSnapshotTest($carrierMappings, $psCarriers);
});

<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleAclResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetCarrierConfigurationResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetCarrierOptionsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Tests\Bootstrap\PsTestBootstrapper;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Psr\Log\LoggerInterface;
use RangePrice;
use RangeWeight;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

function doSnapshotTest(Collection $carrierMappings, Collection $psCarriers): void
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger    = Pdk::get(LoggerInterface::class);
    $errorLogs = array_filter($logger->getLogs(), static function (array $log) {
        return 'error' === $log['level'];
    });

    expect($errorLogs)->toBe([]);

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
                ->toArrayWithoutNull(),
            'carrierMappings' => $carrierMappings->toArray(),
        ])
    );
}

it('creates carriers on account update', function () {
    TestBootstrapper::hasAccount();
    PsTestBootstrapper::hasCarrierImages();

    MockApi::enqueue(
        new ExampleGetAccountsResponse(),
        new ExampleGetCarrierConfigurationResponse(),
        new ExampleGetCarrierOptionsResponse(),
        new ExampleAclResponse()
    );

    Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);

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

    PsTestBootstrapper::hasCarrierImages();

    /** @var PsCarrierServiceInterface $service */
    $service = Pdk::get(PsCarrierServiceInterface::class);
    $service->updateCarriers();
    $service->updateCarriers();
    $service->updateCarriers();
    $service->updateCarriers();

    /** @var PsCarrierMappingRepository $repository */
    $repository      = Pdk::get(PsCarrierMappingRepository::class);
    $carrierMappings = $repository->all();

    expect($carrierMappings->count())->toBe(3);

    $psCarriers = new Collection(PsCarrier::getCarriers(0));

    doSnapshotTest($carrierMappings, $psCarriers);
});

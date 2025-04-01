<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier as PsCarrier;
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
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePrinterGroupIdResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Psr\Log\LoggerInterface;
use RangePrice;
use RangeWeight;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function MyParcelNL\PrestaShop\setupAccountAndCarriers;
use function MyParcelNL\PrestaShop\setupCarrierActiveSettings;
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

                    $zones = (new Collection($psCarrier->getZones()))->values();

                    return array_merge($data, [
                        'zones'        => $zones->toArrayWithoutNull(),
                        'rangeWeights' => (new Collection(RangeWeight::getRanges($id)))->toArrayWithoutNull(),
                        'rangePrices'  => (new Collection(RangePrice::getRanges($id)))->toArrayWithoutNull(),
                    ]);
                })
                ->values()
                ->toArrayWithoutNull(),
            'carrierMappings' => $carrierMappings->toArrayWithoutNull(),
        ])
    );
}

it('creates carriers on account update', function () {
    $carriers = setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)
                ->fromPostNL()
                ->withHuman('PostNL'),
            factory(Carrier::class)
                ->fromDhlForYou()
                ->withContractId(8123)
                ->withHuman('Dhl custom'),
            factory(Carrier::class)
                ->fromDhlForYou()
                ->withHuman('Dhl')
        )
    );

    $carrierOptions = $carriers->map(function (Carrier $carrier) {
        return [
            'id'         => $carrier->contractId ?? $carrier->id,
            'carrier_id' => $carrier->id,
            'enabled'    => (int) $carrier->enabled,
            'type'       => $carrier->type,
        ];
    });

    MockApi::enqueue(
        new ExampleGetAccountsResponse(),
        new ExampleGetCarrierConfigurationResponse(),
        new ExampleGetCarrierOptionsResponse($carrierOptions->toArrayWithoutNull()),
        new ExampleAclResponse(),
        new ExamplePrinterGroupIdResponse([])
    );

    Actions::execute(PdkBackendActions::UPDATE_ACCOUNT);

    /** @var PsCarrierMappingRepository $repository */
    $repository      = Pdk::get(PsCarrierMappingRepository::class);
    $carrierMappings = $repository->all();
    $psCarriers      = new Collection(PsCarrier::getCarriers(0));

    expect($carrierMappings->count())
        ->toBe(3)
        ->and($psCarriers->count())
        ->toBe(3);

    doSnapshotTest($carrierMappings, $psCarriers);
});

it('does not create duplicate carriers', function () {
    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)
                ->withName('postnl')
                ->withId(12)
                ->withContractId(23)
        )
    );

    /** @var PsCarrierServiceInterface $service */
    $service = Pdk::get(PsCarrierServiceInterface::class);
    $service->updateCarriers();
    $service->updateCarriers();
    $service->updateCarriers();

    /** @var PsCarrierMappingRepository $repository */
    $repository      = Pdk::get(PsCarrierMappingRepository::class);
    $carrierMappings = $repository->all();

    $psCarriers = new Collection(PsCarrier::getCarriers(0));

    expect($psCarriers->count())
        ->toBe(1)
        ->and($carrierMappings->count())
        ->toBe(1);
});

it('updates existing carrier if mapping already exists', function () {
    (new FactoryCollection([
        psFactory(PsCarrier::class)->withId(10),
        psFactory(PsCarrier::class)->withId(11),

        psFactory(MyparcelnlCarrierMapping::class)
            ->fromPostNL()
            ->withCarrierId(10),
        psFactory(MyparcelnlCarrierMapping::class)
            ->fromDhlForYou(8123)
            ->withCarrierId(11),
    ]))->store();

    setupAccountAndCarriers(
        factory(CarrierCollection::class)->push(
            factory(Carrier::class)
                ->fromPostNL()
                ->withHuman('This Is PostNL'),
            factory(Carrier::class)
                ->fromDhlForYou()
                ->withContractId(8123)
                ->withHuman('This Is DhLForYou')
        )
    );

    /** @var PsCarrierServiceInterface $service */
    $service = Pdk::get(PsCarrierServiceInterface::class);
    $service->updateCarriers();

    // expect 2 carriers to exist
    $psCarriers = new Collection(PsCarrier::getCarriers(0));

    expect($psCarriers->count())
        ->toBe(2)
        ->and($psCarriers->first())
        ->toHaveKeysAndValues([
            'id'   => 10,
            'name' => 'This Is PostNL',
        ])
        ->and($psCarriers->last())
        ->toHaveKeysAndValues([
            'id'   => 11,
            'name' => 'This Is DhLForYou',
        ]);
});

it('enables carriers based on settings', function (array $settings, bool $result) {
    setupCarrierActiveSettings($settings)->store();

    /** @var PsCarrierServiceInterface $service */
    $service = Pdk::get(PsCarrierServiceInterface::class);
    $service->updateCarriers();

    $psCarrier = new PsCarrier(12);

    expect($psCarrier->active)->toBe($result);
})->with('carrierActiveSettings');

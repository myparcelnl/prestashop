<?php

/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,PhpUnused,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Carrier as PsCarrier;
use Cart;
use Cookie;
use Country;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockLogger;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\Sdk\Support\Arr;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

class WithHasPsCarrierListHooks
{
    use HasPsCarrierListHooks;
}

/**
 * Build a CarrierCapabilitiesRepository test double whose `getCapabilitiesForRecipientCountry()`
 * returns one stub object per V2 carrier name. The hook only reads `getCarrier()` on the result.
 */
function fakeCapabilitiesRepositoryReturning(array $v2CarrierNames): CarrierCapabilitiesRepository
{
    return new class($v2CarrierNames) extends CarrierCapabilitiesRepository {
        private array $v2CarrierNames;

        public function __construct(array $v2CarrierNames)
        {
            $this->v2CarrierNames = $v2CarrierNames;
        }

        public function getCapabilitiesForRecipientCountry(string $cc): array
        {
            return array_map(static function (string $name) {
                return new class($name) {
                    private string $name;
                    public function __construct(string $name) { $this->name = $name; }
                    public function getCarrier(): string { return $this->name; }
                };
            }, $this->v2CarrierNames);
        }
    };
}

function fakeCapabilitiesRepositoryThrowing(): CarrierCapabilitiesRepository
{
    return new class extends CarrierCapabilitiesRepository {
        public function __construct() {}

        public function getCapabilitiesForRecipientCountry(string $cc): array
        {
            throw new RuntimeException('Capabilities API returned 503');
        }
    };
}

/**
 * Standard fixture: install N carrier mappings (V2 names — Migration5_1_0 has already run by now),
 * one PS-only carrier (no mapping) that must always survive, and a delivery address in the given country.
 *
 * @return array{0: array, 1: array<string,int>}  [hookParams, v2Name => psCarrierId map]
 */
function setupModuleWithMappings(
    array  $mappingV2Names,
    string $deliveryCountryIso = 'NL',
    string $proposition = Proposition::MYPARCEL_NAME
): array {
    $propositionId = Proposition::SENDMYPARCEL_NAME === $proposition
        ? Proposition::SENDMYPARCEL_ID
        : Proposition::MYPARCEL_ID;

    Pdk::get(PropositionService::class)->setActivePropositionId($propositionId);

    factory(Account::class, $propositionId)->withShops()->store();

    $deliveryOptionCarrierList = [
        '22,' => [
            // PS-only carrier (no MyParcel mapping). Must always survive filtering.
            'carrier_list' => [['instance' => psFactory(PsCarrier::class)->store()]],
        ],
    ];

    $carrierIdMapping = [];
    $index            = 23;

    foreach ($mappingV2Names as $v2Name) {
        $psCarrier = psFactory(PsCarrier::class)->store();

        psFactory(MyparcelnlCarrierMapping::class)
            ->withCarrierId($psCarrier->id)
            ->withMyparcelCarrier($v2Name)
            ->store();

        $carrierIdMapping[$v2Name]            = $psCarrier->id;
        $deliveryOptionCarrierList["$index,"] = ['carrier_list' => [['instance' => $psCarrier]]];
        $index++;
    }

    $deliveryAddress = psFactory(Address::class)
        ->withIdCountry(Country::getByIso($deliveryCountryIso))
        ->store();

    $params = [
        'altern'               => 1,
        'cookie'               => psFactory(Cookie::class)->make(),
        'cart'                 => psFactory(Cart::class)
            ->withAddressDelivery($deliveryAddress->id)
            ->make(),
        'delivery_option_list' => [2 => $deliveryOptionCarrierList],
    ];

    return [$params, $carrierIdMapping];
}

function survivingV2Names(array $params, array $carrierIdMapping): array
{
    $carriers   = Arr::first($params['delivery_option_list']);
    $flippedMap = array_flip($carrierIdMapping);

    return array_values(array_filter(array_map(static function ($carrier) use ($flippedMap) {
        $first = Arr::first($carrier['carrier_list']);

        return $flippedMap[$first['instance']->id] ?? null;
    }, $carriers)));
}

it('drops carriers that capabilities does not list for the cart country', function () {
    [$params, $map] = setupModuleWithMappings([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        RefCapabilitiesSharedCarrierV2::DPD,
    ]);

    $reset = mockPdkProperty(
        CarrierCapabilitiesRepository::class,
        fakeCapabilitiesRepositoryReturning([RefCapabilitiesSharedCarrierV2::POSTNL])
    );

    try {
        (new WithHasPsCarrierListHooks())->hookActionFilterDeliveryOptionList($params);

        expect(survivingV2Names($params, $map))->toEqual([RefCapabilitiesSharedCarrierV2::POSTNL]);
        expect(Arr::first($params['delivery_option_list']))->toHaveLength(2); // PS-only carrier + PostNL
    } finally {
        $reset();
    }
});

it('keeps every mapped carrier when capabilities lists them all', function () {
    [$params, $map] = setupModuleWithMappings([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        RefCapabilitiesSharedCarrierV2::DPD,
    ]);

    $reset = mockPdkProperty(
        CarrierCapabilitiesRepository::class,
        fakeCapabilitiesRepositoryReturning([
            RefCapabilitiesSharedCarrierV2::POSTNL,
            RefCapabilitiesSharedCarrierV2::DPD,
        ])
    );

    try {
        (new WithHasPsCarrierListHooks())->hookActionFilterDeliveryOptionList($params);

        expect(survivingV2Names($params, $map))->toEqual([
            RefCapabilitiesSharedCarrierV2::POSTNL,
            RefCapabilitiesSharedCarrierV2::DPD,
        ]);
    } finally {
        $reset();
    }
});

it('drops every mapped carrier when capabilities returns an empty set', function () {
    [$params, $map] = setupModuleWithMappings([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        RefCapabilitiesSharedCarrierV2::DPD,
    ]);

    $reset = mockPdkProperty(
        CarrierCapabilitiesRepository::class,
        fakeCapabilitiesRepositoryReturning([])
    );

    try {
        (new WithHasPsCarrierListHooks())->hookActionFilterDeliveryOptionList($params);

        expect(survivingV2Names($params, $map))->toEqual([]);
        expect(Arr::first($params['delivery_option_list']))->toHaveLength(1); // only the PS-only carrier
    } finally {
        $reset();
    }
});

it('keeps all carriers (fail-open) and logs an error when the capabilities call throws', function () {
    [$params, $map] = setupModuleWithMappings([
        RefCapabilitiesSharedCarrierV2::POSTNL,
        RefCapabilitiesSharedCarrierV2::DPD,
    ]);

    $reset = mockPdkProperty(
        CarrierCapabilitiesRepository::class,
        fakeCapabilitiesRepositoryThrowing()
    );

    try {
        (new WithHasPsCarrierListHooks())->hookActionFilterDeliveryOptionList($params);

        expect(survivingV2Names($params, $map))->toEqual([
            RefCapabilitiesSharedCarrierV2::POSTNL,
            RefCapabilitiesSharedCarrierV2::DPD,
        ]);

        /** @var MockLogger $logger */
        $logger = Pdk::get(LoggerInterface::class);
        $errorLogs = array_filter($logger->getLogs(), static function (array $log) {
            return 'error' === $log['level'];
        });

        expect($errorLogs)->not->toBeEmpty();
    } finally {
        $reset();
    }
});

it('does nothing and never calls capabilities when there are no carrier mappings', function () {
    [$params] = setupModuleWithMappings([]);

    // Throwing repository: if the hook calls capabilities, the test fails on uncaught exception.
    $reset = mockPdkProperty(
        CarrierCapabilitiesRepository::class,
        fakeCapabilitiesRepositoryThrowing()
    );

    try {
        (new WithHasPsCarrierListHooks())->hookActionFilterDeliveryOptionList($params);

        expect(Arr::first($params['delivery_option_list']))->toHaveLength(1); // only the PS-only carrier
    } finally {
        $reset();
    }
});

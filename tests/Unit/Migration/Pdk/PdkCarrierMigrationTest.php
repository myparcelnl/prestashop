<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('migrates carriers to pdk', function (
    array $factories = [],
    array $configurationRows = [],
    array $expected = []
) {
    (new FactoryCollection($factories))->store();

    MockPsDb::insertRows(
        'configuration',
        array_map(
            function ($name, $value) {
                return ['name' => $name, 'value' => $value];
            },
            array_keys($configurationRows),
            $configurationRows
        ),
        'id_configuration'
    );

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkCarrierMigration $migration */
    $migration = Pdk::get(PdkCarrierMigration::class);
    $migration->up();

    /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $repo */
    $repo        = Pdk::get(PsCarrierMappingRepository::class);
    $allMappings = $repo->all();

    expect($allMappings->toArray())->toBe($expected);
})->with([
    'migrate carriers' => [
        'factories'     => function () {
            return [
                factory(PsCarrier::class)->withId(21),
                factory(PsCarrier::class)->withId(22),
                factory(PsCarrier::class)->withId(23),
                factory(PsCarrier::class)->withId(24),
            ];
        },
        'configuration' => [
            'MYPARCELNL_POSTNL'    => '21',
            'MYPARCELNL_DHLFORYOU' => '22',
            'MYPARCELNL_BPOST'     => '24',
        ],
        'expected'      => [
            [
                MyparcelnlCarrierMapping::CARRIER_ID       => 21,
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            ],
            [
                MyparcelnlCarrierMapping::CARRIER_ID       => 22,
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME,
            ],
            [
                MyparcelnlCarrierMapping::CARRIER_ID       => 24,
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => Carrier::CARRIER_BPOST_LEGACY_NAME,
            ],
        ],
    ],

    'ignores carriers that do not exist' => [
        'factories'     => function () {
            return [
                factory(PsCarrier::class)->withId(10),
            ];
        },
        'configuration' => [
            'MYPARCELNL_DHLFORYOU' => '26',
        ],
        'expected'      => [],
    ],

    'ignores carriers that already have a mapping' => [
        'factories'     => function () {
            return [
                factory(PsCarrier::class)->withId(21),
                factory(MyparcelnlCarrierMapping::class)
                    ->withMyparcelCarrier(Carrier::CARRIER_POSTNL_LEGACY_NAME)
                    ->withCarrierId(40),
            ];
        },
        'configuration' => [
            'MYPARCELNL_POSTNL' => '21',
        ],
        'expected'      => [
            [
                MyparcelnlCarrierMapping::CARRIER_ID       => 40,
                MyparcelnlCarrierMapping::MYPARCEL_CARRIER => Carrier::CARRIER_POSTNL_LEGACY_NAME,
            ],
        ],
    ],
]);

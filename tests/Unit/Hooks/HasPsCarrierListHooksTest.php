<?php

/** @noinspection AutoloadingIssuesInspection,AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,PhpUnused,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Carrier as PsCarrier;
use Cart;
use Cookie;
use Country;
use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Facade\Platform as PlatformFacade;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use MyParcelNL\Sdk\Support\Arr;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

class WithHasPsCarrierListHooks
{
    use HasPsCarrierListHooks;
}

it('filters carriers from delivery options list', function (
    string $isoCode,
    array  $filteredCarriers,
    string $platform = Platform::MYPARCEL_NAME
) {
    $platformId = Platform::MYPARCEL_ID;
    if (Platform::SENDMYPARCEL_NAME === $platform) {
        $platformId = Platform::SENDMYPARCEL_ID;
    }
    factory(Account::class, $platformId)
        ->withShops()
        ->store();


    $allCarriers = [
        Carrier::CARRIER_BPOST_NAME,
        Carrier::CARRIER_DHL_FOR_YOU_NAME,
        sprintf('%s:1234', Carrier::CARRIER_DHL_FOR_YOU_NAME),
        Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
        Carrier::CARRIER_DPD_NAME,
        Carrier::CARRIER_POSTNL_NAME,
        Carrier::CARRIER_UPS_NAME,
    ];

    $deliveryOptionCarrierList = [
        '22,' => [
            // Some option for a built-in carrier
            'carrier_list' => [
                ['instance' => psFactory(PsCarrier::class)->store()],
            ],
        ],
    ];

    $carrierIdMapping = [];
    // start after the last index in the carrier list
    $index = 23;

    foreach ($allCarriers as $carrierName) {
        // Create a PsCarrier
        $psCarrier = psFactory(PsCarrier::class)->store();

        // Add the mapping
        psFactory(MyparcelnlCarrierMapping::class)
            ->withCarrierId($psCarrier->id)
            ->withMyparcelCarrier($carrierName)
            ->store();

        // Map the carrier name to the PsCarrier id
        $carrierIdMapping[$carrierName] = $psCarrier->id;

        // Add the carrier to the mocked delivery options list
        $deliveryOptionCarrierList["$index,"] = [
            'carrier_list' => [['instance' => $psCarrier]],
        ];

        $index++;
    }

    // Create a delivery address for the current country
    $deliveryAddress = psFactory(Address::class)
        ->withIdCountry(Country::getByIso($isoCode))
        ->store();

    // Create a mocked params array that resembles the one passed to the hook.
    $params = [
        'altern'               => 1,
        'cookie'               => psFactory(Cookie::class)->make(),
        'cart'                 => psFactory(Cart::class)
            ->withAddressDelivery($deliveryAddress->id)
            ->make(),
        'delivery_option_list' => [
            2 => $deliveryOptionCarrierList,
        ],
    ];

    $class = new WithHasPsCarrierListHooks();

    // Should delete some carriers, modifies the array in place.
    $class->hookActionFilterDeliveryOptionList($params);

    $carriers = Arr::first($params['delivery_option_list']);

    $flippedMap           = array_flip($carrierIdMapping);
    $filteredCarrierNames = array_values(array_filter(array_map(function ($carrier) use ($flippedMap) {
        $carrierList  = $carrier['carrier_list'];
        $firstCarrier = Arr::first($carrierList);

        return $flippedMap[$firstCarrier['instance']->id] ?? null;
    }, $carriers)));

    expect($filteredCarrierNames)
        ->toEqual($filteredCarriers)
        ->and($carriers)
        ->toHaveLength(count($filteredCarriers) + 1);
})->with([
    'NL' => [
        'country'          => 'NL',
        'filteredCarriers' => [
            Carrier::CARRIER_DHL_FOR_YOU_NAME,
            Carrier::CARRIER_DHL_FOR_YOU_NAME . ':1234',
            Carrier::CARRIER_DPD_NAME,
            Carrier::CARRIER_POSTNL_NAME,
            Carrier::CARRIER_UPS_NAME,
        ],
    ],

    'BE' => [
        'country'          => 'BE',
        'filteredCarriers' => [
            Carrier::CARRIER_DHL_FOR_YOU_NAME,
            Carrier::CARRIER_DHL_FOR_YOU_NAME . ':1234',
            Carrier::CARRIER_DPD_NAME,
            Carrier::CARRIER_POSTNL_NAME,
            Carrier::CARRIER_UPS_NAME,
        ],
    ],

    'BE (sendmyparcel)' => [
        'country'          => 'BE',
        'filteredCarriers' => [
            Carrier::CARRIER_BPOST_NAME,
            Carrier::CARRIER_DPD_NAME,
            Carrier::CARRIER_POSTNL_NAME,
        ],
        'platform'         => Platform::SENDMYPARCEL_NAME,
    ],

    'FR' => [
        'country'          => 'FR',
        'filteredCarriers' => [
            Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
            Carrier::CARRIER_DPD_NAME,
            Carrier::CARRIER_POSTNL_NAME,
            Carrier::CARRIER_UPS_NAME,
        ],
    ],

    'US' => [
        'country'          => 'US',
        'filteredCarriers' => [
            Carrier::CARRIER_POSTNL_NAME,
            Carrier::CARRIER_UPS_NAME,
        ],
    ],

    'AX' => [
        'country'          => 'AX',
        'filteredCarriers' => [
            Carrier::CARRIER_POSTNL_NAME,
            Carrier::CARRIER_UPS_NAME,
        ],
    ],
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\PrestaShop\Migration\AbstractLegacyPsMigration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

it('migrates carriers and their settings to pdk', function (array $carrierSettings, array $expectations) {
    psFactory(PsCarrier::class)
        ->withId(48)
        ->store();

    psFactory(PsCarrier::class)
        ->withId(49)
        ->store();

    MockPsDb::insertRows(
        AbstractLegacyPsMigration::LEGACY_TABLE_CARRIER_CONFIGURATION,
        $carrierSettings,
        'id_configuration'
    );

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkSettingsMigration $migration */
    $migration = Pdk::get(PdkSettingsMigration::class);
    $migration->up();

    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

    $settings = $settingsRepository->all()->carrier->toArrayWithoutNull();

    expect($settings)->toHaveKeys(array_keys($expectations));

    foreach ($expectations as $carrier => $keyValues) {
        expect($settings[$carrier])->toHaveKeysAndValues($keyValues);
    }
})->with([
    '=' => [
        'carrierSettings' => [
            ['id_carrier' => 48, 'name' => 'carrierType', 'value' => 'postnl'],
            ['id_carrier' => 48, 'name' => 'dropOffDays', 'value' => null],
            ['id_carrier' => 48, 'name' => 'cutoff_exceptions', 'value' => null],
            ['id_carrier' => 48, 'name' => 'mondayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'tuesdayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'wednesdayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'thursdayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'fridayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'saturdayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'sundayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'deliveryDaysWindow', 'value' => null],
            ['id_carrier' => 48, 'name' => 'dropOffDelay', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowMondayDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceMondayDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'saturdayCutoffTime', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowMorningDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceMorningDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowEveningDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceEveningDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowSaturdayDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceSaturdayDelivery', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowSignature', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceSignature', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowOnlyRecipient', 'value' => null],
            ['id_carrier' => 48, 'name' => 'priceOnlyRecipient', 'value' => null],
            ['id_carrier' => 48, 'name' => 'allowPickupPoints', 'value' => null],
            ['id_carrier' => 48, 'name' => 'pricePickup', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_PACKAGE_TYPE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_AGE_CHECK', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_RETURN_PACKAGE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_SIGNATURE_REQUIRED', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_INSURANCE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => null],
            ['id_carrier' => 48, 'name' => 'MYPARCELNL_RECIPIENT_ONLY', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_PACKAGE_TYPE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_RECIPIENT_ONLY', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_AGE_CHECK', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_PACKAGE_FORMAT', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_RETURN_PACKAGE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_SIGNATURE_REQUIRED', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_INSURANCE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => null],
            ['id_carrier' => 48, 'name' => 'return_label_description', 'value' => null],
            ['id_carrier' => 49, 'name' => 'carrierType', 'value' => 'dhlforyou'],
            ['id_carrier' => 49, 'name' => 'dropOffDays', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'cutoff_exceptions', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'mondayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'tuesdayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'wednesdayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'thursdayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'fridayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'saturdayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'sundayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'deliveryDaysWindow', 'value' => '-1'],
            ['id_carrier' => 49, 'name' => 'dropOffDelay', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'allowMondayDelivery', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'priceMondayDelivery', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'saturdayCutoffTime', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'allowMorningDelivery', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'priceMorningDelivery', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'allowEveningDelivery', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'priceEveningDelivery', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'allowSaturdayDelivery', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'priceSaturdayDelivery', 'value' => ''],
            ['id_carrier' => 49, 'name' => 'allowSignature', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'priceSignature', 'value' => '0.29'],
            ['id_carrier' => 49, 'name' => 'allowOnlyRecipient', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'priceOnlyRecipient', 'value' => '1.29'],
            ['id_carrier' => 49, 'name' => 'allowPickupPoints', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'pricePickup', 'value' => '4.95'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_PACKAGE_TYPE', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_AGE_CHECK', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_RETURN_PACKAGE', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_SIGNATURE_REQUIRED', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_INSURANCE', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => '10'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => '100'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => '1000'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => '10000'],
            ['id_carrier' => 49, 'name' => 'MYPARCELNL_RECIPIENT_ONLY', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_PACKAGE_TYPE', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_RECIPIENT_ONLY', 'value' => '1'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_AGE_CHECK', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_PACKAGE_FORMAT', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_RETURN_PACKAGE', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_SIGNATURE_REQUIRED', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_INSURANCE', 'value' => '0'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => '211'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => '212'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => '213'],
            ['id_carrier' => 49, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => '214'],
        ],

        'expectations' => [
            Carrier::CARRIER_POSTNL_NAME      => [],
            Carrier::CARRIER_DHL_FOR_YOU_NAME => [],
        ],
    ],

    'dropoff possibilities' => [
        [
            ['id_carrier' => 48, 'name' => 'carrierType', 'value' => 'postnl'],
            ['id_carrier' => 48, 'name' => 'dropOffDays', 'value' => '1,2,3,4,5'],
            ['id_carrier' => 48, 'name' => 'mondayCutoffTime', 'value' => '16:01'],
            ['id_carrier' => 48, 'name' => 'tuesdayCutoffTime', 'value' => '16:02'],
            ['id_carrier' => 48, 'name' => 'wednesdayCutoffTime', 'value' => '16:03'],
            ['id_carrier' => 48, 'name' => 'thursdayCutoffTime', 'value' => '16:04'],
            ['id_carrier' => 48, 'name' => 'fridayCutoffTime', 'value' => '16:05'],
            ['id_carrier' => 48, 'name' => 'saturdayCutoffTime', 'value' => '16:06'],
            ['id_carrier' => 48, 'name' => 'sundayCutoffTime', 'value' => '16:07'],
        ],

        'expectations' => [
            Carrier::CARRIER_POSTNL_NAME => [
                CarrierSettings::DROP_OFF_POSSIBILITIES => [
                    'dropOffDays'           => [
                        [
                            'weekday'           => 0,
                            'dispatch'          => false,
                            'cutoffTime'        => '16:07',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 1,
                            'dispatch'          => true,
                            'cutoffTime'        => '16:01',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 2,
                            'dispatch'          => true,
                            'cutoffTime'        => '16:02',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 3,
                            'dispatch'          => true,
                            'cutoffTime'        => '16:03',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 4,
                            'dispatch'          => true,
                            'cutoffTime'        => '16:04',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 5,
                            'dispatch'          => true,
                            'cutoffTime'        => '16:05',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                        [
                            'weekday'           => 6,
                            'dispatch'          => false,
                            'cutoffTime'        => '16:06',
                            'date'              => null,
                            'sameDayCutoffTime' => '10:00',
                        ],
                    ],
                    'dropOffDaysDeviations' => [],
                ],
            ],
        ],
    ],
]);

it('migrates plugin settings to pdk', function () {
    // todo

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkSettingsMigration $migration */
    $migration = Pdk::get(PdkSettingsMigration::class);
    $migration->up();

    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);
    $allSettings        = $settingsRepository->all();

    assertMatchesJsonSnapshot(
        json_encode($allSettings->toArrayWithoutNull())
    );
})->skip();

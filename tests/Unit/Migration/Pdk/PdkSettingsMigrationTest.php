<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('migrates settings to pdk', function () {
    psFactory(PsCarrier::class)
        ->withId(4)
        ->store();

    psFactory(PsCarrier::class)
        ->withId(8)
        ->store();

    MockPsDb::insertRows(
        AbstractPsMigration::LEGACY_TABLE_CARRIER_CONFIGURATION,
        [
            // Carrier 4
            ['id_carrier' => 4, 'name' => 'carrierType', 'value' => 'postnl'],

            ['id_carrier' => 4, 'name' => 'allowMorningDelivery', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceMorningDelivery', 'value' => '2.19'],

            ['id_carrier' => 4, 'name' => 'allowEveningDelivery', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceEveningDelivery', 'value' => '2.49'],

            ['id_carrier' => 4, 'name' => 'allowPickupPoints', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'pricePickup', 'value' => '-1.00'],

            ['id_carrier' => 4, 'name' => 'allowMondayDelivery', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceMondayDelivery', 'value' => '2.29'],

            ['id_carrier' => 4, 'name' => 'allowSaturdayDelivery', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceSaturdayDelivery', 'value' => '2.99'],

            ['id_carrier' => 4, 'name' => 'allowOnlyRecipient', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceOnlyRecipient', 'value' => '0.99'],

            ['id_carrier' => 4, 'name' => 'allowSignature', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'priceSignature', 'value' => '0.59'],

            [
                'id_carrier' => 4,
                'name'       => 'cutoff_exceptions',
                'value'      => '{"27-10-2023":{"nodispatch":true,"cutoff":"10:00"},"29-10-2023":{"nodispatch":true},"30-10-2023":{"nodispatch":true}}',
            ],

            ['id_carrier' => 4, 'name' => 'deliveryDaysWindow', 'value' => '3'],
            ['id_carrier' => 4, 'name' => 'dropOffDays', 'value' => '1,2,3,4,5'],
            ['id_carrier' => 4, 'name' => 'dropOffDelay', 'value' => '2'],

            ['id_carrier' => 4, 'name' => 'mondayCutoffTime', 'value' => '16:01'],
            ['id_carrier' => 4, 'name' => 'tuesdayCutoffTime', 'value' => '16:02'],
            ['id_carrier' => 4, 'name' => 'wednesdayCutoffTime', 'value' => '16:03'],
            ['id_carrier' => 4, 'name' => 'thursdayCutoffTime', 'value' => '16:04'],
            ['id_carrier' => 4, 'name' => 'fridayCutoffTime', 'value' => '16:05'],
            ['id_carrier' => 4, 'name' => 'saturdayCutoffTime', 'value' => '16:06'],
            ['id_carrier' => 4, 'name' => 'sundayCutoffTime', 'value' => '16:07'],

            ['id_carrier' => 4, 'name' => 'MYPARCELNL_AGE_CHECK', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_INSURANCE', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => '50000'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => '20000'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => '10000'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_PACKAGE_TYPE', 'value' => '3'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_RECIPIENT_ONLY', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_RETURN_PACKAGE', 'value' => '1'],
            ['id_carrier' => 4, 'name' => 'MYPARCELNL_SIGNATURE_REQUIRED', 'value' => '1'],

            // Carrier 8
            ['id_carrier' => 8, 'name' => 'carrierType', 'value' => 'dhlforyou'],
            ['id_carrier' => 8, 'name' => 'allowEveningDelivery', 'value' => '0'],
            ['id_carrier' => 8, 'name' => 'priceEveningDelivery', 'value' => '2.00'],

            ['id_carrier' => 8, 'name' => 'allowMondayDelivery', 'value' => '0'],
            ['id_carrier' => 8, 'name' => 'priceMondayDelivery', 'value' => '1.80'],

            ['id_carrier' => 8, 'name' => 'allowMorningDelivery', 'value' => '0'],
            ['id_carrier' => 8, 'name' => 'priceMorningDelivery', 'value' => '2.30'],

            ['id_carrier' => 8, 'name' => 'allowOnlyRecipient', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'priceOnlyRecipient', 'value' => '0.90'],

            ['id_carrier' => 8, 'name' => 'allowPickupPoints', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'pricePickup', 'value' => '0.00'],

            ['id_carrier' => 8, 'name' => 'allowSaturdayDelivery', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'priceSaturdayDelivery', 'value' => '3.00'],

            ['id_carrier' => 8, 'name' => 'allowSignature', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'priceSignature', 'value' => '0.40'],

            ['id_carrier' => 8, 'name' => 'cutoff_exceptions', 'value' => ''],
            ['id_carrier' => 8, 'name' => 'deliveryDaysWindow', 'value' => '-1'],
            ['id_carrier' => 8, 'name' => 'dropOffDelay', 'value' => '0'],

            ['id_carrier' => 8, 'name' => 'saturdayCutoffTime', 'value' => '14:00'],

            ['id_carrier' => 8, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '2'],
            ['id_carrier' => 8, 'name' => 'MYPARCELNL_PACKAGE_TYPE', 'value' => '2'],

            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_AGE_CHECK', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_INSURANCE', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_INSURANCE_FROM_PRICE', 'value' => '211'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT', 'value' => '212'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_BE', 'value' => '213'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_EU', 'value' => '214'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_PACKAGE_FORMAT', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_PACKAGE_TYPE', 'value' => '2'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_RECIPIENT_ONLY', 'value' => '1'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_RETURN_PACKAGE', 'value' => '3'],
            ['id_carrier' => 8, 'name' => 'return_MYPARCELNL_SIGNATURE_REQUIRED', 'value' => '1'],
        ],
        'id_configuration'
    );

    MockPsDb::insertRows(
        'configuration',
        [
            ['name' => 'MYPARCELNL_API_KEY', 'value' => 'api_key'],
            ['name' => 'MYPARCELNL_USE_ADDRESS2_AS_STREET_NUMBER', 'value' => '1'],
            ['name' => 'MYPARCELNL_DELIVERY_OPTIONS_PRICE_FORMAT', 'value' => 'surcharge'],
            ['name' => 'MYPARCELNL_WEBHOOK_HASH', 'value' => 'webhook_hash'],
            ['name' => 'MYPARCELNL_DELIVERY_TITLE', 'value' => 'delivery options here'],
            ['name' => 'MYPARCELNL_DELIVERY_STANDARD_TITLE', 'value' => 'home'],
            ['name' => 'MYPARCELNL_DELIVERY_MORNING_TITLE', 'value' => 'morning'],
            ['name' => 'MYPARCELNL_DELIVERY_EVENING_TITLE', 'value' => 'evening'],
            ['name' => 'MYPARCELNL_SATURDAY_DELIVERY_TITLE', 'value' => 'saturday'],
            ['name' => 'MYPARCELNL_SIGNATURE_TITLE', 'value' => 'signature'],
            ['name' => 'MYPARCELNL_ONLY_RECIPIENT_TITLE', 'value' => 'only recipient'],
            ['name' => 'MYPARCELNL_PICKUP_TITLE', 'value' => 'Afhaalpunt'],
            ['name' => 'MYPARCELNL_HOUSE_NUMBER_TITLE', 'value' => 'Huisnummer'],
            ['name' => 'MYPARCELNL_CITY_TITLE', 'value' => 'city'],
            ['name' => 'MYPARCELNL_POSTCODE_TITLE', 'value' => 'postal code'],
            ['name' => 'MYPARCELNL_CC', 'value' => 'country'],
            ['name' => 'MYPARCELNL_OPENING_HOURS_TITLE', 'value' => 'opening hours'],
            ['name' => 'MYPARCELNL_LOAD_MORE_TITLE', 'value' => 'load more'],
            ['name' => 'MYPARCELNL_PICKUP_MAP_TITLE', 'value' => 'map'],
            ['name' => 'MYPARCELNL_PICKUP_LIST_TITLE', 'value' => 'list'],
            ['name' => 'MYPARCELNL_RETRY_TITLE', 'value' => 'retry'],
            ['name' => 'MYPARCELNL_ADDRESS_NOT_FOUND_TITLE', 'value' => 'not found'],
            ['name' => 'MYPARCELNL_WRONG_POSTAL_CODE_CITY_TITLE', 'value' => 'not found'],
            ['name' => 'MYPARCELNL_WRONG_NUMBER_POSTAL_CODE', 'value' => 'not found'],
            ['name' => 'MYPARCELNL_FROM_TITLE', 'value' => 'from'],
            ['name' => 'MYPARCELNL_DISCOUNT_TITLE', 'value' => 'discount'],
            ['name' => 'MYPARCELNL_CONCEPT_FIRST', 'value' => '1'],
            ['name' => 'MYPARCELNL_LABEL_DESCRIPTION', 'value' => '{order.id} {order.reference}'],
            ['name' => 'MYPARCELNL_LABEL_SIZE', 'value' => 'a6'],
            ['name' => 'MYPARCELNL_LABEL_POSITION', 'value' => '3'],
            ['name' => 'MYPARCELNL_LABEL_PROMPT_POSITION', 'value' => '1'],
            ['name' => 'MYPARCELNL_POSTNL', 'value' => '4'],
            ['name' => 'MYPARCELNL_DHLFORYOU', 'value' => '8'],
            ['name' => 'MYPARCELNL_API_LOGGING', 'value' => '1'],
            ['name' => 'MYPARCELNL_WEBHOOK_ID', 'value' => '123456'],
            ['name' => 'MYPARCELNL_SHARE_CUSTOMER_EMAIL', 'value' => '1'],
            ['name' => 'MYPARCELNL_SHARE_CUSTOMER_PHONE', 'value' => '1'],
            ['name' => 'MYPARCELNL_LABEL_OPEN_DOWNLOAD', 'value' => 'false'],
            ['name' => 'MYPARCELNL_LABEL_CREATED_ORDER_STATUS', 'value' => '3'],
            ['name' => 'MYPARCELNL_LABEL_SCANNED_ORDER_STATUS', 'value' => null],
            ['name' => 'MYPARCELNL_DELIVERED_ORDER_STATUS', 'value' => '5'],
            ['name' => 'MYPARCELNL_IGNORE_ORDER_STATUS', 'value' => '8,6'],
            ['name' => 'MYPARCELNL_STATUS_CHANGE_MAIL', 'value' => '1'],
            ['name' => 'MYPARCELNL_ORDER_NOTIFICATION_AFTER', 'value' => 'first_scan'],
            ['name' => 'MYPARCELNL_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS', 'value' => '1'],
            ['name' => 'MYPARCELNL_CUSTOMS_FORM', 'value' => 'Add'],
            ['name' => 'MYPARCELNL_DEFAULT_CUSTOMS_CODE', 'value' => '1234'],
            ['name' => 'MYPARCELNL_DEFAULT_CUSTOMS_ORIGIN', 'value' => 'DE'],
        ],
        'id_configuration'
    );

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkSettingsMigration $migration */
    $migration = Pdk::get(PdkSettingsMigration::class);
    $migration->up();

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

    $settings = $settingsRepository
        ->all()
        ->toArrayWithoutNull();

    // Migration outcomes. We assert only the keys the migration is responsible for —
    // PDK-provided defaults are intentionally not asserted here so this test does not
    // churn when PDK adds new fields.
    $expected = [
        'account.apiKey'                            => 'api_key',
        'account.apiKeyValid'                       => true,

        'order.conceptShipments'                    => true,
        'order.statusOnLabelCreate'                 => 'status_3',
        'order.statusWhenDelivered'                 => 'status_5',

        'label.description'                         => '[ORDER_ID] [ORDER_ID]',
        'label.format'                              => 'a6',
        'label.output'                              => 'open',
        'label.position'                            => 3,
        'label.prompt'                              => true,

        'customs.countryOfOrigin'                   => 'DE',
        'customs.customsCode'                       => '1234',
        'customs.packageContents'                   => '1',

        'checkout.deliveryOptionsHeader'            => 'delivery options here',
        'checkout.priceType'                        => 'surcharge',

        // Carrier 4 → postnl
        'carrier.postnl.cutoffTime'                 => '16:00',
        'carrier.postnl.defaultPackageType'         => 'letter',
        'carrier.postnl.deliveryDaysWindow'         => 3,
        'carrier.postnl.dropOffDelay'               => 2,
        'carrier.postnl.exportAgeCheck'             => 1,
        'carrier.postnl.exportInsurance'            => 1,
        'carrier.postnl.exportInsuranceFromAmount'  => 100,
        'carrier.postnl.exportInsuranceUpTo'        => 5000000,
        'carrier.postnl.exportInsuranceUpToEu'      => 1000000,
        'carrier.postnl.exportInsuranceUpToUnique'  => 2000000,
        'carrier.postnl.exportLargeFormat'          => 1,
        'carrier.postnl.exportOnlyRecipient'        => 1,
        'carrier.postnl.exportReturn'               => 1,
        'carrier.postnl.exportSignature'            => 1,
        'carrier.postnl.allowEveningDelivery'       => true,
        'carrier.postnl.allowMondayDelivery'        => true,
        'carrier.postnl.allowMorningDelivery'      => true,
        'carrier.postnl.allowOnlyRecipient'         => true,
        'carrier.postnl.allowPickupLocations'       => true,
        'carrier.postnl.allowSaturdayDelivery'      => true,
        'carrier.postnl.allowSignature'             => true,
        'carrier.postnl.priceDeliveryTypeEvening'   => 249.0,
        'carrier.postnl.priceDeliveryTypeMonday'    => 229.0,
        'carrier.postnl.priceDeliveryTypeMorning'   => 219.0,
        'carrier.postnl.priceDeliveryTypePickup'    => -100.0,
        'carrier.postnl.priceDeliveryTypeSaturday'  => 299.0,
        'carrier.postnl.priceOnlyRecipient'         => 99.0,
        'carrier.postnl.priceSignature'             => 59.0,

        // Carrier 8 → dhlforyou
        'carrier.dhlforyou.cutoffTime'              => '16:00',
        'carrier.dhlforyou.defaultPackageType'      => 'mailbox',
        'carrier.dhlforyou.deliveryDaysWindow'      => -1,
        'carrier.dhlforyou.dropOffDelay'            => 0,
        'carrier.dhlforyou.exportLargeFormat'       => 1,
        'carrier.dhlforyou.exportReturnLargeFormat' => 1,
        'carrier.dhlforyou.exportReturnPackageType' => 'mailbox',
        'carrier.dhlforyou.allowOnlyRecipient'      => true,
        'carrier.dhlforyou.allowPickupLocations'    => true,
        'carrier.dhlforyou.allowSaturdayDelivery'   => true,
        'carrier.dhlforyou.allowSignature'          => true,
        'carrier.dhlforyou.priceDeliveryTypeEvening' => 200.0,
        'carrier.dhlforyou.priceDeliveryTypeMonday' => 180.0,
        'carrier.dhlforyou.priceDeliveryTypeMorning' => 230.0,
        'carrier.dhlforyou.priceDeliveryTypePickup' => 0.0,
        'carrier.dhlforyou.priceDeliveryTypeSaturday' => 300.0,
        'carrier.dhlforyou.priceOnlyRecipient'      => 90.0,
        'carrier.dhlforyou.priceSignature'          => 40.0,
    ];

    foreach ($expected as $path => $value) {
        expect(Arr::get($settings, $path), $path)->toBe($value);
    }
});

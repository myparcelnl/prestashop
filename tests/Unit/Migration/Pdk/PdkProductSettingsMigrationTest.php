<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsDb;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Product;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

it('migrates product settings to pdk', function (array $productConfigurations, array $result) {
    /** @var PdkProductRepositoryInterface $pdkProductRepository */
    $pdkProductRepository = Pdk::get(PdkProductRepositoryInterface::class);

    psFactory(Product::class)
        ->withId(1)
        ->store();

    MockPsDb::insertRows(
        AbstractPsMigration::LEGACY_TABLE_PRODUCT_CONFIGURATION,
        $productConfigurations,
        'id_configuration'
    );

    /** @var \MyParcelNL\PrestaShop\Migration\Pdk\PdkOrderShipmentsMigration $migration */
    $migration = Pdk::get(PdkProductSettingsMigration::class);
    $migration->up();
    $migration->up(); // done twice to test that it doesn't migrate product settings twice

    $pdkProduct = $pdkProductRepository->getProduct(1);

    expect($pdkProduct->settings->toStorableArray())->toEqual(
        array_replace([
            ProductSettings::COUNTRY_OF_ORIGIN        => TriStateService::INHERIT,
            ProductSettings::CUSTOMS_CODE             => TriStateService::INHERIT,
            ProductSettings::DISABLE_DELIVERY_OPTIONS => TriStateService::INHERIT,
            ProductSettings::DROP_OFF_DELAY           => TriStateService::INHERIT,
            ProductSettings::EXPORT_AGE_CHECK         => TriStateService::INHERIT,
            ProductSettings::EXPORT_HIDE_SENDER       => TriStateService::INHERIT,
            ProductSettings::EXPORT_INSURANCE         => TriStateService::INHERIT,
            ProductSettings::EXPORT_LARGE_FORMAT      => TriStateService::INHERIT,
            ProductSettings::EXPORT_ONLY_RECIPIENT    => TriStateService::INHERIT,
            ProductSettings::EXPORT_RETURN            => TriStateService::INHERIT,
            ProductSettings::EXPORT_SIGNATURE         => TriStateService::INHERIT,
            ProductSettings::EXPORT_TRACKED           => TriStateService::INHERIT,
            ProductSettings::FIT_IN_DIGITAL_STAMP     => TriStateService::INHERIT,
            ProductSettings::FIT_IN_MAILBOX           => TriStateService::INHERIT,
            ProductSettings::PACKAGE_TYPE             => TriStateService::INHERIT,
            ProductSettings::EXCLUDE_PARCEL_LOCKERS   => TriStateService::INHERIT,
            ProductSettings::EXPORT_FRESH_FOOD        => TriStateService::INHERIT,
            ProductSettings::EXPORT_FROZEN            => TriStateService::INHERIT,
        ], $result)
    );
})->with([
    'all options' => [
        'rows' => [
            ['id_product' => 1, 'name' => 'MYPARCELNL_AGE_CHECK', 'value' => '1'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_CUSTOMS_CODE', 'value' => '123'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_CUSTOMS_FORM', 'value' => 'Add'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_CUSTOMS_ORIGIN', 'value' => 'DE'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_INSURANCE', 'value' => '1'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '2'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_PACKAGE_TYPE', 'value' => '2'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_RECIPIENT_ONLY', 'value' => '1'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_RETURN_PACKAGE', 'value' => '1'],
            ['id_product' => 1, 'name' => 'MYPARCELNL_SIGNATURE_REQUIRED', 'value' => '1'],
        ],

        'result' => [
            ProductSettings::PACKAGE_TYPE          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ProductSettings::COUNTRY_OF_ORIGIN     => 'DE',
            ProductSettings::CUSTOMS_CODE          => '123',
            ProductSettings::EXPORT_AGE_CHECK      => TriStateService::ENABLED,
            ProductSettings::EXPORT_INSURANCE      => TriStateService::ENABLED,
            ProductSettings::EXPORT_LARGE_FORMAT   => TriStateService::ENABLED,
            ProductSettings::EXPORT_ONLY_RECIPIENT => TriStateService::ENABLED,
            ProductSettings::EXPORT_RETURN         => TriStateService::ENABLED,
            ProductSettings::EXPORT_SIGNATURE      => TriStateService::ENABLED,
        ],
    ],

    'changes country of origin "AF" to inherit' => [
        'rows'   => [['id_product' => 1, 'name' => 'MYPARCELNL_CUSTOMS_ORIGIN', 'value' => 'AF']],
        'result' => [],
    ],

    'large format: 1' => [
        'rows'   => [['id_product' => 1, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '1']],
        'result' => [ProductSettings::EXPORT_LARGE_FORMAT => TriStateService::DISABLED],
    ],

    'large format: 2' => [
        'rows'   => [['id_product' => 1, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '2']],
        'result' => [ProductSettings::EXPORT_LARGE_FORMAT => TriStateService::ENABLED],
    ],

    'large format: 3' => [
        'rows'   => [['id_product' => 1, 'name' => 'MYPARCELNL_PACKAGE_FORMAT', 'value' => '3']],
        'result' => [],
    ],
]);

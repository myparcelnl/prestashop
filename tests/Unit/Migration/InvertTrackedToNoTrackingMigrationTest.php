<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
use MyParcelNL\Pdk\App\Options\Definition\NoTrackingDefinition;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

const INVERT_MIGRATION_ID = '2026_08_04_144911_invert_tracked_to_no_tracking';

/** The keys the option used to be stored under, before NoTrackingDefinition replaced TrackedDefinition. */
const LEGACY_SETTING_KEY = 'exportTracked';

const LEGACY_SHIPMENT_OPTION_KEY = 'tracked';

/**
 * Loads the migration the same way the installer does: require the file and take the returned
 * anonymous-class instance.
 */
function loadInvertMigration(): TimestampedMigrationInterface
{
    return require __DIR__ . '/../../../src/Migration/' . INVERT_MIGRATION_ID . '.php';
}

function settingKey(): string
{
    return (new NoTrackingDefinition())->getCarrierSettingsKey();
}

function optionKey(): string
{
    return (new NoTrackingDefinition())->getShipmentOptionsKey();
}

function withShipmentOptions(array $options): array
{
    return ['deliveryOptions' => ['shipmentOptions' => $options]];
}

it('is a timestamped migration the installer can discover', function () {
    $migration = loadInvertMigration();

    $migration->setIdentity(INVERT_MIGRATION_ID);

    expect($migration)->toBeInstanceOf(TimestampedMigrationInterface::class)
        ->and($migration->getId())->toBe(INVERT_MIGRATION_ID);
});

/**
 * Carrier settings: one stored blob, converted inline.
 */
dataset('carrier tracking values', [
    'tracking on becomes opt-out off' => [TriStateService::ENABLED, TriStateService::DISABLED],
    'tracking off becomes opt-out on' => [TriStateService::DISABLED, TriStateService::ENABLED],
    'not set stays not set'           => [TriStateService::INHERIT, TriStateService::INHERIT],
]);

it('flips the option in carrier settings', function (int $stored, int $expected) {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey        = Pdk::get('createSettingsKey')('carrier');

    $settingsRepository->store($settingsKey, ['POSTNL' => [LEGACY_SETTING_KEY => $stored]]);

    loadInvertMigration()->up();

    $settings = $settingsRepository->get($settingsKey);

    expect($settings['POSTNL'][settingKey()])->toBe($expected)
        ->and($settings['POSTNL'])->not->toHaveKey(LEGACY_SETTING_KEY);
})->with('carrier tracking values');

it('leaves carriers that never stored the option alone', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey        = Pdk::get('createSettingsKey')('carrier');

    $settingsRepository->store($settingsKey, ['DHL_FOR_YOU' => ['exportSignature' => TriStateService::ENABLED]]);

    loadInvertMigration()->up();

    expect($settingsRepository->get($settingsKey)['DHL_FOR_YOU'])
        ->toBe(['exportSignature' => TriStateService::ENABLED]);
});

it('is safe to run over carrier settings twice', function () {
    // A second pass would otherwise flip tracking back on for a merchant who switched it off.
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey        = Pdk::get('createSettingsKey')('carrier');

    $settingsRepository->store($settingsKey, ['POSTNL' => [LEGACY_SETTING_KEY => TriStateService::DISABLED]]);

    loadInvertMigration()->up();
    loadInvertMigration()->up();

    expect($settingsRepository->get($settingsKey)['POSTNL'][settingKey()])->toBe(TriStateService::ENABLED);
});

/**
 * Product settings: one row per product, and the option sits under a "settings" key rather than at the
 * root of the row's JSON.
 */
it('flips the option in product settings', function () {
    (new FactoryCollection([
        factory(MyparcelnlProductSettings::class)
            ->withProductId(1)
            ->withData(json_encode(['settings' => [LEGACY_SETTING_KEY => TriStateService::ENABLED]])),
    ]))->store();

    loadInvertMigration()->up();

    $settings = Pdk::get(PsProductSettingsRepository::class)
        ->findOneBy(['productId' => 1])
        ->getData()['settings'];

    expect($settings[settingKey()])->toBe(TriStateService::DISABLED)
        ->and($settings)->not->toHaveKey(LEGACY_SETTING_KEY);
});

it('leaves product rows without the option alone', function () {
    (new FactoryCollection([
        factory(MyparcelnlProductSettings::class)
            ->withProductId(1)
            ->withData(json_encode(['settings' => ['exportSignature' => TriStateService::ENABLED]])),
    ]))->store();

    loadInvertMigration()->up();

    expect(Pdk::get(PsProductSettingsRepository::class)->findOneBy(['productId' => 1])->getData())
        ->toBe(['settings' => ['exportSignature' => TriStateService::ENABLED]]);
});

/**
 * Order data: the choice made for the order, which still drives a re-export.
 */
it('flips the option in order data', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode(withShipmentOptions([LEGACY_SHIPMENT_OPTION_KEY => TriStateService::ENABLED]))),
    ]))->store();

    loadInvertMigration()->up();

    $options = Pdk::get(PsOrderDataRepository::class)
        ->findOneBy(['orderId' => 1])
        ->getData()['deliveryOptions']['shipmentOptions'];

    expect($options[optionKey()])->toBe(TriStateService::DISABLED)
        ->and($options)->not->toHaveKey(LEGACY_SHIPMENT_OPTION_KEY);
});

it('leaves order rows without delivery options alone', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode(['notes' => 'test'])),
    ]))->store();

    loadInvertMigration()->up();

    expect(Pdk::get(PsOrderDataRepository::class)->findOneBy(['orderId' => 1])->getData())
        ->toBe(['notes' => 'test']);
});

/**
 * Stored shipments: how each shipment went out. One row per shipment on PrestaShop.
 */
it('flips the option on a stored shipment', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(1)
            ->withData(json_encode(withShipmentOptions([LEGACY_SHIPMENT_OPTION_KEY => TriStateService::DISABLED]))),
    ]))->store();

    loadInvertMigration()->up();

    $options = Pdk::get(PsOrderShipmentRepository::class)
        ->findOneBy(['shipmentId' => 1])
        ->getData()['deliveryOptions']['shipmentOptions'];

    expect($options[optionKey()])->toBe(TriStateService::ENABLED);
});

it('flips the option on every stored shipment', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(1)
            ->withData(json_encode(withShipmentOptions([LEGACY_SHIPMENT_OPTION_KEY => TriStateService::ENABLED]))),
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(2)
            ->withData(json_encode(withShipmentOptions([LEGACY_SHIPMENT_OPTION_KEY => TriStateService::DISABLED]))),
    ]))->store();

    loadInvertMigration()->up();

    $repository = Pdk::get(PsOrderShipmentRepository::class);

    expect($repository->findOneBy(['shipmentId' => 1])->getData()['deliveryOptions']['shipmentOptions'][optionKey()])
        ->toBe(TriStateService::DISABLED)
        ->and(
            $repository->findOneBy(['shipmentId' => 2])->getData()['deliveryOptions']['shipmentOptions'][optionKey()]
        )->toBe(TriStateService::ENABLED);
});

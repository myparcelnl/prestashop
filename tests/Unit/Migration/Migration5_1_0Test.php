<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('remaps legacy carrier setting keys to V2 format', function () {
    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepo */
    $settingsRepo = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey  = Pdk::get('createSettingsKey')('carrier');

    $settingsRepo->store($settingsKey, [
        'postnl'           => ['delivery_enabled' => '1', 'pickup_enabled' => '1'],
        'dhlforyou'        => ['delivery_enabled' => '1'],
        'dhlparcelconnect' => ['delivery_enabled' => '0'],
    ]);

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $result = $settingsRepo->get($settingsKey);

    expect($result)
        ->toHaveKeys(['POSTNL', 'DHL_FOR_YOU', 'DHL_PARCEL_CONNECT'])
        ->and($result)->not->toHaveKey('postnl')
        ->and($result)->not->toHaveKey('dhlforyou')
        ->and($result)->not->toHaveKey('dhlparcelconnect')
        ->and($result['POSTNL'])->toBe(['delivery_enabled' => '1', 'pickup_enabled' => '1'])
        ->and($result['DHL_FOR_YOU'])->toBe(['delivery_enabled' => '1'])
        ->and($result['DHL_PARCEL_CONNECT'])->toBe(['delivery_enabled' => '0']);
});

it('does not fail when carrier settings are empty', function () {
    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepo */
    $settingsRepo = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey  = Pdk::get('createSettingsKey')('carrier');

    expect($settingsRepo->get($settingsKey))->toBeEmpty();
});

it('preserves settings that already use V2 key format', function () {
    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepo */
    $settingsRepo = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settingsKey  = Pdk::get('createSettingsKey')('carrier');

    $settingsRepo->store($settingsKey, [
        'postnl' => ['delivery_enabled' => '1'],
        'BPOST'  => ['delivery_enabled' => '1'],
    ]);

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $result = $settingsRepo->get($settingsKey);

    expect($result)
        ->toHaveKeys(['POSTNL', 'BPOST'])
        ->and($result)->not->toHaveKey('postnl');
});

it('migrates carrier mapping table entries to V2 names', function () {
    (new FactoryCollection([
        factory(MyparcelnlCarrierMapping::class)
            ->withMyparcelCarrier('postnl')
            ->withCarrierId(21),
        factory(MyparcelnlCarrierMapping::class)
            ->withMyparcelCarrier('dhlforyou')
            ->withCarrierId(22),
        factory(MyparcelnlCarrierMapping::class)
            ->withMyparcelCarrier('bpost')
            ->withCarrierId(24),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo     = Pdk::get(PsCarrierMappingRepository::class);
    $mappings = $repo->all();

    $carriers = $mappings->map(function (MyparcelnlCarrierMapping $m) {
        return $m->getMyparcelCarrier();
    })->toArray();

    expect($carriers)->toBe(['POSTNL', 'DHL_FOR_YOU', 'BPOST']);
});

it('skips carrier mappings that are already V2 format', function () {
    (new FactoryCollection([
        factory(MyparcelnlCarrierMapping::class)
            ->withMyparcelCarrier('POSTNL')
            ->withCarrierId(21),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsCarrierMappingRepository::class);
    expect($repo->all()->first()->getMyparcelCarrier())->toBe('POSTNL');
});

dataset('cart delivery options carrier variants', [
    'plain legacy string'            => [['carrier' => 'postnl'], 'POSTNL'],
    'object with externalIdentifier' => [['carrier' => ['externalIdentifier' => 'dhlforyou']], 'DHL_FOR_YOU'],
    'object with carrier key'        => [['carrier' => ['carrier' => 'dhlparcelconnect']], 'DHL_PARCEL_CONNECT'],
    'already V2 format'              => [['carrier' => 'POSTNL'], 'POSTNL'],
]);

it('normalises the carrier field in cart delivery options', function (array $cartData, string $expectedCarrier) {
    (new FactoryCollection([
        factory(MyparcelnlCartDeliveryOptions::class)
            ->withCartId(1)
            ->withData(json_encode($cartData)),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsCartDeliveryOptionsRepository::class);
    $cart = $repo->findOneBy(['cartId' => 1]);

    expect($cart->getData()['carrier'])->toBe($expectedCarrier);
})->with('cart delivery options carrier variants');

it('skips cart delivery options without carrier field', function () {
    (new FactoryCollection([
        factory(MyparcelnlCartDeliveryOptions::class)
            ->withCartId(1)
            ->withData(json_encode(['deliveryType' => 'standard'])),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsCartDeliveryOptionsRepository::class);
    expect($repo->findOneBy(['cartId' => 1])->getData())->toBe(['deliveryType' => 'standard']);
});

dataset('order carrier variants', [
    'plain legacy string'               => [['deliveryOptions' => ['carrier' => 'postnl']], 'POSTNL'],
    'legacy string with contract suffix' => [['deliveryOptions' => ['carrier' => 'postnl:123']], 'POSTNL'],
    'object with externalIdentifier'    => [['deliveryOptions' => ['carrier' => ['externalIdentifier' => 'dhlforyou']]], 'DHL_FOR_YOU'],
    'object with carrier key'           => [['deliveryOptions' => ['carrier' => ['carrier' => 'dhlparcelconnect']]], 'DHL_PARCEL_CONNECT'],
    'already V2 format'                 => [['deliveryOptions' => ['carrier' => 'POSTNL']], 'POSTNL'],
]);

it('normalises the carrier field in order data', function (array $orderData, string $expectedCarrier) {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode($orderData)),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo  = Pdk::get(PsOrderDataRepository::class);
    $order = $repo->findOneBy(['orderId' => 1]);

    expect($order->getData()['deliveryOptions']['carrier'])->toBe($expectedCarrier);
})->with('order carrier variants');

it('skips order data rows without deliveryOptions', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode(['notes' => 'test'])),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsOrderDataRepository::class);
    expect($repo->findOneBy(['orderId' => 1])->getData())->toBe(['notes' => 'test']);
});

dataset('shipment carrier variants', [
    'plain legacy string'            => [['carrier' => 'postnl'], ['carrier' => 'POSTNL']],
    'legacy string with contract'    => [['carrier' => 'postnl:42'], ['carrier' => 'POSTNL', 'contractId' => '42']],
    'object with externalIdentifier' => [['carrier' => ['externalIdentifier' => 'dhlforyou']], ['carrier' => 'DHL_FOR_YOU']],
    'nested deliveryOptions carrier' => [['carrier' => 'postnl', 'deliveryOptions' => ['carrier' => 'postnl']], ['carrier' => 'POSTNL', 'deliveryOptions' => ['carrier' => 'POSTNL']]],
    'already V2 format'              => [['carrier' => 'POSTNL'], ['carrier' => 'POSTNL']],
]);

it('normalises the carrier field in shipment data', function (array $shipmentData, array $expected) {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(100)
            ->withOrderId(1)
            ->withData(json_encode($shipmentData)),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsOrderShipmentRepository::class);
    $data = $repo->findOneBy(['shipmentId' => 100])->getData();

    foreach ($expected as $key => $value) {
        expect($data[$key])->toBe($value);
    }
})->with('shipment carrier variants');

it('migrates multiple order data rows in a single run', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode(['deliveryOptions' => ['carrier' => 'postnl']])),
        factory(MyparcelnlOrderData::class)
            ->withOrderId(2)
            ->withData(json_encode(['deliveryOptions' => ['carrier' => 'dhlforyou']])),
        factory(MyparcelnlOrderData::class)
            ->withOrderId(3)
            ->withData(json_encode(['deliveryOptions' => ['carrier' => 'POSTNL']])),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsOrderDataRepository::class);

    expect($repo->findOneBy(['orderId' => 1])->getData()['deliveryOptions']['carrier'])->toBe('POSTNL')
        ->and($repo->findOneBy(['orderId' => 2])->getData()['deliveryOptions']['carrier'])->toBe('DHL_FOR_YOU')
        ->and($repo->findOneBy(['orderId' => 3])->getData()['deliveryOptions']['carrier'])->toBe('POSTNL');
});

it('migrates multiple shipment data rows in a single run', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(100)
            ->withOrderId(1)
            ->withData(json_encode(['carrier' => 'postnl'])),
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(101)
            ->withOrderId(2)
            ->withData(json_encode(['carrier' => 'dhlforyou'])),
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(102)
            ->withOrderId(3)
            ->withData(json_encode(['carrier' => 'dhlparcelconnect'])),
    ]))->store();

    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $repo = Pdk::get(PsOrderShipmentRepository::class);

    expect($repo->findOneBy(['shipmentId' => 100])->getData()['carrier'])->toBe('POSTNL')
        ->and($repo->findOneBy(['shipmentId' => 101])->getData()['carrier'])->toBe('DHL_FOR_YOU')
        ->and($repo->findOneBy(['shipmentId' => 102])->getData()['carrier'])->toBe('DHL_PARCEL_CONNECT');
});

it('does not fail when no account is available during migration', function () {
    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);

    // migrateAccountData is wrapped in try/catch; no account configured means it silently returns
    expect(fn() => $migration->up())->not->toThrow(\Throwable::class);

    /** @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface $accountRepo */
    $accountRepo = Pdk::get(PdkAccountRepositoryInterface::class);
    expect($accountRepo->getAccount())->toBeNull();
});

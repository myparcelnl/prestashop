# Carrier V2 Migration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Migrate all stored carrier data from legacy lowercase names (e.g. `"postnl"`, `"dhlforyou"`) to the new PDK V2 SCREAMING_SNAKE_CASE identifiers (e.g. `"POSTNL"`, `"DHL_FOR_YOU"`), including handling of legacy object formats and contract ID extraction.

**Architecture:** A single upgrade migration class `Migration5_1_0` that runs synchronously during module upgrade. It migrates five data stores: account data (re-fetch from API), carrier settings keys, carrier mapping table entries, order data JSON, and shipment data JSON. The migration reuses `Carrier::CARRIER_NAME_TO_LEGACY_MAP` from the PDK for all name mapping and ports `parseLegacyCarrier()` from the WooCommerce implementation to handle all legacy carrier formats.

**Tech Stack:** PHP 7.4+, Pest (testing), Doctrine ORM, PDK `Carrier::CARRIER_NAME_TO_LEGACY_MAP`

---

## File Structure

| File | Responsibility |
|------|---------------|
| `src/Migration/Migration5_1_0.php` | Upgrade migration — orchestrates all five sub-migrations |
| `src/Pdk/Installer/Service/PsMigrationService.php` | Register new migration in `all()` |
| `tests/Unit/Migration/Migration5_1_0Test.php` | Tests for the migration |

---

## Task 1: Create the migration class with carrier settings migration

The first piece of the migration: remap carrier settings keys in the PrestaShop `configuration` table from legacy names to V2 names.

**Files:**
- Create: `src/Migration/Migration5_1_0.php`
- Test: `tests/Unit/Migration/Migration5_1_0Test.php`

- [ ] **Step 1: Write the migration class with carrier settings migration**

Create `src/Migration/Migration5_1_0.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Migration\Pdk\AbstractPsPdkMigration;

final class Migration5_1_0 extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(PdkSettingsRepositoryInterface $settingsRepository)
    {
        parent::__construct();
        $this->settingsRepository = $settingsRepository;
    }

    public function getVersion(): string
    {
        return '5.1.0';
    }

    public function up(): void
    {
        $this->migrateCarrierSettings();
    }

    /**
     * Remap carrier setting keys from legacy lowercase names to V2 SCREAMING_SNAKE_CASE.
     */
    private function migrateCarrierSettings(): void
    {
        $settingsKey     = Pdk::get('createSettingsKey')('carrier');
        $currentSettings = $this->settingsRepository->get($settingsKey);

        if (empty($currentSettings) || ! is_array($currentSettings)) {
            return;
        }

        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        $migratedSettings = [];
        foreach ($currentSettings as $key => $carrierData) {
            $newKey                    = $legacyToNewMap[$key] ?? $key;
            $migratedSettings[$newKey] = $carrierData;
        }

        $this->settingsRepository->store($settingsKey, $migratedSettings);
    }
}
```

- [ ] **Step 2: Write the tests for carrier settings migration**

Create `tests/Unit/Migration/Migration5_1_0Test.php`:

```php
<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
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
```

- [ ] **Step 3: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Migration/Migration5_1_0Test.php`
Expected: PASS (3 tests)

- [ ] **Step 4: Commit**

```bash
git add src/Migration/Migration5_1_0.php tests/Unit/Migration/Migration5_1_0Test.php
git commit -m "feat: add carrier V2 migration — carrier settings"
```

---

## Task 2: Add carrier mapping table migration

Update the `myparcelnl_carrier_mapping.myparcel_carrier` column values from legacy to V2 names.

**Files:**
- Modify: `src/Migration/Migration5_1_0.php`
- Modify: `tests/Unit/Migration/Migration5_1_0Test.php`

- [ ] **Step 1: Add carrier mapping migration to Migration5_1_0**

Add the following to `src/Migration/Migration5_1_0.php`:

Add `use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;` and `use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;` and `use MyParcelNL\PrestaShop\Facade\EntityManager;` to the imports.

Add constructor parameter and property:

```php
/**
 * @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository
 */
private $carrierMappingRepository;

public function __construct(
    PdkSettingsRepositoryInterface $settingsRepository,
    PsCarrierMappingRepository     $carrierMappingRepository
) {
    parent::__construct();
    $this->settingsRepository       = $settingsRepository;
    $this->carrierMappingRepository = $carrierMappingRepository;
}
```

Add call in `up()`:

```php
public function up(): void
{
    $this->migrateCarrierSettings();
    $this->migrateCarrierMappings();
}
```

Add the method:

```php
/**
 * Update myparcel_carrier values in the carrier mapping table from legacy to V2 names.
 */
private function migrateCarrierMappings(): void
{
    $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
    $mappings       = $this->carrierMappingRepository->all();

    /** @var MyparcelnlCarrierMapping $mapping */
    foreach ($mappings as $mapping) {
        $currentName = $mapping->getMyparcelCarrier();
        $newName     = $legacyToNewMap[$currentName] ?? null;

        if (! $newName) {
            continue;
        }

        $mapping->setMyparcelCarrier($newName);
    }

    EntityManager::flush();
}
```

- [ ] **Step 2: Write the tests for carrier mapping migration**

Append to `tests/Unit/Migration/Migration5_1_0Test.php`:

```php
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use function MyParcelNL\Pdk\Tests\factory;

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

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var PsCarrierMappingRepository $repo */
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

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var PsCarrierMappingRepository $repo */
    $repo     = Pdk::get(PsCarrierMappingRepository::class);
    $mappings = $repo->all();

    expect($mappings->first()->getMyparcelCarrier())->toBe('POSTNL');
});
```

- [ ] **Step 3: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Migration/Migration5_1_0Test.php`
Expected: PASS (all tests)

- [ ] **Step 4: Commit**

```bash
git add src/Migration/Migration5_1_0.php tests/Unit/Migration/Migration5_1_0Test.php
git commit -m "feat: add carrier mapping table migration to V2 names"
```

---

## Task 3: Add order data carrier migration

Migrate the `carrier` field inside the `deliveryOptions` JSON in `myparcelnl_order_data.data`. Must handle all legacy formats: plain string, string with `:contractId` suffix, object with `externalIdentifier`, and object with `carrier` key.

**Files:**
- Modify: `src/Migration/Migration5_1_0.php`
- Modify: `tests/Unit/Migration/Migration5_1_0Test.php`

- [ ] **Step 1: Add parseLegacyCarrier and order data migration**

Add to `src/Migration/Migration5_1_0.php`:

Add `use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;` to imports.

Add constructor parameter:

```php
/**
 * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
 */
private $orderDataRepository;

public function __construct(
    PdkSettingsRepositoryInterface $settingsRepository,
    PsCarrierMappingRepository     $carrierMappingRepository,
    PsOrderDataRepository          $orderDataRepository
) {
    parent::__construct();
    $this->settingsRepository       = $settingsRepository;
    $this->carrierMappingRepository = $carrierMappingRepository;
    $this->orderDataRepository      = $orderDataRepository;
}
```

Add call in `up()`:

```php
public function up(): void
{
    $this->migrateCarrierSettings();
    $this->migrateCarrierMappings();
    $this->migrateOrderData();
}
```

Add the methods:

```php
/**
 * Extracts the carrier name from legacy formats and strips the contract ID suffix.
 *
 * Handles:
 * - plain string: "postnl"
 * - string with contract ID: "postnl:123"
 * - object with externalIdentifier: {"externalIdentifier": "postnl"}
 * - object with carrier key: {"carrier": "postnl"}
 *
 * @param  mixed $carrier
 *
 * @return null|string[] [carrierName, contractId] or null if not parseable
 */
private function parseLegacyCarrier($carrier): ?array
{
    if (is_array($carrier)) {
        $raw = $carrier['externalIdentifier'] ?? ($carrier['carrier'] ?? null);
    } elseif (is_string($carrier)) {
        $raw = $carrier;
    } else {
        return null;
    }

    if (! is_string($raw)) {
        return null;
    }

    $parts      = explode(':', $raw, 2);
    $name       = $parts[0];
    $contractId = $parts[1] ?? null;

    return [$name, $contractId];
}

/**
 * Migrate carrier names in order data JSON.
 */
private function migrateOrderData(): void
{
    $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
    $allOrderData   = $this->orderDataRepository->all();

    foreach ($allOrderData as $orderData) {
        $data = $orderData->getData();

        $parsed = $this->parseLegacyCarrier($data['deliveryOptions']['carrier'] ?? null);

        if (! $parsed) {
            continue;
        }

        [$legacyName] = $parsed;
        $newName = $legacyToNewMap[$legacyName] ?? $legacyName;

        if ($newName === ($data['deliveryOptions']['carrier'] ?? null)) {
            continue;
        }

        $data['deliveryOptions']['carrier'] = $newName;
        $orderData->setData(json_encode($data));
    }

    EntityManager::flush();
}
```

- [ ] **Step 2: Write the tests for order data migration**

Append to `tests/Unit/Migration/Migration5_1_0Test.php`:

```php
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;

dataset('order carrier variants', [
    'plain legacy string' => [
        ['deliveryOptions' => ['carrier' => 'postnl']],
        'POSTNL',
    ],
    'legacy string with contract suffix' => [
        ['deliveryOptions' => ['carrier' => 'postnl:123']],
        'POSTNL',
    ],
    'object with externalIdentifier' => [
        ['deliveryOptions' => ['carrier' => ['externalIdentifier' => 'dhlforyou']]],
        'DHL_FOR_YOU',
    ],
    'object with carrier key' => [
        ['deliveryOptions' => ['carrier' => ['carrier' => 'dhlparcelconnect']]],
        'DHL_PARCEL_CONNECT',
    ],
    'already V2 format' => [
        ['deliveryOptions' => ['carrier' => 'POSTNL']],
        'POSTNL',
    ],
]);

it('normalises the carrier field in order data', function (array $orderData, string $expectedCarrier) {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode($orderData)),
    ]))->store();

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var PsOrderDataRepository $repo */
    $repo  = Pdk::get(PsOrderDataRepository::class);
    $order = $repo->findOneBy(['orderId' => 1]);

    $data = $order->getData();

    expect($data['deliveryOptions']['carrier'])->toBe($expectedCarrier);
})->with('order carrier variants');

it('skips order data rows without deliveryOptions', function () {
    (new FactoryCollection([
        factory(MyparcelnlOrderData::class)
            ->withOrderId(1)
            ->withData(json_encode(['notes' => 'test'])),
    ]))->store();

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var PsOrderDataRepository $repo */
    $repo  = Pdk::get(PsOrderDataRepository::class);
    $order = $repo->findOneBy(['orderId' => 1]);

    expect($order->getData())->toBe(['notes' => 'test']);
});
```

- [ ] **Step 3: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Migration/Migration5_1_0Test.php`
Expected: PASS (all tests)

- [ ] **Step 4: Commit**

```bash
git add src/Migration/Migration5_1_0.php tests/Unit/Migration/Migration5_1_0Test.php
git commit -m "feat: add order data carrier migration with legacy format parsing"
```

---

## Task 4: Add shipment data carrier migration

Migrate the `carrier` field in `myparcelnl_order_shipment.data` JSON. Also handles `deliveryOptions.carrier` within shipments and extracts `contractId` from the `:N` suffix.

**Files:**
- Modify: `src/Migration/Migration5_1_0.php`
- Modify: `tests/Unit/Migration/Migration5_1_0Test.php`

- [ ] **Step 1: Add shipment data migration**

Add to `src/Migration/Migration5_1_0.php`:

Add `use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;` to imports.

Add constructor parameter:

```php
/**
 * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
 */
private $orderShipmentRepository;

public function __construct(
    PdkSettingsRepositoryInterface $settingsRepository,
    PsCarrierMappingRepository     $carrierMappingRepository,
    PsOrderDataRepository          $orderDataRepository,
    PsOrderShipmentRepository      $orderShipmentRepository
) {
    parent::__construct();
    $this->settingsRepository        = $settingsRepository;
    $this->carrierMappingRepository  = $carrierMappingRepository;
    $this->orderDataRepository       = $orderDataRepository;
    $this->orderShipmentRepository   = $orderShipmentRepository;
}
```

Add call in `up()`:

```php
public function up(): void
{
    $this->migrateCarrierSettings();
    $this->migrateCarrierMappings();
    $this->migrateOrderData();
    $this->migrateShipmentData();
}
```

Add the method:

```php
/**
 * Migrate carrier names in shipment data JSON. Extracts contractId from `:N` suffix.
 */
private function migrateShipmentData(): void
{
    $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
    $allShipments   = $this->orderShipmentRepository->all();

    foreach ($allShipments as $shipment) {
        $data    = $shipment->getData();
        $changed = false;

        // Migrate top-level carrier
        $parsed = $this->parseLegacyCarrier($data['carrier'] ?? null);

        if ($parsed) {
            [$legacyName, $contractId] = $parsed;
            $newName = $legacyToNewMap[$legacyName] ?? $legacyName;

            if ($newName !== ($data['carrier'] ?? null) || ! is_string($data['carrier'] ?? null)) {
                $data['carrier'] = $newName;
                $changed         = true;
            }

            if ($contractId && ! isset($data['contractId'])) {
                $data['contractId'] = $contractId;
                $changed            = true;
            }
        }

        // Migrate nested deliveryOptions.carrier
        if (isset($data['deliveryOptions']['carrier'])) {
            $parsedDo = $this->parseLegacyCarrier($data['deliveryOptions']['carrier']);

            if ($parsedDo) {
                [$doLegacyName] = $parsedDo;
                $doNewName = $legacyToNewMap[$doLegacyName] ?? $doLegacyName;

                if ($doNewName !== $data['deliveryOptions']['carrier']) {
                    $data['deliveryOptions']['carrier'] = $doNewName;
                    $changed                            = true;
                }
            }
        }

        if ($changed) {
            $shipment->setData(json_encode($data));
        }
    }

    EntityManager::flush();
}
```

- [ ] **Step 2: Write the tests for shipment data migration**

Append to `tests/Unit/Migration/Migration5_1_0Test.php`:

```php
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;

dataset('shipment carrier variants', [
    'plain legacy string' => [
        ['carrier' => 'postnl'],
        ['carrier' => 'POSTNL'],
    ],
    'legacy string with contract suffix' => [
        ['carrier' => 'postnl:42'],
        ['carrier' => 'POSTNL', 'contractId' => '42'],
    ],
    'object with externalIdentifier' => [
        ['carrier' => ['externalIdentifier' => 'dhlforyou']],
        ['carrier' => 'DHL_FOR_YOU'],
    ],
    'with nested deliveryOptions carrier' => [
        ['carrier' => 'postnl', 'deliveryOptions' => ['carrier' => 'postnl']],
        ['carrier' => 'POSTNL', 'deliveryOptions' => ['carrier' => 'POSTNL']],
    ],
    'already V2 format' => [
        ['carrier' => 'POSTNL'],
        ['carrier' => 'POSTNL'],
    ],
]);

it('normalises the carrier field in shipment data', function (array $shipmentData, array $expected) {
    (new FactoryCollection([
        factory(MyparcelnlOrderShipment::class)
            ->withShipmentId(100)
            ->withOrderId(1)
            ->withData(json_encode($shipmentData)),
    ]))->store();

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    /** @var PsOrderShipmentRepository $repo */
    $repo     = Pdk::get(PsOrderShipmentRepository::class);
    $shipment = $repo->findOneBy(['shipmentId' => 100]);

    $data = $shipment->getData();

    foreach ($expected as $key => $value) {
        expect($data[$key])->toBe($value);
    }
})->with('shipment carrier variants');
```

- [ ] **Step 3: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Migration/Migration5_1_0Test.php`
Expected: PASS (all tests)

- [ ] **Step 4: Commit**

```bash
git add src/Migration/Migration5_1_0.php tests/Unit/Migration/Migration5_1_0Test.php
git commit -m "feat: add shipment data carrier migration with contractId extraction"
```

---

## Task 5: Add account data migration and register the migration

Re-fetch carrier definitions from the API (same as WooCommerce) and register the migration in `PsMigrationService`.

**Files:**
- Modify: `src/Migration/Migration5_1_0.php`
- Modify: `src/Pdk/Installer/Service/PsMigrationService.php`
- Modify: `tests/Unit/Migration/Migration5_1_0Test.php`

- [ ] **Step 1: Add account data migration and all remaining constructor dependencies**

Update `src/Migration/Migration5_1_0.php`:

Add imports:

```php
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Logger;
use Throwable;
```

Update constructor:

```php
/**
 * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
 */
private $accountRepository;

/**
 * @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository
 */
private $carrierCapabilitiesRepository;

public function __construct(
    PdkSettingsRepositoryInterface    $settingsRepository,
    PsCarrierMappingRepository        $carrierMappingRepository,
    PsOrderDataRepository             $orderDataRepository,
    PsOrderShipmentRepository         $orderShipmentRepository,
    PdkAccountRepositoryInterface     $accountRepository,
    CarrierCapabilitiesRepository     $carrierCapabilitiesRepository
) {
    parent::__construct();
    $this->settingsRepository            = $settingsRepository;
    $this->carrierMappingRepository      = $carrierMappingRepository;
    $this->orderDataRepository           = $orderDataRepository;
    $this->orderShipmentRepository       = $orderShipmentRepository;
    $this->accountRepository             = $accountRepository;
    $this->carrierCapabilitiesRepository = $carrierCapabilitiesRepository;
}
```

Update `up()`:

```php
public function up(): void
{
    $this->migrateAccountData();
    $this->migrateCarrierSettings();
    $this->migrateCarrierMappings();
    $this->migrateOrderData();
    $this->migrateShipmentData();
}
```

Add the method:

```php
/**
 * Re-fetch carrier definitions from the API to populate the new carrier model.
 */
private function migrateAccountData(): void
{
    try {
        $account = $this->accountRepository->getAccount(true);
        $shop    = $account->shops->first();

        $shop->carriers = $this->carrierCapabilitiesRepository->getContractDefinitions();

        $this->accountRepository->store($account);
    } catch (Throwable $e) {
        Logger::warning('Could not refresh account carrier data during migration', ['exception' => $e]);
    }
}
```

- [ ] **Step 2: Write the tests for account data migration**

Append to `tests/Unit/Migration/Migration5_1_0Test.php`:

```php
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockAccountRepository;

it('refreshes account carrier definitions from the API', function () {
    /** @var MockAccountRepository $accountRepo */
    $accountRepo = Pdk::get(PdkAccountRepositoryInterface::class);

    /** @var Migration5_1_0 $migration */
    $migration = Pdk::get(Migration5_1_0::class);
    $migration->up();

    $account = $accountRepo->getAccount();
    $shop    = $account->shops->first();

    expect($shop->carriers)->not->toBeEmpty();
});
```

- [ ] **Step 3: Register the migration in PsMigrationService**

Update `src/Pdk/Installer/Service/PsMigrationService.php`:

Add import:

```php
use MyParcelNL\PrestaShop\Migration\Migration5_1_0;
```

Update the `all()` method:

```php
public function all(): array
{
    return [
        Migration4_0_0::class,
        Migration4_2_3::class,
        Migration5_1_0::class,
    ];
}
```

- [ ] **Step 4: Run the full test suite**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add src/Migration/Migration5_1_0.php src/Pdk/Installer/Service/PsMigrationService.php tests/Unit/Migration/Migration5_1_0Test.php
git commit -m "feat: add account data migration and register Migration5_1_0"
```

---

## Task 6: Update config/pdk.php carrier references

The `config/pdk.php` file uses `Carrier::CARRIER_*_LEGACY_NAME` constants as array keys in `countriesPerPlatformAndCarrier`. These must be updated to V2 names so the config matches the migrated data.

**Files:**
- Modify: `config/pdk.php`

- [ ] **Step 1: Replace legacy carrier name constants with V2 equivalents**

In `config/pdk.php`, replace all `Carrier::CARRIER_*_LEGACY_NAME` references in the `countriesPerPlatformAndCarrier` arrays with `RefCapabilitiesSharedCarrierV2::*` constants (or plain `'POSTNL'` strings).

Add import at the top of the config file:

```php
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
```

Replace all occurrences:

| Before | After |
|--------|-------|
| `Carrier::CARRIER_POSTNL_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::POSTNL` |
| `Carrier::CARRIER_DHL_FOR_YOU_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU` |
| `Carrier::CARRIER_DHL_PARCEL_CONNECT_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT` |
| `Carrier::CARRIER_DHL_EUROPLUS_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS` |
| `Carrier::CARRIER_UPS_EXPRESS_SAVER_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::UPS_EXPRESS_SAVER` |
| `Carrier::CARRIER_UPS_STANDARD_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::UPS_STANDARD` |
| `Carrier::CARRIER_DPD_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::DPD` |
| `Carrier::CARRIER_BPOST_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::BPOST` |
| `Carrier::CARRIER_BRT_LEGACY_NAME` | `RefCapabilitiesSharedCarrierV2::BRT` |

- [ ] **Step 2: Run the full test suite**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest`
Expected: All tests pass.

- [ ] **Step 3: Commit**

```bash
git add config/pdk.php
git commit -m "chore: update carrier references in pdk.php to V2 names"
```

---

## Task 7: Fix source code broken by removed PDK APIs

The new PDK removed `FrontendData::carrierCollectionToLegacyFormat()` and `PropositionService::mapNewToLegacyCarrierName()`. Source files calling these must be updated.

**Files:**
- Modify: `src/Service/PsCarrierService.php:178-186`
- Modify: `src/Service/PsCountryService.php:36-42`
- Modify: `src/Migration/Pdk/PdkCarrierMigration.php` (uses `mapNewToLegacyCarrierName`)
- Modify: `src/Migration/Pdk/PdkDeliveryOptionsMigration.php` (uses `mapNewToLegacyCarrierName`)

- [ ] **Step 1: Fix PsCarrierService::updateCarriers()**

In `src/Service/PsCarrierService.php`, the `updateCarriers()` method calls `FrontendData::carrierCollectionToLegacyFormat()` which no longer exists. Since carriers now use V2 names natively, remove the legacy conversion:

```php
// Before (line 178-186):
public function updateCarriers(): void
{
    $carriers = AccountSettings::getCarriers();

    // Map to legacy carrier for BC compatibility
    $carriers = FrontendData::carrierCollectionToLegacyFormat($carriers);

    $createdCarriers = $this->createOrUpdateCarriers($carriers);
    $this->deleteUnusedCarriers($createdCarriers);
}

// After:
public function updateCarriers(): void
{
    $carriers = AccountSettings::getCarriers();

    $createdCarriers = $this->createOrUpdateCarriers($carriers);
    $this->deleteUnusedCarriers($createdCarriers);
}
```

Remove the `use MyParcelNL\Pdk\Facade\FrontendData;` import.

- [ ] **Step 2: Fix PsCountryService::getCountriesForCarrier()**

In `src/Service/PsCountryService.php`, the method uses `mapNewToLegacyCarrierName()` to look up carriers in the `countriesPerPlatformAndCarrier` config. Since Task 6 updates the config keys to V2 names, the legacy mapping is no longer needed:

```php
// Before (line 34-46):
public function getCountriesForCarrier(string $carrierName): array
{
    $propositionService = Pdk::get(PropositionService::class);

    // Resolve carrier identifier
    [$resolvedCarrierName] = explode(':', $carrierName);

    // Use legacy carrier name for backwards compatibility
    $resolvedCarrierName = $propositionService->mapNewToLegacyCarrierName($resolvedCarrierName);

    $propositionName     = $propositionService->getPropositionConfig()->proposition->key;
    $allCarrierCountries = Pdk::get('countriesPerPlatformAndCarrier')[$propositionName] ?? [];
    $countriesForCarrier = $allCarrierCountries[$resolvedCarrierName] ?? [];

// After:
public function getCountriesForCarrier(string $carrierName): array
{
    $propositionService = Pdk::get(PropositionService::class);

    // Strip contract ID suffix if present
    [$resolvedCarrierName] = explode(':', $carrierName);

    $propositionName     = $propositionService->getPropositionConfig()->proposition->key;
    $allCarrierCountries = Pdk::get('countriesPerPlatformAndCarrier')[$propositionName] ?? [];
    $countriesForCarrier = $allCarrierCountries[$resolvedCarrierName] ?? [];
```

Remove the `use MyParcelNL\Pdk\Proposition\Service\PropositionService;` import if no longer used (check other methods first — the `$propositionService` is still used for `getPropositionConfig()`).

- [ ] **Step 3: Fix PdkCarrierMigration**

In `src/Migration/Pdk/PdkCarrierMigration.php`, replace `PropositionService::mapNewToLegacyCarrierName()` with `FrontendData::getLegacyCarrierIdentifier()` or use `Carrier::CARRIER_NAME_TO_LEGACY_MAP` directly.

In `getCarriersToMigrate()` (line 112-118), replace:

```php
// Before:
$legacyNames = $carriers->map(function ($carrier) use ($propositionService) {
    return $propositionService->mapNewToLegacyCarrierName($carrier->name);
});

// After:
$legacyNames = $carriers->map(function ($carrier) {
    return Carrier::CARRIER_NAME_TO_LEGACY_MAP[$carrier->name] ?? strtolower($carrier->name);
});
```

Remove `$propositionService` and related imports if no longer used.

- [ ] **Step 4: Fix PdkDeliveryOptionsMigration**

In `src/Migration/Pdk/PdkDeliveryOptionsMigration.php`, replace `PropositionService::mapNewToLegacyCarrierName()` calls (lines 82-94) with direct use of `Carrier::CARRIER_NAME_TO_LEGACY_MAP`:

```php
// Before:
$propositionService = Pdk::get(PropositionService::class);
yield new MigratableValue(
    'carrier',
    DeliveryOptions::CARRIER,
    new TransformValue(function ($value) use ($propositionService) {
        $carriers     = new Collection($propositionService->getCarriers());
        $carrierNames = $carriers->pluck('name')->map(
            fn ($name) => $propositionService->mapNewToLegacyCarrierName($name)
        );

        return in_array($value, $carrierNames->toArray(), true)
            ? $value
            : $propositionService->mapNewToLegacyCarrierName($propositionService->getDefaultCarrier()->name);
    })
);

// After:
$propositionService = Pdk::get(PropositionService::class);
yield new MigratableValue(
    'carrier',
    DeliveryOptions::CARRIER,
    new TransformValue(function ($value) use ($propositionService) {
        $legacyNames = array_values(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        return in_array($value, $legacyNames, true)
            ? $value
            : Carrier::CARRIER_NAME_TO_LEGACY_MAP[$propositionService->getDefaultCarrier()->carrier] ?? $value;
    })
);
```

Note: `getCarriers()` was removed but `getDefaultCarrier()` still exists. The default carrier now returns a `Carrier` model with a `$carrier` property (not `$name`) — use `->carrier` instead of `->name`.

- [ ] **Step 5: Run the full test suite**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest`
Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add src/Service/PsCarrierService.php src/Service/PsCountryService.php src/Migration/Pdk/PdkCarrierMigration.php src/Migration/Pdk/PdkDeliveryOptionsMigration.php
git commit -m "fix: update source code for removed PDK carrier APIs"
```

---

## Task 8: Update test factories and test data

Tests that use `Carrier::CARRIER_*_LEGACY_NAME` for factory data or assertions need updating where the stored format is expected to be V2.

**Files:**
- Modify: `tests/factories/MyParcelNL/PrestaShop/Entity/MyparcelnlCarrierMappingFactory.php`
- Modify: `tests/functions.php`
- Modify: `tests/Unit/Hooks/HasPsShippingCostHooksTest.php`
- Modify: test files in `tests/Unit/Migration/Pdk/` (only if PDK migration tests break due to changed carrier format expectations)

- [ ] **Step 1: Update MyparcelnlCarrierMappingFactory**

In `tests/factories/MyParcelNL/PrestaShop/Entity/MyparcelnlCarrierMappingFactory.php`, update factory methods to use V2 carrier names since the mapping table now stores V2 names:

```php
// Before:
return $this->fromCarrier(Carrier::CARRIER_BPOST_LEGACY_NAME, $contractId);
return $this->fromCarrier(Carrier::CARRIER_POSTNL_LEGACY_NAME, $contractId);
// etc.

// After:
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::BPOST, $contractId);
return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::POSTNL, $contractId);
// etc.
```

- [ ] **Step 2: Update tests/functions.php**

Update default carrier mapping data to use V2 names.

- [ ] **Step 3: Update HasPsShippingCostHooksTest.php**

Update test data and assertions to use V2 carrier names where the test creates `MyparcelnlCarrierMapping` or `DeliveryOptions` with carrier values.

- [ ] **Step 4: Run the full test suite**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest`
Expected: All tests pass.

- [ ] **Step 5: Commit**

```bash
git add tests/
git commit -m "test: update test factories and data for V2 carrier names"
```

---

## Task 9: Fix removed Carrier properties in source code

The PDK removed `Carrier->externalIdentifier`, `Carrier->enabled`, and `Carrier->name`. Multiple source files use these. This is the highest-impact root cause (14 failing tests).

**Failing tests:** PsCarrierServiceTest (10), PdkSettingsRepositoryTest (3), HasPsCarrierUpdateHooksTest (1)

**Files:**
- Modify: `src/Service/PsCarrierService.php`
- Modify: `src/Carrier/Service/CarrierBuilder.php`
- Modify: `src/Pdk/Settings/Repository/PsPdkSettingsRepository.php`

- [ ] **Step 1: Fix PsCarrierService — replace `externalIdentifier` and `enabled`**

`carrierIsActive()` uses `$carrier->enabled` (removed) and `Settings::get($carrier->externalIdentifier, ...)` which becomes `null`. Replace `$carrier->externalIdentifier` with `$carrier->carrier` throughout the file.

```php
// Before (line 56-58):
public function carrierIsActive(Carrier $carrier): bool
{
    if (! $carrier->enabled) {
        return false;
    }

// After: remove the enabled check entirely — carrier availability is now
// determined by whether the carrier exists in the account's contract definitions.
// The settings check below already handles whether the merchant enabled it.
public function carrierIsActive(Carrier $carrier): bool
{
```

```php
// Before (line 68):
$settings = Settings::get($carrier->externalIdentifier, CarrierSettings::ID);

// After:
$settings = Settings::get($carrier->carrier, CarrierSettings::ID);
```

Also replace `$carrier->externalIdentifier` on lines 89, 115, 139 with `$carrier->carrier`.

- [ ] **Step 2: Fix CarrierBuilder — replace removed properties and fix `delay` indirect modification**

In `src/Carrier/Service/CarrierBuilder.php`:

Replace `$this->carrier->externalIdentifier` with `$this->carrier->carrier` everywhere.
Replace `$this->carrier->name` or `$this->carrier->human` with appropriate alternatives — read the file to determine what's used and how.

Fix the `delay` indirect modification bug (line 185-188). PrestaShop's `Carrier->delay` is an overloaded property:

```php
// Before:
foreach (PsLanguage::getLanguages() as $lang) {
    $existingString = $psCarrier->delay[$lang['id_lang']] ?? null;
    $newString      = Language::translate('carrier_delivery_time', $lang['iso_code']);
    $psCarrier->delay[$lang['id_lang']] = $existingString ?? $newString;
}

// After:
$delays = $psCarrier->delay ?? [];
foreach (PsLanguage::getLanguages() as $lang) {
    $existingString = $delays[$lang['id_lang']] ?? null;
    $newString      = Language::translate('carrier_delivery_time', $lang['iso_code']);
    $delays[$lang['id_lang']] = $existingString ?? $newString;
}
$psCarrier->delay = $delays;
```

- [ ] **Step 3: Fix PsPdkSettingsRepository — replace `externalIdentifier`**

In `src/Pdk/Settings/Repository/PsPdkSettingsRepository.php` line 111:

```php
// Before:
$carrier = new Carrier(['externalIdentifier' => $carrierIdentifier]);

// After:
$carrier = new Carrier(['carrier' => $carrierIdentifier]);
```

- [ ] **Step 4: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Service/PsCarrierServiceTest.php tests/Unit/Pdk/Settings/Repository/PdkSettingsRepositoryTest.php tests/Unit/Hooks/HasPsCarrierUpdateHooksTest.php`
Expected: All pass.

- [ ] **Step 5: Commit**

```bash
git add src/Service/PsCarrierService.php src/Carrier/Service/CarrierBuilder.php src/Pdk/Settings/Repository/PsPdkSettingsRepository.php
git commit -m "fix: replace removed Carrier properties with V2 equivalents"
```

---

## Task 10: Fix removed `FrontendData::convertCarrierToLegacyFormat()` in shipping hooks

**Failing tests:** HasPsShippingCostHooksTest (4)

**Files:**
- Modify: `src/Hooks/HasPsShippingCostHooks.php`

- [ ] **Step 1: Remove `convertCarrierToLegacyFormat()` call**

Read `src/Hooks/HasPsShippingCostHooks.php`. Find the call to `FrontendData::convertCarrierToLegacyFormat()` (around line 52) and remove it — the Carrier model already has the correct V2 format.

Also check for any comparisons against `$carrier['externalIdentifier']` or similar legacy structure and update to work with the new carrier format (plain string `->carrier`).

- [ ] **Step 2: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Hooks/HasPsShippingCostHooksTest.php`
Expected: All pass.

- [ ] **Step 3: Commit**

```bash
git add src/Hooks/HasPsShippingCostHooks.php
git commit -m "fix: remove FrontendData::convertCarrierToLegacyFormat() from shipping hooks"
```

---

## Task 11: Fix `DeliveryOptions` model changes in migration tests

The PDK `DeliveryOptions` constructor now normalises carrier to V2 format automatically. The carrier is stored as a plain string (`"POSTNL"`) instead of an object (`{"externalIdentifier": "postnl"}`). Test assertions and migration output need updating.

**Failing tests:** PdkDeliveryOptionsMigrationTest (9), PdkOrderShipmentsMigrationTest (2)

**Files:**
- Modify: `tests/Unit/Migration/Pdk/PdkDeliveryOptionsMigrationTest.php`
- Modify: `tests/Unit/Migration/Pdk/PdkOrderShipmentsMigrationTest.php`
- Possibly modify: `src/Migration/Pdk/PdkDeliveryOptionsMigration.php` (if the transformer output format is wrong)

- [ ] **Step 1: Read the test files and understand the expected format**

The old tests expect `DeliveryOptions::CARRIER` to be `['externalIdentifier' => 'postnl']`. The new PDK stores carrier as a plain string like `'POSTNL'`. Update all assertions to match the new format.

Read the PDK's `DeliveryOptions::toStorableArray()` to understand the new output format.

- [ ] **Step 2: Update PdkDeliveryOptionsMigrationTest assertions**

Update all dataset entries and assertions where the expected output contains `['externalIdentifier' => ...]` to use the new V2 carrier string format.

- [ ] **Step 3: Update PdkOrderShipmentsMigrationTest assertions**

Same approach — update carrier format expectations.

- [ ] **Step 4: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Migration/Pdk/PdkDeliveryOptionsMigrationTest.php tests/Unit/Migration/Pdk/PdkOrderShipmentsMigrationTest.php`
Expected: All pass.

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/Migration/Pdk/
git commit -m "test: update migration test assertions for V2 carrier format"
```

---

## Task 12: Fix snapshot-based tests for new Carrier model structure

Tests using snapshot assertions expect the old Carrier model structure (with `externalIdentifier`, `id`, `name`, etc.). The new model has completely different properties.

**Failing tests:** PdkOrderRepositoryTest (1), PsPdkProductRepositoryTest (3), PdkProductSettingsMigrationTest (5), PdkSettingsMigrationTest (1)

**Files:**
- Modify: `tests/Unit/Pdk/Order/Repository/PdkOrderRepositoryTest.php`
- Modify: `tests/Unit/Pdk/Product/Repository/PsPdkProductRepositoryTest.php`
- Modify: `tests/Unit/Migration/Pdk/PdkProductSettingsMigrationTest.php`
- Modify: `tests/Unit/Migration/Pdk/PdkSettingsMigrationTest.php`
- Regenerate: `tests/__snapshots__/` (affected snapshot files)

- [ ] **Step 1: Identify which snapshots need regenerating**

Run the failing tests and check the snapshot diff output. If the tests use `toMatchSnapshot()`, regenerate:

```bash
docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Pdk/Order/Repository/PdkOrderRepositoryTest.php tests/Unit/Pdk/Product/Repository/PsPdkProductRepositoryTest.php tests/Unit/Migration/Pdk/PdkProductSettingsMigrationTest.php tests/Unit/Migration/Pdk/PdkSettingsMigrationTest.php -d --update-snapshots
```

- [ ] **Step 2: Review the regenerated snapshots**

Verify the new snapshot content makes sense — carrier should appear as V2 name string, not as an object with `externalIdentifier`.

- [ ] **Step 3: Fix any non-snapshot assertion failures**

If tests have inline assertions (not just snapshots), update those too.

- [ ] **Step 4: Run the tests**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest tests/Unit/Pdk/Order/Repository/ tests/Unit/Pdk/Product/Repository/ tests/Unit/Migration/Pdk/PdkProductSettingsMigrationTest.php tests/Unit/Migration/Pdk/PdkSettingsMigrationTest.php`
Expected: All pass.

- [ ] **Step 5: Commit**

```bash
git add tests/__snapshots__/ tests/Unit/
git commit -m "test: regenerate snapshots for V2 carrier model"
```

---

## Task 13: Fix remaining isolated test failures

**Failing tests:** AbstractPsMigrationTest (1), PsOrderStatusServiceTest (1)

**Files:**
- Modify: `tests/Unit/Migration/AbstractPsMigrationTest.php`
- Modify: `tests/Unit/Pdk/Order/Service/PsOrderStatusServiceTest.php`

- [ ] **Step 1: Diagnose AbstractPsMigrationTest**

Read the test and the error. It fails with "Undefined offset: 3" — likely a mock data issue where the PDK's new model expects different data shape. Fix the mock data or assertion.

- [ ] **Step 2: Diagnose PsOrderStatusServiceTest**

Read the test and error. Fix the underlying issue (likely a removed constant or changed method signature).

- [ ] **Step 3: Run the full test suite**

Run: `docker compose exec -w /tmp/modules/myparcelnl prestashop vendor/bin/pest`
Expected: All 130+ tests pass, 0 failures.

- [ ] **Step 4: Commit**

```bash
git add tests/
git commit -m "test: fix remaining test failures for PDK V2 compatibility"
```

---

## Checklist

- [x] Update PDK dependency to version with new carrier model (path-linked)
- [x] Create migration class `Migration5_1_0` (Task 1)
- [x] Migrate account data — re-fetch from API (Task 5)
- [x] Migrate carrier settings keys in configuration table (Task 1)
- [x] Update `myparcelnl_carrier_mapping` table entries (Task 2)
- [x] Migrate carrier field in `myparcelnl_order_data.data` JSON (Task 3)
- [x] Migrate carrier field in `myparcelnl_order_shipment.data` JSON + extract contractId (Task 4)
- [x] Register migration in `PsMigrationService` (Task 5)
- [x] Synchronous migration — no cron needed (Decision: sync, dataset is bounded per shop)
- [x] Update config/pdk.php carrier references (Task 6)
- [x] Fix source code for removed PDK APIs — `mapNewToLegacyCarrierName`, `carrierCollectionToLegacyFormat` (Task 7)
- [x] Update test factories and data (Task 8)
- [x] Fix removed Carrier properties — `externalIdentifier`, `enabled`, `name` (Task 9)
- [x] Fix removed `convertCarrierToLegacyFormat()` in shipping hooks (Task 10)
- [x] Fix DeliveryOptions model changes in migration tests (Task 11)
- [x] Regenerate snapshots for new Carrier model (Task 12)
- [x] Fix remaining isolated test failures (Task 13)
- [x] Fix carrier logo filename mismatch — use legacy names via `getLegacyCarrierIdentifier()`, skip missing logos gracefully
- [ ] Test checkout flow with new carrier format (manual, after all tests green)
- [ ] Test order export with migrated data (manual, after all tests green)

# Capabilities-driven carrier filtering — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the hardcoded `countriesPerPlatformAndCarrier` carrier-country allowlist from the MyParcel PrestaShop module. Filter carriers at checkout via a single per-cart `CarrierCapabilitiesRepository::getCapabilitiesForRecipientCountry()` call.

**Architecture:** Replace `PsCountryService::getCountriesForCarrier()` and the hook's `getAllowedCountryIdsForCarrier()` helper with a single private helper on `HasPsCarrierListHooks` that returns the set of V2 carrier names the API supports for the cart's destination country. Comparison stays end-to-end V2 because `Migration5_1_0` already converts stored carrier names to V2 on upgrade. On any `Throwable` from the API call, the helper logs and returns `null`; the hook treats `null` as "skip filtering" (fail-open).

**Tech Stack:** PHP 7.4+, Pest 1.x, MyParcel PDK with `CarrierCapabilitiesRepository::getCapabilitiesForRecipientCountry()` (currently provided by `~/projects/pdk` linked locally; will be a published version constraint at release).

**Spec:** `docs/superpowers/specs/2026-05-06-countries-per-carrier-replacement-design.md`

---

## Prerequisites

- Local PDK at `~/projects/pdk` is linked into this module (`pdk-dev-on`) so `vendor/myparcelnl/pdk` exposes `MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository::getCapabilitiesForRecipientCountry()` and the test helper `MyParcelNL\Pdk\Tests\mockPdkProperty()`.
- All commands run inside the docker container per project convention. Wrap them as: `docker compose exec prestashop bash -c "cd /tmp/modules/myparcelnl && <command>"`. The plan shows the inner command only.

## Files

| File | Action |
|---|---|
| `tests/Unit/Hooks/HasPsCarrierListHooksTest.php` | Rewrite — five capability-mocked scenarios, replacing the static-config-driven cases |
| `src/Hooks/HasPsCarrierListHooks.php` | Replace `getAllowedCountryIdsForCarrier()` with `getSupportedCarriersForCountry()`; rewrite the inner loop |
| `src/Service/PsCountryService.php` | Delete `getCountriesForCarrier()`; remove imports used only by it |
| `src/Contract/PsCountryServiceInterface.php` | Delete `getCountriesForCarrier()` |
| `config/pdk.php` | Delete the `'countriesPerPlatformAndCarrier'` factory and any imports used only by it |

---

## Task 1: Rewrite the hook test against capabilities mocks

The current test in `tests/Unit/Hooks/HasPsCarrierListHooksTest.php` is data-driven against the static `countriesPerPlatformAndCarrier` config. Replace it with five focused tests that drive the new behavior, mocking `CarrierCapabilitiesRepository` per-test via `mockPdkProperty()` (same pattern used in `tests/Unit/Pdk/Action/Backend/Account/PsUpdateAccountActionTest.php:45`).

**Files:**
- Modify: `tests/Unit/Hooks/HasPsCarrierListHooksTest.php`

- [ ] **Step 1.1: Confirm baseline test passes today**

```
composer test -- --filter="filters carriers"
```

Expected: PASS for all six current data sets (`NL`, `BE`, `BE (sendmyparcel)`, `FR`, `US`, `AX`).

- [ ] **Step 1.2: Replace the test file**

Overwrite `tests/Unit/Hooks/HasPsCarrierListHooksTest.php` with:

```php
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
```

- [ ] **Step 1.3: Run the new tests and verify they fail**

```
composer test -- --filter="HasPsCarrierListHooks"
```

Expected: at least the four data-bearing tests fail. The empty-mappings test may pass coincidentally because the hook already returns early when `mappings->isEmpty()`. Don't commit yet — the failing tests pair with the implementation in Task 2.

---

## Task 2: Replace the hook implementation with a capabilities-driven helper

**Files:**
- Modify: `src/Hooks/HasPsCarrierListHooks.php`

- [ ] **Step 2.1: Overwrite the file**

Replace the entire content of `src/Hooks/HasPsCarrierListHooks.php` with:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use Address;
use Cart;
use Country;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPsCarrierListHooks
{
    /**
     * Filter carriers from the checkout delivery-option list using the MyParcel
     * capabilities API: a carrier is kept iff capabilities reports it for the cart's
     * destination country.
     *
     * @param  array $params
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionFilterDeliveryOptionList(array &$params): void
    {
        /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $carrierMappingRepository */
        $carrierMappingRepository = Pdk::get(PsCarrierMappingRepository::class);
        $mappings                 = $carrierMappingRepository->all();

        if ($mappings->isEmpty()) {
            return;
        }

        $country   = $this->getCountryFromCart($params['cart'] ?? $this->context->cart ?? new Cart());
        $supported = $this->getSupportedCarriersForCountry((string) $country->iso_code);

        if ($supported === null) {
            // Capabilities call failed — fail-open, keep every carrier visible.
            return;
        }

        $deliveryOptionList = $params['delivery_option_list'] ?? [];

        foreach ($deliveryOptionList as $addressId => $item) {
            foreach ($item as $key => $value) {
                $carrierMapping = $this->getCarrierMapping($value['carrier_list'] ?? [], $mappings);

                if (! $carrierMapping) {
                    continue;
                }

                [$v2Name] = explode(':', $carrierMapping->getMyparcelCarrier(), 2);

                if (in_array($v2Name, $supported, true)) {
                    continue;
                }

                unset($params['delivery_option_list'][$addressId][$key]);
            }
        }
    }

    /**
     * Hook for displaying content in the carrier list. Filtering is handled by
     * hookActionFilterDeliveryOptionList; this hook is registered for compatibility
     * but produces no output.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayCarrierList(array $params): string
    {
        return '';
    }

    /**
     * Returns the V2 carrier names that capabilities lists as supported for the
     * given destination country, or null if the API call failed (fail-open signal).
     *
     * @param  string $countryIso ISO 3166-1 alpha-2 destination country code
     *
     * @return null|string[]
     */
    private function getSupportedCarriersForCountry(string $countryIso): ?array
    {
        /** @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository $repository */
        $repository = Pdk::get(CarrierCapabilitiesRepository::class);

        try {
            $capabilities = $repository->getCapabilitiesForRecipientCountry($countryIso);
        } catch (Throwable $exception) {
            Logger::error('Failed to fetch carrier capabilities for country.', [
                'country' => $countryIso,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        $names = [];

        foreach ($capabilities as $capability) {
            $names[$capability->getCarrier()] = true;
        }

        return array_keys($names);
    }

    /**
     * @param  array                                   $carrierList
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $mappings
     *
     * @return null|\MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping
     */
    private function getCarrierMapping(
        array      $carrierList,
        Collection $mappings
    ): ?MyparcelnlCarrierMapping {
        $carrierArray = Arr::first($carrierList);

        /** @var \Carrier $psCarrier */
        $psCarrier = $carrierArray['instance'] ?? null;

        return $mappings
            ->filter(function (MyparcelnlCarrierMapping $mapping) use ($psCarrier) {
                return $mapping->getCarrierId() === $psCarrier->id;
            })
            ->first();
    }

    /**
     * @param  \Cart $cart
     *
     * @return \Country
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getCountryFromCart(Cart $cart): Country
    {
        $configurationService = Pdk::get(PsConfigurationServiceInterface::class);

        if ($cart->id_address_delivery) {
            $address = new Address($cart->id_address_delivery);
            $country = new Country($address->id_country);
        } else {
            $country = $this->context->country ?? new Country((int) $configurationService->get('PS_COUNTRY_DEFAULT'));
        }

        return $country;
    }
}
```

**Notes for the implementer:**
- The `explode(':', ..., 2)` call strips the optional `:contractId` suffix some legacy mappings carry (e.g. `'DHL_FOR_YOU:1234'`), matching the existing convention in `PsCountryService::getCountriesForCarrier()` before this plan removes it.
- The new code talks V2 names end-to-end. After `Migration5_1_0` runs on shop upgrade, all stored mappings are V2; capabilities responses are V2; `Carrier::CARRIER_NAME_TO_LEGACY_MAP` is no longer needed in this path.
- The `PsCountryServiceInterface` import drops from the `use` block; `PsCountryService::getCountriesForCarrier()` is removed in Task 3.

- [ ] **Step 2.2: Run the hook test suite**

```
composer test -- --filter="HasPsCarrierListHooks"
```

Expected: all five tests PASS.

- [ ] **Step 2.3: Commit**

```bash
git add src/Hooks/HasPsCarrierListHooks.php tests/Unit/Hooks/HasPsCarrierListHooksTest.php
git commit -m "feat(checkout): filter carriers via capabilities API instead of static country map

Replace the per-carrier country allowlist with a single capabilities call
keyed on the cart's destination country. Drops the per-cart filtering loop
to one PDK call and a set lookup. Fail-open with logging when the API
call errors so transient outages do not block checkout."
```

---

## Task 3: Remove `getCountriesForCarrier` from the country service

**Files:**
- Modify: `src/Service/PsCountryService.php`
- Modify: `src/Contract/PsCountryServiceInterface.php`

- [ ] **Step 3.1: Remove the interface method**

In `src/Contract/PsCountryServiceInterface.php`, delete the `getCountriesForCarrier` declaration (around line 21) and its docblock. Leave the rest of the interface untouched.

- [ ] **Step 3.2: Remove the method and unused imports**

In `src/Service/PsCountryService.php`, delete the `getCountriesForCarrier()` method and its docblock (currently around lines 28–58). Then drop imports that became unused:

```php
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
```

Verify each removed import has no remaining reference in the file before deleting:

```
grep -n "CountryCodes\|Pdk::\|PropositionService" src/Service/PsCountryService.php
```

The expected post-edit `use` block:

```php
use Country;
use Country as PsCountry;
use MyParcelNL\PrestaShop\Contract\PsCountryServiceInterface;
use MyParcelNL\Sdk\Support\Str;
```

- [ ] **Step 3.3: Run the suite to confirm no other caller broke**

```
composer test
```

Expected: full suite PASSES. The pre-implementation grep showed only `HasPsCarrierListHooks` used `getCountriesForCarrier`; this run is the safety net.

- [ ] **Step 3.4: Commit**

```bash
git add src/Service/PsCountryService.php src/Contract/PsCountryServiceInterface.php
git commit -m "refactor(country-service): remove getCountriesForCarrier

The hook trait now derives carrier support from the capabilities API.
PsCountryService keeps its ISO/PS country-id helpers for other consumers."
```

---

## Task 4: Delete `countriesPerPlatformAndCarrier` from `config/pdk.php`

**Files:**
- Modify: `config/pdk.php`

- [ ] **Step 4.1: Delete the factory and its surrounding doc comment**

In `config/pdk.php`, remove the block that starts with the doc comment

```
/**
 * Countries per platform and carrier.
 * This is intended as a temporary solution until we can use the Carrier Capabilities service. This is copied from the Delivery Options.
 *
 * @TODO Replace when Carrier Capabilities is available.
 */
'countriesPerPlatformAndCarrier' => value([
```

…through the closing `]),` (currently ~lines 200–565). Leave a single blank line between the surrounding entries.

- [ ] **Step 4.2: Drop now-unused imports**

After deleting the block, scan the top of `config/pdk.php` for imports referenced only by the deleted block. Likely candidates:

```php
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Proposition\Proposition;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
```

For each, confirm with grep before removing:

```
grep -n "CountryCodes\|Proposition\b\|RefCapabilitiesSharedCarrierV2" config/pdk.php
```

Keep any import with a remaining reference. If `Proposition::*` or `RefCapabilitiesSharedCarrierV2::*` is referenced elsewhere in `config/pdk.php`, leave that import in place.

- [ ] **Step 4.3: Run the suite**

```
composer test
```

Expected: full suite PASSES. After Task 3 there are no remaining `Pdk::get('countriesPerPlatformAndCarrier')` consumers; removing the factory cannot break runtime code.

- [ ] **Step 4.4: Commit**

```bash
git add config/pdk.php
git commit -m "chore(config): remove countriesPerPlatformAndCarrier static map

The capabilities API now provides this data per request. New carriers
flow through automatically once they appear in capabilities, removing
the need to maintain a hardcoded per-platform allowlist in the module."
```

---

## Task 5: Final verification

**Files:** none changed; verification only.

- [ ] **Step 5.1: Run the full suite once more**

```
composer test
```

Expected: PASS.

- [ ] **Step 5.2: Confirm zero remaining references to the removed names**

```
grep -rn "countriesPerPlatformAndCarrier\|getCountriesForCarrier\|getAllowedCountryIdsForCarrier" src config tests
```

Expected: zero hits.

- [ ] **Step 5.3: Re-run the spec's "broader audit" sweep**

```
grep -rn "Carrier::CARRIER_\|RefCapabilitiesSharedCarrierV2::" src config | grep -v "src/Migration/"
```

Expected: zero hits. The remaining hardcoded carrier references should all be inside `src/Migration/` (the one-time pre-PDK and 5.1.0 migrations the spec called out as out of scope). Anything outside `src/Migration/` is a regression and should be removed before declaring the change done.

---

## Self-review (run after writing this plan)

- [x] **Spec coverage:** Architecture (Tasks 2–4); data flow + error handling (Task 2); testing (Task 1); broader audit (Task 5). Migration concern (`PdkCarrierMigration::LEGACY_CARRIER_MAP`) was explicitly out of scope; no task touches it.
- [x] **No placeholders:** every code step shows full code; every command step shows the command and expected outcome.
- [x] **Type/name consistency:** `getSupportedCarriersForCountry`, `hookActionFilterDeliveryOptionList`, `hookDisplayCarrierList`, `getCarrierMapping`, `getCountryFromCart`, `CarrierCapabilitiesRepository`, `getCapabilitiesForRecipientCountry`, `MyparcelnlCarrierMapping` — all aligned across tasks. Test helper names (`fakeCapabilitiesRepositoryReturning`, `fakeCapabilitiesRepositoryThrowing`, `setupModuleWithMappings`, `survivingV2Names`) appear only in Task 1 and are used consistently across the five test cases.
- [x] **End-to-end V2 idiom:** all carrier comparisons happen between V2 names — no legacy↔V2 mapping in production code, since `Migration5_1_0` already converted stored data on upgrade.

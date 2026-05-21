# Replace `countriesPerPlatformAndCarrier` with PDK capabilities

**Date:** 2026-05-06
**Status:** Approved (pending implementation plan)
**Scope:** PHP backend of the MyParcel PrestaShop module only.

## Goal

Remove the hardcoded `countriesPerPlatformAndCarrier` carrier-country allowlist from the PrestaShop module. Replace it with a single per-cart capabilities call against the PDK's `CapabilitiesService`. After this change the module contains no per-carrier country definitions; adding a new carrier requires only a PDK / SDK update.

## Non-goals

- No changes to PDK, SDK, proposition JSON, or generated SDK models.
- No changes to frontend / admin app / delivery-options widget.
- `PdkCarrierMigration::LEGACY_CARRIER_MAP` (one-time pre-PDK setting-prefix lookup) is left in place — it is migration data, not a carrier definition.

## Broader carrier-definition audit (PHP `src/` + `config/`)

Confirmed before approving the design that no other carrier-specific definitions remain in the module after this change:

- No carrier-specific branching (`if carrier === X`, `match` on carrier, etc.) in production logic.
- No hardcoded subscriptions, contract IDs, package types, or delivery types keyed per carrier.
- All carrier-specific behavior routes through the PDK (`PropositionService::getCarriers()`, `CarrierCapabilitiesRepository`, `Carrier` model) and the `MyparcelnlCarrierMapping` entity (merchant-managed PS↔MyParcel mapping).

The only remaining hardcoded carrier references are in `src/Migration/Pdk/PdkCarrierMigration.php`:

1. `LEGACY_CARRIER_MAP` (`POSTNL` / `DHL` setting-prefix map, line 26–29) — recognises pre-PDK shop-config keys like `MYPARCELNL_POSTNL` during one-time upgrade migration.
2. Hardcoded `AccountPlatform::SENDMYPARCEL_ID` (line 98) — forces the same migration to consider BE-proposition carriers regardless of active platform.

Both apply only to upgrades from pre-PDK installs and do not block new carriers from appearing automatically. They are intentionally out of scope.

### Outcome

After this change, a new carrier added to capabilities (visible after account refresh) flows end-to-end with no module change required:

- **Carrier list** — `PsCarrierService` enumerates from `PropositionService::getCarriers()`, which reflects PDK data.
- **Checkout filter** — the new `getSupportedCarriersForCountry` accepts whatever carriers the capabilities API returns for the cart country.
- **Mapping** — merchants create a `MyparcelnlCarrierMapping` for the new carrier in the PrestaShop admin via the existing UI.

## Background

`config/pdk.php` declares `countriesPerPlatformAndCarrier` (≈360 lines, 3 propositions × ~7 carriers each, with `deliveryCountries` / `pickupCountries` / `fakeDelivery`). `PsCountryService::getCountriesForCarrier()` reads it. Its only consumer is `HasPsCarrierListHooks::getAllowedCountryIdsForCarrier()`, called from `hookActionFilterDeliveryOptionList`. The hook receives a cart with one specific shipping country and uses the country list to drop unsupported carriers from `delivery_option_list`.

Because there is exactly one consumer and it already has the cart country in hand, the question we actually need to answer at runtime is `(country) → which carriers ship there?` — not `(carrier) → all supported countries`. The PDK's `CapabilitiesService::getCapabilities()` answers this with one call per country when invoked with only `recipient.country_code`.

## Architecture

Three files change. No new classes.

### `config/pdk.php`
- Delete the `'countriesPerPlatformAndCarrier' => value([...])` factory and its surrounding doc comment.
- Remove now-unused imports (`Carrier`, `Platform`, `CountryCodes`) only if they were imported solely for this block; verify with grep before removing.

### `src/Service/PsCountryService.php` and `src/Contract/PsCountryServiceInterface.php`
- Delete `getCountriesForCarrier(string $carrierName): array` from both. The ISO ↔ PrestaShop country-ID helpers (`getCountryIdByIsoCode`, `getCountryIdsByIsoCodes`) stay; they have other callers.
- Drop the now-unused `PropositionService` import in `PsCountryService`.

### `src/Hooks/HasPsCarrierListHooks.php`
- Delete `getAllowedCountryIdsForCarrier()`.
- Add `getSupportedCarriersForCountry(string $countryIso): ?array` — calls `CapabilitiesService::getCapabilities(['recipient' => ['country_code' => $countryIso]])` once and returns the unique set of v2 carrier names from the response, or `null` on failure.
- Rewrite `hookActionFilterDeliveryOptionList` to call `getSupportedCarriersForCountry` once with the cart country, then for each `delivery_option_list` entry drop it iff the mapped (legacy) carrier resolves to a v2 name that is not in the supported set. When the supported set is `null` (capabilities call failed), skip filtering.
- Carrier mappings store legacy names (e.g. `"postnl"`, `"dhlforyou"`); capabilities returns v2 names (e.g. `"POSTNL"`, `"DHL_FOR_YOU"`). Normalise via `PropositionService::mapNewToLegacyCarrierName()` (or comparison after running the supported set through it) so the existing storage shape stays untouched.

## Data flow

```
hookActionFilterDeliveryOptionList(params)
  ├─ load all PsCarrierMappingRepository mappings; early-return if empty   [unchanged]
  ├─ derive cart country via getCountryFromCart()                           [unchanged]
  ├─ supported = getSupportedCarriersForCountry(country.iso_code)
  │     └─ CapabilitiesService::getCapabilities(
  │            ['recipient' => ['country_code' => $iso]]
  │        ) → RefCapabilitiesResponseCapabilityV2[]
  │        → array_map(getCarrier()) → array_unique → string[]
  │        on Throwable → Logger::error(...) → return null
  └─ for each delivery_option_list entry:
        if supported is null: skip filtering (fail-open).
        else: find mapping; if its myparcel carrier (legacy, normalised)
              is not in supported, unset the entry.
```

One capabilities call per cart-country. PDK's `CarrierCapabilitiesRepository` cache keys on a hash of args; if the implementation routes through the repository the same country reuses the cached result. (Routing decision — repository vs. service direct — left to the implementation plan; both end at the same SDK call.)

## Error handling

- Any `Throwable` from `CapabilitiesService::getCapabilities()` is caught inside `getSupportedCarriersForCountry`.
- `Logger::error()` is called with the country ISO and the exception message.
- The helper returns `null`. The caller interprets `null` as "skip filtering" — all carriers remain visible. This is fail-open with a logged trail and matches the explicit decision recorded in this spec's discussion.
- Empty array (capabilities ran successfully and returned no carriers for this country) is a real result, not an error: it correctly drops every carrier.

## Testing

Updates to `tests/Unit/Hooks/HasPsCarrierListHooksTest.php`. The existing fixtures (carrier list at lines 49–55, scenarios from line 132 onward) cover the cases we need; the test setup needs a mock for `CapabilitiesService`.

Cases:
- **Happy path** — capabilities returns a subset of mapped carriers; only the unsupported entries are removed.
- **All supported** — capabilities returns every mapped carrier; `delivery_option_list` is unchanged.
- **None supported** — capabilities returns `[]`; every entry is removed.
- **Fail-open** — capabilities throws; `delivery_option_list` is unchanged and `Logger::error` is asserted.
- **Empty mappings** — repository returns no mappings; hook short-circuits, no capabilities call (verify the mock is never invoked).

## Migration / rollout

- No data migration. The change is code-only and behaviour-preserving for any carrier whose true country support matches what was hardcoded.
- For carriers that previously had `fakeDelivery: true` (PostNL Belgium pickup-only countries, UPS, BPost on SendMyParcel, BRT on Italie), the API now decides; if its answer differs from the hardcoded allowlist the visible carrier set will change. This is the intended cleanup.
- Plugin version bump on release; no schema changes.

## Open questions for the implementation plan

- Whether to call `CapabilitiesService` directly or via `CarrierCapabilitiesRepository` (cache wrapper). Both are valid; the repository gives free caching and a single seam for future evolution. The capabilities call signature is identical.
- Exact mechanism for legacy ↔ v2 carrier-name comparison: pre-map the supported set down to legacy and compare strings, or map the mapping's carrier up to v2 once before the loop. Either works.

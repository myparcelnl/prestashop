# PrestaShop 9+ Modernization Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove all PrestaShop 1.7/8 and PHP 7.4 compatibility shims, fully embrace PS9/10 architecture and PHP 8.1+ language features.

**Architecture:** Replace legacy workarounds with native PS9 Symfony integration: Symfony form builders for product page, PrestaShopAdminController base class, request_stack for page detection, modern Context services instead of the singleton. Adopt PHP 8.1+ features throughout (enums, readonly, typed properties, nullsafe operators).

**Tech Stack:** PHP 8.1+, PrestaShop 9.x/10.x, Symfony 6.4+, Doctrine ORM 2.7+, Vite/Vue 3 (js-pdk)

---

## Phase 1: Drop Legacy Compatibility Layer

### Task 1: Remove PHP 7.4 compatibility patterns

**Files:**
- Modify: `src/Pdk/Frontend/Service/PsViewService.php`
- Modify: `src/Hooks/HasPdkProductHooks.php`
- Modify: `src/Controller/AbstractAdminController.php`
- Modify: `composer.json`

- [ ] **Step 1: Update PHP version constraint**

In `composer.json`, change the PHP requirement:

```json
"require": {
    "php": ">=8.1.0"
}
```

And in `composer.json` config, remove the platform override:

```json
"config": {
    "prepend-autoloader": false,
    "allow-plugins": {
        "pestphp/pest-plugin": true
    }
}
```

- [ ] **Step 2: Use nullsafe operator in PsViewService**

In `src/Pdk/Frontend/Service/PsViewService.php`, replace the ternary null check:

```php
// Before:
$request = Pdk::get('getPsService')('request_stack')->getCurrentRequest();
$legacyController = $request ? $request->attributes->get('_legacy_controller') : null;

// After:
$legacyController = Pdk::get('getPsService')('request_stack')
    ->getCurrentRequest()
    ?->attributes
    ->get('_legacy_controller');
```

- [ ] **Step 3: Use str_contains() in HasPdkProductHooks**

In `src/Hooks/HasPdkProductHooks.php`, replace strpos pattern:

```php
// Before:
if (strpos($html, 'data-pdk-context') !== false

// After:
if (str_contains($html, 'data-pdk-context')
```

- [ ] **Step 4: Add typed properties throughout**

Add typed property declarations where currently using `@var` docblocks. Example in `src/Repository/AbstractPsObjectRepository.php`:

```php
// Before:
/** @var class-string<T> */
protected $entity;
/** @var \Doctrine\ORM\EntityManager */
protected $entityManager;
/** @var \Doctrine\ORM\EntityRepository */
protected $entityRepository;

// After:
/** @var class-string<T> */
protected string $entity;
protected ?\Doctrine\ORM\EntityManager $entityManager = null;
protected ?\Doctrine\ORM\EntityRepository $entityRepository = null;
```

- [ ] **Step 5: Run tests**

Run: `composer test`
Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "chore: drop PHP 7.4 support, require PHP 8.1+"
```

---

### Task 2: Remove legacy Dispatcher fallback from PsViewService

**Files:**
- Modify: `src/Pdk/Frontend/Service/PsViewService.php`

- [ ] **Step 1: Write failing test**

Create a test that verifies `getPage()` uses only the Symfony request stack:

```php
it('resolves page from symfony request stack', function () {
    $mockRequest = Mockery::mock(Request::class);
    $mockRequest->attributes = new ParameterBag(['_legacy_controller' => 'AdminProducts']);

    $mockStack = Mockery::mock(RequestStack::class);
    $mockStack->shouldReceive('getCurrentRequest')->andReturn($mockRequest);

    // Mock the PDK service resolution
    MockPdk::mock('getPsService', fn () => fn ($service) => $mockStack);

    $viewService = Pdk::get(ViewServiceInterface::class);
    expect($viewService->isProductPage())->toBeTrue();
});
```

- [ ] **Step 2: Remove legacy Dispatcher fallback**

In `src/Pdk/Frontend/Service/PsViewService.php`, replace the entire `getPage()` method:

```php
use Symfony\Component\HttpFoundation\RequestStack;

private function getPage(): string
{
    /** @var RequestStack $requestStack */
    $requestStack = Pdk::get('getPsService')('request_stack');

    return $requestStack
        ->getCurrentRequest()
        ?->attributes
        ->get('_legacy_controller', '') ?? '';
}
```

Remove the `use Dispatcher;` and `use RuntimeException;` imports.

- [ ] **Step 3: Run tests**

Run: `composer test`
Expected: All tests pass.

- [ ] **Step 4: Commit**

```bash
git add src/Pdk/Frontend/Service/PsViewService.php
git commit -m "refactor: use Symfony request stack exclusively for page detection"
```

---

### Task 3: Remove legacy checkout step listener

**Files:**
- Modify: `views/js/frontend/checkout/src/config/pdkCheckoutInitialize.ts`

- [ ] **Step 1: Remove changedCheckoutStep listener**

Replace `pdkCheckoutInitialize.ts` with PS9-only implementation:

```typescript
const isDeliveryStep = () => document.querySelector('#checkout-delivery-step.js-current-step');

export const pdkCheckoutInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    if (isDeliveryStep()) {
      resolve();
      return;
    }

    // PS 9 (Hummingbird): steps are Bootstrap Tabs.
    // Observe the delivery step element for the js-current-step class being added.
    const deliveryStep = document.querySelector('#checkout-delivery-step');

    if (deliveryStep) {
      const observer = new MutationObserver(() => {
        if (isDeliveryStep()) {
          observer.disconnect();
          resolve();
        }
      });

      observer.observe(deliveryStep, {attributes: true, attributeFilter: ['class']});
    }
  });
};
```

- [ ] **Step 2: Remove legacy DOM traversal fallback in useShippingMethodData**

In `views/js/frontend/checkout/src/utils/useShippingMethodData.ts`, remove the PS 1.7/8 fallback:

```typescript
carrierData.forEach((carrier) => {
    const $item = carrier.row.closest('.delivery-option__item');
    const $checkbox = $item.find('input[type="radio"][name^="delivery_option"]');

    data.shippingMethodName = $checkbox.attr('name') ?? '';

    const value = $checkbox.val()?.toString();

    data.shippingMethods.push({
      value: value ?? '?',
      carrier: carrier.carrier,
      row: carrier.row,
      input: $checkbox,
    });
  });
```

- [ ] **Step 3: Build and test**

Run: `yarn build:js:dev:frontend`

Test manually on PS9 checkout page: delivery options should render when selecting a MyParcel carrier.

- [ ] **Step 4: Commit**

```bash
git add views/js/frontend/checkout/
git commit -m "refactor: remove PS 1.7/8 checkout compatibility, use PS9 Hummingbird only"
```

---

## Phase 2: Migrate to PS9 Controller Architecture

### Task 4: Migrate from FrameworkBundleAdminController to PrestaShopAdminController

**Files:**
- Modify: `src/Controller/AbstractAdminController.php`
- Modify: `config/services.yml`

- [ ] **Step 1: Update base class**

Replace `AbstractAdminController.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;

/**
 * @property \MyParcelNL $module
 */
abstract class AbstractAdminController extends PrestaShopAdminController
{
    public function __construct()
    {
        // Trigger PDK setup
        new MyParcelNL();
    }
}
```

- [ ] **Step 2: Verify services.yml tags**

`config/services.yml` should already have `autoconfigure: true` which applies the correct tags for `PrestaShopAdminController`. Verify controllers still resolve:

Run (inside container):
```bash
php bin/console debug:container MyParcelNL\\PrestaShop\\Controller\\SettingsController
```

Expected: Service info displayed with `controller.service_arguments` tag.

- [ ] **Step 3: Test settings page and PDK endpoint**

Navigate to the settings page and verify it renders. Execute a PDK action and verify the response.

- [ ] **Step 4: Commit**

```bash
git add src/Controller/AbstractAdminController.php
git commit -m "refactor: migrate to PrestaShopAdminController base class"
```

---

## Phase 3: Native Product Page Integration

### Task 5: Migrate product page from displayAdminProductsExtra to actionProductFormBuilderModifier

**Files:**
- Create: `src/Form/ProductFormModifier.php`
- Modify: `src/Hooks/HasPdkProductHooks.php`
- Modify: `src/Hooks/HasPdkRenderHooks.php`
- Modify: `config/services.yml`

Reference: https://devdocs.prestashop-project.org/9/modules/sample-modules/extend-product-page/

- [ ] **Step 1: Create ProductFormModifier service**

Create `src/Form/ProductFormModifier.php`:

```php
<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Form;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductId;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductFormModifier
{
    public function modify(int $productId, FormBuilderInterface $formBuilder): void
    {
        /** @var PdkProductRepositoryInterface $repository */
        $repository = Pdk::get(PdkProductRepositoryInterface::class);
        $product = $repository->getProduct($productId);

        $html = Frontend::renderProductSettings($product);

        $formBuilder->add('myparcel_settings', TextareaType::class, [
            'label' => false,
            'required' => false,
            'mapped' => false,
            'data' => '',
            'attr' => ['class' => 'd-none'],
            'form_theme' => '@Modules/myparcelnl/views/templates/admin/product-settings.html.twig',
        ]);
    }
}
```

Note: The exact form integration depends on how the PDK renders its Vue components. The `TextareaType` with a custom form theme is one approach — the Twig template would output the PDK HTML directly. An alternative is a fully custom FormType. This task will need refinement once the PDK's rendering model is tested with Symfony forms.

- [ ] **Step 2: Register the service**

Add to `config/services.yml`:

```yaml
  MyParcelNL\PrestaShop\Form\ProductFormModifier:
    class: MyParcelNL\PrestaShop\Form\ProductFormModifier
    autowire: true
    public: true
```

- [ ] **Step 3: Replace displayAdminProductsExtra with actionProductFormBuilderModifier**

In `src/Hooks/HasPdkProductHooks.php`, replace the hook method:

```php
/**
 * @param array $params
 */
public function hookActionProductFormBuilderModifier(array $params): void
{
    /** @var ProductFormModifier $modifier */
    $modifier = Pdk::get(ProductFormModifier::class);
    $modifier->modify((int) $params['id'], $params['form_builder']);
}
```

Remove `hookDisplayAdminProductsExtra`, `renderProductSettings`, `$pendingProductSettingsHtml`, and the related regex/context extraction logic.

- [ ] **Step 4: Remove HTMLPurifier workaround from HasPdkRenderHooks**

In `src/Hooks/HasPdkRenderHooks.php`, remove the product context footer injection:

```php
// Remove this block:
if (self::$pendingProductSettingsHtml) {
    $html .= self::$pendingProductSettingsHtml;
    if (preg_match('/id="([^"]+)"/', self::$pendingProductSettingsHtml, $idMatch)) {
        $id = $idMatch[1];
        $html .= "<script>document.getElementById('{$id}-placeholder')?.replaceWith(document.getElementById('{$id}'));</script>";
    }
}
```

- [ ] **Step 5: Create Twig template for product settings**

Create `views/templates/admin/product-settings.html.twig`:

```twig
{% block textarea_widget %}
  {{ value|raw }}
{% endblock %}
```

Note: This template approach needs testing. The PDK component includes `data-pdk-context` which must NOT be sanitized. Since `actionProductFormBuilderModifier` renders through Symfony forms, the output path may differ from the `displayAdminProductsExtra` hook. Verify that the form theme output is not passed through HTMLPurifier.

- [ ] **Step 6: Register the hook**

In `myparcelnl.php` install method or hook registration, ensure `actionProductFormBuilderModifier` is registered. The dynamic hook detection via reflection should pick it up automatically since the method is named `hookActionProductFormBuilderModifier`.

- [ ] **Step 7: Test product settings page**

Navigate to a product edit page, open the Modules tab, and verify MyParcel settings render with form elements.

- [ ] **Step 8: Commit**

```bash
git add src/Form/ src/Hooks/ views/templates/admin/ config/services.yml
git commit -m "feat: migrate product page to actionProductFormBuilderModifier"
```

---

## Phase 4: Modern Context & Service Integration

### Task 6: Replace legacy Context singleton with PS9 Context services

**Files:**
- Modify: `src/Hooks/HasPdkCheckoutDeliveryOptionsHooks.php`
- Modify: `src/Hooks/HasPdkCheckoutHooks.php`
- Modify: `src/Hooks/HasPsShippingCostHooks.php`
- Modify: `src/Hooks/HasPsCarrierListHooks.php`
- Modify: `src/Pdk/Base/PsPdkBootstrapper.php`

PS9 introduced split context services:
- `PrestaShop\PrestaShop\Core\Context\ShopContext`
- `PrestaShop\PrestaShop\Core\Context\EmployeeContext`
- `PrestaShop\PrestaShop\Core\Context\CurrencyContext`
- `PrestaShop\PrestaShop\Core\Context\LanguageContext`
- `PrestaShop\PrestaShop\Core\Context\CountryContext`
- `PrestaShop\PrestaShop\Core\Context\LegacyControllerContext`

- [ ] **Step 1: Audit all Context singleton usage**

Search the codebase for `$this->context->` and `Context::getContext()` to identify all usages. Group them by what context data they access:

- `$this->context->cart` → Cart data (checkout hooks)
- `$this->context->controller` → Controller instance (script hooks)
- `$this->context->link` → Link generation (module class)
- `$this->context->country` → Country data (carrier list hooks)
- `$this->context->currency` → Currency data (installer)
- `$this->context->smarty` → Smarty template engine (delivery options hooks)

- [ ] **Step 2: Register PS9 context services in PsPdkBootstrapper**

Add to `src/Pdk/Base/PsPdkBootstrapper.php` in the service definitions:

```php
'ps.shopContext' => factory(function () {
    return Pdk::get('getPsService')('PrestaShop\PrestaShop\Core\Context\ShopContext');
}),
'ps.countryContext' => factory(function () {
    return Pdk::get('getPsService')('PrestaShop\PrestaShop\Core\Context\CountryContext');
}),
'ps.currencyContext' => factory(function () {
    return Pdk::get('getPsService')('PrestaShop\PrestaShop\Core\Context\CurrencyContext');
}),
'ps.languageContext' => factory(function () {
    return Pdk::get('getPsService')('PrestaShop\PrestaShop\Core\Context\LanguageContext');
}),
```

- [ ] **Step 3: Migrate context usages incrementally**

Replace `$this->context->country` usages with `CountryContext` in `HasPsCarrierListHooks.php`. Replace `$this->context->controller` usages in script services. Keep `$this->context->cart` and `$this->context->smarty` as-is — these are still provided by PrestaShop's hook system and have no PS9 replacement.

Note: The legacy `Context` singleton is still functional in PS9. This migration is recommended but not urgent. Prioritize replacing usages that access deprecated properties first. The `$this->context` object in hook methods is populated by PrestaShop's hook dispatcher and will continue to work for the foreseeable future.

- [ ] **Step 4: Test all affected flows**

Test: settings page, product page, checkout, order page, carrier list.

- [ ] **Step 5: Commit**

```bash
git add src/Hooks/ src/Pdk/Base/PsPdkBootstrapper.php
git commit -m "refactor: replace legacy Context singleton with PS9 context services"
```

---

### Task 7: Expand Symfony service registration

**Files:**
- Modify: `config/services.yml`
- Modify: `src/Pdk/Base/PsPdkBootstrapper.php`

- [ ] **Step 1: Evaluate which PHP-DI services can move to Symfony DI**

Services that interact with PrestaShop's Symfony container (controllers, form types, event subscribers) benefit from Symfony DI registration. Services internal to the PDK should remain in PHP-DI.

Candidates for Symfony DI:
- Controllers (already done)
- `ProductFormModifier` (from Task 5)
- Future event subscribers or form types

- [ ] **Step 2: Add autowiring defaults to services.yml**

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  MyParcelNL\PrestaShop\Controller\:
    resource: '../src/Controller/'
    public: true
    tags: ['controller.service_arguments']
```

This auto-registers all controllers without listing each one individually.

- [ ] **Step 3: Test all controllers still resolve**

Run (inside container):
```bash
php bin/console debug:container | grep MyParcelNL
```

- [ ] **Step 4: Commit**

```bash
git add config/services.yml
git commit -m "refactor: use Symfony DI autowiring for controllers"
```

---

## Phase 5: Script & Asset Modernization

### Task 8: Modernize backend script registration

**Files:**
- Modify: `src/Script/Service/PsBackendScriptService.php`
- Modify: `src/Hooks/HasPdkScriptHooks.php`

- [ ] **Step 1: Remove LegacyControllerContext compatibility**

In `PsBackendScriptService.php`, type-hint `$controller` to `LegacyControllerContext`:

```php
use PrestaShop\PrestaShop\Core\Context\LegacyControllerContext;

public function register(LegacyControllerContext $controller, string $path): void
{
    $this->addVue($controller, Pdk::get('vueVersion'));

    $adminPath = "{$path}views/js/backend/admin";

    $controller->addCSS("$adminPath/dist/style.css");
    $controller->addJS("$adminPath/dist/index.iife.js");

    $themeCss = sprintf('%s%s/themes/new-theme/public/theme.css', __PS_BASE_URI__, $controller->adminFolderName);
    $controller->addCSS($themeCss, 'all', 1);
}

protected function addVue(LegacyControllerContext $controller, string $version): void
{
    $controller->addJS($this->getVueCdnUrl($version), false);
}
```

Remove the `admin_webpath ?? adminFolderName` fallback — use `adminFolderName` directly.

- [ ] **Step 2: Remove comments about legacy support**

Remove all comments like "No type hint: PS 1.7/8 passes AdminController..." and "When dropping PS 1.7/8 support, type-hint to LegacyControllerContext instead."

- [ ] **Step 3: Test admin pages**

Verify settings page, order page, and product page all load JS/CSS correctly.

- [ ] **Step 4: Commit**

```bash
git add src/Script/Service/ src/Hooks/HasPdkScriptHooks.php
git commit -m "refactor: type-hint script services to PS9 LegacyControllerContext"
```

---

## Phase 6: Entity & Repository Modernization

### Task 9: Align entity registration with PS9 native approach

**Files:**
- Modify: `src/Repository/AbstractPsObjectRepository.php`
- Modify: `src/Pdk/Installer/Service/PsPreInstallService.php`

- [ ] **Step 1: Evaluate removing lazy entity manager loading**

The lazy loading in `AbstractPsObjectRepository` was added because PS9's `ModulesDoctrineCompilerPass` registers entities at container compilation. During first install, entities aren't available yet.

With PS9-only support, evaluate whether the lazy loading is still needed:
- If the module is always installed via `prestashop:module install` (which compiles the container before calling `install()`), the entity manager may still not have the module's entities during the install hook.
- The lazy loading pattern is safe and has no performance cost — **keep it** but remove the PS9-specific comment, as this is now the standard approach.

```php
/**
 * Lazy-loads the entity manager. Module Doctrine entities are registered at Symfony
 * container compilation time (ModulesDoctrineCompilerPass), which is not available
 * during the module's first install.
 */
protected function getEntityManager(): \Doctrine\ORM\EntityManager
```

- [ ] **Step 2: Evaluate PsPreInstallService::prepareEntityManager()**

This method manually registers entity namespaces with the Doctrine driver chain at runtime. With PS9-only support, `ModulesDoctrineCompilerPass` handles this automatically for installed modules. The `prepareEntityManager()` method is only needed for first install (before the container recompiles).

Keep the method but add a note:

```php
/**
 * Registers entity namespaces for the first install, before the Symfony container
 * has been recompiled with ModulesDoctrineCompilerPass.
 * After install, the container recompilation picks up entities automatically.
 */
private function prepareEntityManager(): void
```

- [ ] **Step 3: Consider migrating from Doctrine annotations to PHP 8 attributes**

PS9 still supports annotations but they're deprecated in Doctrine ORM 3.0. Migrate entity definitions:

```php
// Before (annotations):
/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class MyparcelnlCarrierMapping extends AbstractEntity

/**
 * @var int
 * @ORM\Id
 * @ORM\Column(name="carrier_id", type="integer", nullable=false, unique=true)
 */
private $carrierId;

// After (PHP 8 attributes):
#[ORM\Table]
#[ORM\Entity]
class MyparcelnlCarrierMapping extends AbstractEntity

#[ORM\Id]
#[ORM\Column(name: 'carrier_id', type: 'integer', nullable: false, unique: true)]
private int $carrierId;
```

Apply to all entity files in `src/Entity/`.

- [ ] **Step 4: Update PsPreInstallService to use AttributeDriver**

In `src/Pdk/Installer/Service/PsPreInstallService.php`, replace `AnnotationDriver` with `AttributeDriver`:

```php
use Doctrine\ORM\Mapping\Driver\AttributeDriver;

private function prepareEntityManager(): void
{
    $appInfo = Pdk::getAppInfo();

    /** @var EntityManagerInterface $entityManager */
    $entityManager = Pdk::get('ps.entityManager');

    $driverChain = $entityManager
        ->getConfiguration()
        ->getMetadataDriverImpl();

    $driver = new AttributeDriver(["{$appInfo->path}src/Entity"]);

    if ($driverChain instanceof MappingDriverChain) {
        $driverChain->addDriver($driver, 'MyParcelNL\PrestaShop\Entity');
    }
}
```

Remove `AnnotationReader`, `DocParser`, `PsrCachedReader`, `ArrayAdapter` imports.

- [ ] **Step 5: Test module install on clean database**

Run (inside container):
```bash
php bin/console prestashop:module uninstall myparcelnl
rm -rf var/cache/*
php bin/console prestashop:module install myparcelnl
```

- [ ] **Step 6: Commit**

```bash
git add src/Entity/ src/Repository/ src/Pdk/Installer/
git commit -m "refactor: migrate entities to PHP 8 attributes, use AttributeDriver"
```

---

## Phase 7: PHP 8.1+ Language Modernization

### Task 10: Adopt PHP 8.1+ features across the codebase

**Files:**
- Multiple files across `src/`

- [ ] **Step 1: Convert enums where applicable**

Identify string/int constants that represent a fixed set of values. Example candidates:
- Database table names in `src/Database/Table.php`
- Hook event types
- Package types if defined as constants

```php
// Before:
class Table {
    public const TABLE_CARRIER_MAPPING = 'myparcelnl_carrier_mapping';
    public const TABLE_CART_DELIVERY_OPTIONS = 'myparcelnl_cart_delivery_options';
}

// After:
enum Table: string {
    case CarrierMapping = 'myparcelnl_carrier_mapping';
    case CartDeliveryOptions = 'myparcelnl_cart_delivery_options';
}
```

Note: Only convert if the values are used as enums (switch/match, comparison). Don't convert constants used in string interpolation — enums require `.value` access.

- [ ] **Step 2: Use readonly properties**

For value objects and models with immutable data:

```php
// Before:
private string $name;
public function getName(): string { return $this->name; }

// After:
public function __construct(
    public readonly string $name,
) {}
```

- [ ] **Step 3: Use match expressions**

Replace switch statements in `PsViewService` and similar:

```php
// Before (in PDK, not our code — but for our own switch/if chains):
if ($page === 'order') return true;
if ($page === 'AdminOrders') return true;

// After:
return match ($page) {
    'order' => true,
    'AdminOrders' => true,
    default => false,
};
```

- [ ] **Step 4: Use named arguments where it improves readability**

```php
// Before:
$controller->addCSS($themeCss, 'all', 1);

// After:
$controller->addCSS($themeCss, media: 'all', priority: 1);
```

- [ ] **Step 5: Run full test suite**

Run: `composer test`
Expected: All tests pass.

- [ ] **Step 6: Commit**

```bash
git add src/
git commit -m "refactor: adopt PHP 8.1+ enums, readonly properties, match expressions"
```

---

## Phase Summary

| Phase | Focus | Risk | Priority |
|-------|-------|------|----------|
| 1 | Drop PHP 7.4 compat | Low | High — unblocks all other phases |
| 2 | PS9 controller architecture | Low | High — FrameworkBundleAdminController removed in PS10 |
| 3 | Native product page integration | Medium | High — removes HTMLPurifier workaround |
| 4 | Modern context & services | Low | Medium — legacy Context still works in PS9 |
| 5 | Script modernization | Low | Medium — cosmetic, improves type safety |
| 6 | Entity & repository modernization | Medium | Medium — annotations deprecated in Doctrine ORM 3.0 |
| 7 | PHP 8.1+ language features | Low | Low — code quality, no behavioral change |

Phases 1-3 should be completed before PS10 release. Phases 4-7 can be done incrementally.

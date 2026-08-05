<?php

/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Cart\Repository;

use Address;
use Cart;
use Country;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

// The repository resolves Country via the PrestaShop adapter namespace, which the mock test env
// does not provide; in real PrestaShop it is the legacy global Country. Alias it so get() can run.
if (! class_exists('PrestaShop\\PrestaShop\\Adapter\\Entity\\Country', false)) {
    class_alias(\Country::class, 'PrestaShop\\PrestaShop\\Adapter\\Entity\\Country');
}

usesShared(new UsesMockPsPdkInstance());

it('maps the delivery address company to the pdk cart as isBusiness, without storing the company', function (
    ?string $company,
    bool    $expected
) {
    $addressFactory = psFactory(Address::class)->withIdCountry(Country::getByIso('NL'));

    if (null !== $company) {
        $addressFactory = $addressFactory->withCompany($company);
    }

    $address = $addressFactory->store();

    $cart              = psFactory(Cart::class)->withAddressDelivery($address->id)->make();
    $cart->id          = $address->id;
    // getProducts() reads the 'products' attribute (BaseMock::__call); the repository maps over it.
    $cart->products    = [];

    /** @var PdkCartRepositoryInterface $repository */
    $repository = Pdk::get(PdkCartRepositoryInterface::class);

    $shippingAddress = $repository->get($cart)->shippingMethod->shippingAddress;

    expect($shippingAddress->isBusiness)->toBe($expected)
        // Company is used only to derive the flag; it must never land on the PII-free cart address.
        ->and($shippingAddress->toArray())->not->toHaveKey('company');
})->with([
    'business (company entered)' => ['Acme B.V.', true],
    'consumer (no company)'      => [null, false],
]);

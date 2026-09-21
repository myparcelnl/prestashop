<?php

/** @noinspection AutoloadingIssuesInspection,PhpIllegalPsrClassPathInspection,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Cart\Repository;

use Address;
use Cart;
use Country;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Product;
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

it('uses the resolved cart line weight without changing the base product', function (
    array $line,
    string $unit,
    int $expectedWeight
) {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', $unit);
    $product = psFactory(Product::class)->withWeight(21)->withActive(true)->withAvailableForOrder(true)->store();
    $address = psFactory(Address::class)->store();
    $cart    = psFactory(Cart::class)->withAddressDelivery($address->id)->make();
    $cart->id = 91234;
    $cart->products = [$line + ['id_product' => $product->id, 'cart_quantity' => 1, 'price' => 100]];

    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $baseProduct       = $productRepository->getProduct($product->id);
    $baseWeight        = $baseProduct->weight;
    $pdkCart           = Pdk::get(PdkCartRepositoryInterface::class)->get($cart);

    expect($pdkCart->lines->getTotalWeight())->toBe($expectedWeight)
        ->and($baseProduct->weight)->toBe($baseWeight)
        ->and($productRepository->getProduct($product->id)->weight)->toBe($baseWeight);
})->with([
    'lighter combination'             => [['id_product_attribute' => 42, 'weight' => '19'], 'kg', 19000],
    'heavier combination'             => [['id_product_attribute' => 42, 'weight' => '22'], 'kg', 22000],
    // PrestaShop adds the combination's impact to the base weight before returning these fields.
    'zero combination weight impact'  => [['id_product_attribute' => 42, 'weight_attribute' => '21', 'weight' => '21'], 'kg', 21000],
    'customization included once'     => [['id_customization' => 7, 'weight' => '21.001'], 'kg', 21001],
    'quantity included once'          => [['weight' => '10', 'cart_quantity' => 3], 'kg', 30000],
    'explicit one gram'               => [['weight' => '1'], 'g', 1],
    'zero stays unknown'              => [['weight' => '0'], 'kg', 0],
    'null stays unknown'              => [['weight' => null], 'kg', 0],
    'malformed stays unknown'         => [['weight' => 'unknown'], 'kg', 0],
    'negative stays unknown'          => [['weight' => -1], 'kg', 0],
    'missing combination weight'      => [['id_product_attribute' => 42], 'kg', 0],
    'missing customization weight'    => [['id_customization' => 7], 'kg', 0],
    'simple product legacy fallback'  => [[], 'kg', 21000],
]);

it('keeps two combinations of the same product independent', function () {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', 'kg');
    $product = psFactory(Product::class)->withWeight(21)->withActive(true)->withAvailableForOrder(true)->store();
    $address = psFactory(Address::class)->store();
    $cart    = psFactory(Cart::class)->withAddressDelivery($address->id)->make();
    $cart->id = 91235;
    $cart->products = [
        ['id_product' => $product->id, 'id_product_attribute' => 41, 'weight' => 19, 'cart_quantity' => 2, 'price' => 100],
        ['id_product' => $product->id, 'id_product_attribute' => 42, 'weight' => 22, 'cart_quantity' => 1, 'price' => 100],
    ];

    $pdkCart = Pdk::get(PdkCartRepositoryInterface::class)->get($cart);

    expect($pdkCart->lines->pluck('product.weight')->all())->toBe([19000, 22000])
        ->and($pdkCart->lines->getTotalWeight())->toBe(60000)
        ->and(Pdk::get(PdkProductRepositoryInterface::class)->getProduct($product->id)->weight)->toBe(21000);
});

it('keeps two customizations of the same combination independent', function () {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', 'kg');
    $product = psFactory(Product::class)->withWeight(21)->withActive(true)->withAvailableForOrder(true)->store();
    $address = psFactory(Address::class)->store();
    $cart    = psFactory(Cart::class)->withAddressDelivery($address->id)->make();
    $cart->id = 91236;
    // Both weights are per item and already include the combination and customization impacts.
    $cart->products = [
        [
            'id_product'           => $product->id,
            'id_product_attribute' => 42,
            'id_customization'     => 7,
            'weight_attribute'     => '19.001',
            'weight'               => '19.001',
            'cart_quantity'        => 2,
            'price'                => 100,
        ],
        [
            'id_product'           => $product->id,
            'id_product_attribute' => 42,
            'id_customization'     => 8,
            'weight_attribute'     => '20.002',
            'weight'               => '20.002',
            'cart_quantity'        => 1,
            'price'                => 100,
        ],
    ];

    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);
    $baseProduct       = $productRepository->getProduct($product->id);
    $pdkCart           = Pdk::get(PdkCartRepositoryInterface::class)->get($cart);

    expect($pdkCart->lines->pluck('product.weight')->all())->toBe([19001, 20002])
        ->and($pdkCart->lines->getTotalWeight())->toBe(58004)
        ->and($baseProduct->weight)->toBe(21000)
        ->and($productRepository->getProduct($product->id)->weight)->toBe(21000);
});

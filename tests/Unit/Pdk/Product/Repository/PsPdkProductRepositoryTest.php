<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Product\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Product;
use ProductFactory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPsPdkInstance());

it('creates a valid pdk product', function (ProductFactory $productFactory) {
    /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository */
    $productRepository = Pdk::get(PdkProductRepositoryInterface::class);

    /** @var Product $psProduct */
    $psProduct = $productFactory->store();

    $pdkProduct = $productRepository->getProduct($psProduct->id);

    assertMatchesJsonSnapshot(json_encode($pdkProduct->toArrayWithoutNull(), JSON_THROW_ON_ERROR));
})->with([
    'simple product'                => function () {
        return psFactory(Product::class)
            ->withPrice(1000)
            ->withWeight(1);
    },
    'product with weight as string' => function () {
        return psFactory(Product::class)->withWeight('234');
    },
    'unavailable virtual product'   => function () {
        return psFactory(Product::class)
            ->withIsAvailableForOrder(false)
            ->withIsVirtual(true);
    },
]);

<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('converts a weight without a unit with the shop weight unit', function (
    string $shopUnit,
    float  $weight,
    int    $expected
) {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', $shopUnit);

    /** @var \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService $service */
    $service = Pdk::get(PsWeightService::class);

    expect($service->convertToGrams($weight))->toBe($expected);
})->with([
    'kg shop'  => ['kg', 1.5, 1500],
    'g shop'   => ['g', 1500, 1500],
    'lbs shop' => ['lbs', 1, 454],
]);

it('converts a weight with an explicit unit regardless of the shop weight unit', function (
    string $shopUnit,
    float  $weight,
    string $unit,
    int    $expected
) {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', $shopUnit);

    /** @var \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService $service */
    $service = Pdk::get(PsWeightService::class);

    expect($service->convertToGrams($weight, $unit))->toBe($expected);
})->with([
    'grams in a kg shop'     => ['kg', 1, 'g', 1],
    'grams max in a kg shop' => ['kg', 31500, 'g', 31500],
    'kilograms in a g shop'  => ['g', 30, 'kg', 30000],
    'grams in a g shop'      => ['g', 1, 'g', 1],
]);

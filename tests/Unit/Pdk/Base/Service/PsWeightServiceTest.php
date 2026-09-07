<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use MyParcelNL\PrestaShop\Tests\Mock\MockPsConfiguration;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('uses the PrestaShop weight unit when no explicit unit is supplied', function () {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', 'kg');

    expect((new PsWeightService())->convertToGrams(1))->toBe(1000);
});

it('uses an explicit API unit instead of the PrestaShop weight unit', function () {
    MockPsConfiguration::set('PS_WEIGHT_UNIT', 'g');

    expect((new PsWeightService())->convertToGrams(31.5, 'kg'))->toBe(31500);
});

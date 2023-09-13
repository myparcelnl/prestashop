<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPlugin;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPlugin());

it('boots plugin', function () {
    expect(true)->toBeTrue();
});

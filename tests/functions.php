<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Bootstrap\MockPsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Factory\PsFactoryFactory;

/**
 * @return void
 * @throws \Exception
 */
function bootPdk(): void
{
    if (! defined('PEST')) {
        PsPdkBootstrapper::boot(...func_get_args());

        return;
    }

    MockPsPdkBootstrapper::boot(...func_get_args());
}

/**
 * @param  class-string<\ObjectModel> $class
 * @param  mixed                      ...$args
 *
 * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
 */
function psFactory(string $class, ...$args)
{
    return PsFactoryFactory::create($class, ...$args);
}

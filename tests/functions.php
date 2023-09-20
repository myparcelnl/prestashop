<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop;

use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;
use MyParcelNL\PrestaShop\Tests\Factory\PsFactoryFactory;
use ObjectModel;

/**
 * @param  class-string<ObjectModel|EntityInterface> $class
 * @param  mixed                                     ...$args
 *
 * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
 */
function psFactory(string $class, ...$args)
{
    return PsFactoryFactory::create($class, ...$args);
}

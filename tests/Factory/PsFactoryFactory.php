<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException;
use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;
use ObjectModel;
use Throwable;

final class PsFactoryFactory
{
    /**
     * @param  class-string<ObjectModel|EntityInterface> $class
     * @param  mixed                                     ...$args
     *
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public static function create(string $class, ...$args): FactoryInterface
    {
        $factory = "{$class}Factory";

        try {
            return new $factory(...$args);
        } catch (Throwable $e) {
            throw new InvalidFactoryException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

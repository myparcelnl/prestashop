<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsEntityFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @template T of \MyParcelNL\PrestaShop\Entity\EntityInterface
 * @implements PsObjectModelFactoryInterface<T>
 * @extends AbstractPsFactory<T>
 * @method $this withCreated(string $created)
 * @method $this withUpdated(string $updated)
 */
abstract class AbstractPsEntityFactory extends AbstractPsModelFactory implements PsEntityFactoryInterface
{
    abstract protected function getEntityClass(): string;

    /**
     * @param  class-string<T> $class
     * @param  array           $attributes
     *
     * @return mixed
     */
    protected function createObject(string $class, array $attributes)
    {
        $instance = new $class();

        foreach ($attributes as $key => $value) {
            $setter = sprintf('set%s', Str::studly($key));
            $instance->{$setter}($value);
        }

        return $instance;
    }

    protected function getClass(): string
    {
        return $this->getEntityClass();
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save($model): void
    {
        MockPsEntities::update($model);
    }
}

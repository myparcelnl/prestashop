<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsEntityFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use MyParcelNL\Sdk\src\Support\Str;

/**
 * @template T of \MyParcelNL\PrestaShop\Entity\Contract\EntityInterface
 * @implements PsObjectModelFactoryInterface<T>
 * @extends AbstractPsFactory<T>
 * @method self withCreated(string $created)
 * @method self withUpdated(string $updated)
 */
abstract class AbstractPsEntityFactory extends AbstractPsModelFactory implements PsEntityFactoryInterface
{
    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withCreated('2020-01-01 08:00:00')
            ->withUpdated('2020-01-02 12:00:00');
    }

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
            $instance->{Str::camel($key)} = $value;
        }

        return $instance;
    }

    protected function getClass(): string
    {
        return $this->getEntityClass();
    }

    abstract protected function getEntityClass(): string;

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

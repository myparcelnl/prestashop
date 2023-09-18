<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;
use ObjectModel;

/**
 * @template T of ObjectModel
 * @implements PsObjectModelFactoryInterface<T>
 * @extends AbstractPsFactory<T>
 */
abstract class AbstractPsObjectModelFactory extends AbstractPsModelFactory implements PsObjectModelFactoryInterface
{
    /**
     * @param  int $id
     *
     * @return $this
     */
    public function withId(int $id): self
    {
        return $this->with(['id' => $id]);
    }

    /**
     * @return $this
     */
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    /**
     * @param  string $class
     * @param  array  $attributes
     *
     * @return T
     */
    protected function createObject(string $class, array $attributes): ObjectModel
    {
        /** @var T $created */
        $created = new $class();
        $created->hydrate(
            array_map(static function ($value) {
                return $value instanceof FactoryInterface ? $value->make() : $value;
            }, $attributes)
        );

        return $created;
    }

    /**
     * @return string
     */
    protected function getClass(): string
    {
        return $this->getObjectModelClass();
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getObjectModelClass(): string;

    /**
     * @return array
     */
    protected function resolveAttributes(): array
    {
        return array_replace(parent::resolveAttributes(), ['id' => $this->getId()]);
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save($model): void
    {
        MockPsObjectModels::update($model);
    }

    /**
     * @param  string                                    $key
     * @param  ObjectModel|PsObjectModelFactoryInterface $input
     *
     * @return $this
     */
    protected function withModel(string $key, $input): self
    {
        if ($input instanceof PsFactoryInterface) {
            return $this->withModel($key, $input->make());
        }

        $idKey = sprintf('id_%s', $key);

        return $this->with([$idKey => $input]);
    }
}

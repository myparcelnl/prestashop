<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;
use ObjectModel;

/**
 * @template T of ObjectModel
 * @implements PsObjectModelFactoryInterface<T>
 */
abstract class AbstractPsObjectModelFactory extends AbstractPsFactory implements PsObjectModelFactoryInterface
{
    /**
     * @var array<string, T>
     */
    private $cache = [];

    /**
     * @return T
     */
    public function make(): ObjectModel
    {
        $class      = $this->getObjectModelClass();
        $attributes = $this->resolveAttributes();

        $cacheKey = sprintf('%s::%s', $class, md5(json_encode($attributes)));

        if (! isset($this->cache[$cacheKey])) {
            /** @var T $created */
            $created = new $class();
            $created->hydrate(
                array_map(static function ($value) {
                    return $value instanceof FactoryInterface ? $value->make() : $value;
                }, $attributes)
            );

            $this->cache[$cacheKey] = $created;
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return T
     */
    public function store(): ObjectModel
    {
        $model = $this->make();

        MockPsObjectModels::update($model);

        return $model;
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    /**
     * @return int
     */
    final protected function getId(): int
    {
        return $this->attributes->get('id') ?? $this->getNextId();
    }

    /**
     * @param  string|null $key
     *
     * @return int
     */
    protected function getNextId(string $key = null): int
    {
        $key = $key ?? $this->getObjectModelClass();

        return $this->state->getNextId($key);
    }

    abstract protected function getObjectModelClass(): string;

    /**
     * @return array
     */
    protected function resolveAttributes(): array
    {
        return array_replace(parent::resolveAttributes(), ['id' => $this->getId()]);
    }

    /**
     * @param  int $id
     *
     * @return self
     */
    protected function withId(int $id): self
    {
        return $this->with(['id' => $id]);
    }

    /**
     * @param  string                                    $key
     * @param  ObjectModel|PsObjectModelFactoryInterface $input
     *
     * @return self
     */
    protected function withModel(string $key, $input): self
    {
        if ($input instanceof PsObjectModelFactoryInterface) {
            return $this->withModel($key, $input->make());
        }

        $idKey = sprintf('id_%s', $key);

        return $this->with([$idKey => $input]);
    }
}

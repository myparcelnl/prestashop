<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsClassFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsEntities;
use ObjectModel;

/**
 * @template T of ObjectModel
 * @implements PsClassFactoryInterface<T>
 */
abstract class AbstractPsClassFactory extends AbstractPsFactory implements PsClassFactoryInterface
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
        $class      = $this->getEntityClass();
        $attributes = $this->resolveAttributes();

        $cacheKey = sprintf('%s::%s', $class, md5(json_encode($attributes)));

        if (! isset($this->cache[$cacheKey])) {
            /** @var ObjectModel $created */
            $created = new $class();
            $created->hydrate($attributes);

            $this->cache[$cacheKey] = $created;
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return T
     */
    public function store(): ObjectModel
    {
        return MockPsEntities::save($this->make());
    }

    abstract protected function getEntityClass(): string;

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
        $key = $key ?? $this->getEntityClass();

        return $this->state->getNextId($key);
    }

    /**
     * @return array
     */
    protected function resolveAttributes(): array
    {
        return array_replace(parent::resolveAttributes(), ['id' => $this->getId()]);
    }

    /**
     * @param  string                              $key
     * @param  ObjectModel|PsClassFactoryInterface $input
     *
     * @return self
     */
    protected function withModel(string $key, $input): self
    {
        if ($input instanceof FactoryInterface) {
            return $this->withModel($key, $input->make());
        }

        $idKey = sprintf('id_%s', $key);

        return $this->with([$idKey => $input]);
    }
}

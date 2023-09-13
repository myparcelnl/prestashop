<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use Props\BadMethodCallException;

/**
 * @template T
 */
abstract class AbstractPsModelFactory extends AbstractPsFactory
{
    /**
     * @var array<string, T>
     */
    private $cache = [];

    /**
     * @return T
     */
    public function make()
    {
        $class      = $this->getClass();
        $attributes = $this->resolveAttributes();

        $cacheKey = sprintf('%s::%s', $class, md5(json_encode($attributes)));

        if (! isset($this->cache[$cacheKey])) {
            $created = $this->createObject($class, $attributes);

            $this->cache[$cacheKey] = $created;
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return T
     * @throws \Exception
     */
    public function store()
    {
        $model = $this->make();

        $this->save($model);

        return $model;
    }

    /**
     * @param  string $class
     * @param  array  $attributes
     *
     * @return T
     */
    abstract protected function createObject(string $class, array $attributes);

    /**
     * @return class-string<T>
     */
    abstract protected function getClass(): string;

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
        $key = $key ?? $this->getClass();

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
     * @param  T $model
     *
     * @return void
     * @throws \Exception
     */
    protected function save($model): void
    {
        throw new BadMethodCallException('Not implemented');
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
}

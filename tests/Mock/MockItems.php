<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\Sdk\Support\Str;

abstract class MockItems implements StaticMockInterface
{
    /**
     * @var Collection<Collection>
     */
    private static $items;

    /**
     * @param  object $object
     *
     * @return bool
     */
    public static function add(object $object): bool
    {
        $byClass = self::getByClass(get_class($object));

        if (isset($object->id) && $byClass->has($object->id)) {
            return false;
        }

        $object->id = self::getOrCreateId($object);

        static::update($object);

        return true;
    }

    /**
     * @param  object $entity
     *
     * @return void
     */
    public static function addOrUpdate(object $entity): void
    {
        static::add($entity) || static::update($entity);
    }

    /**
     * @return Collection<Collection>
     */
    public static function all(): Collection
    {
        if (! isset(static::$items)) {
            static::reset();
        }

        return static::$items;
    }

    /**
     * @param  int $id
     *
     * @return void
     */
    public static function delete(int $id): void
    {
        static::all()
            ->forget($id);
    }

    /**
     * @param  string $class
     * @param  int    $id
     *
     * @return null|object
     */
    public static function get(string $class, int $id): ?object
    {
        return static::getByClass($class)
            ->first(function (object $item) use ($id) {
                return $item->id === $id;
            });
    }

    /**
     * @param  class-string $class
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public static function getByClass(string $class): Collection
    {
        return static::all()
            ->get(Str::lower($class), new Collection());
    }

    public static function reset(): void
    {
        static::$items = new Collection();
    }

    /**
     * @param  object $entity
     *
     * @return bool
     */
    public static function update(object $entity): bool
    {
        $class = Str::lower(get_class($entity));
        $all   = static::all();

        if (! $all->has($class)) {
            $all->put($class, new Collection());
        }

        $all->get($class)
            ->put(self::getOrCreateId($entity), $entity);

        return true;
    }

    /**
     * @param  object $object
     *
     * @return int
     */
    protected static function getOrCreateId(object $object): int
    {
        if (isset($object->id)) {
            return $object->id;
        }

        $byClass = self::getByClass(get_class($object));

        return $byClass->count() + 1;
    }
}

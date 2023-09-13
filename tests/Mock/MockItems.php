<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

abstract class MockItems implements StaticMockInterface
{
    /**
     * @var Collection
     */
    private static $items;

    /**
     * @param  object $object
     *
     * @return bool
     */
    public static function add(object $object): bool
    {
        $all = static::all();

        if ($all->has($object->id)) {
            return false;
        }

        $object->id = self::getId($object);

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
     * @return \MyParcelNL\Pdk\Base\Support\Collection
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
     * @param  int $id
     *
     * @return null|object
     */
    public static function get(int $id): ?object
    {
        return static::all()
            ->get($id);
    }

    /**
     * @param  class-string $class
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public static function getByClass(string $class): Collection
    {
        return static::all()
            ->whereInstanceOf($class)
            ->values();
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
        static::all()
            ->put($entity->id, $entity);

        return true;
    }

    /**
     * @param  object $object
     *
     * @return int
     */
    protected static function getId(object $object): int
    {
        if (isset($object->id)) {
            return $object->id;
        }

        $count = self::all()
            ->count();

        return $count + 1;
    }
}

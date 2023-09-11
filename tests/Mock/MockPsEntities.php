<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use ObjectModelCore;

final class MockPsEntities implements StaticMockInterface
{
    private static $entities = [];

    /**
     * @param  int $id
     *
     * @return void
     */
    public static function delete(int $id): void
    {
        unset(self::$entities[$id]);
    }

    /**
     * @param  int $id
     *
     * @return null|\ObjectModelCore
     */
    public static function get(int $id): ?ObjectModelCore
    {
        return self::$entities[$id] ?? null;
    }

    /**
     * @param  class-string<ObjectModelCore> $class
     *
     * @return array
     */
    public static function getByClass(string $class): array
    {
        return Arr::where(self::$entities, static function (ObjectModelCore $entity) use ($class) {
            return $entity instanceof $class;
        });
    }

    public static function reset(): void
    {
        self::$entities = [];
    }

    /**
     * @template T of ObjectModelCore
     * @param  T $entity
     *
     * @return T
     */
    public static function save(ObjectModelCore $entity): ObjectModelCore
    {
        self::$entities[$entity->id] = $entity;

        return $entity;
    }
}

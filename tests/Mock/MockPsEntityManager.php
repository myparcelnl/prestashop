<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use ObjectModel;

final class MockPsEntityManager implements StaticMockInterface
{
    private static $repositories = [];

    public static function reset(): void
    {
        self::$repositories = [];
    }

    public function flush(): void {}

    /**
     * @param  class-string<ObjectModel> $entityName
     *
     * @return \MyParcelNL\PrestaShop\Tests\Mock\MockPsEntityRepository
     */
    public function getRepository(string $entityName): MockPsEntityRepository
    {
        if (! isset(self::$repositories[$entityName])) {
            self::$repositories[$entityName] = new MockPsEntityRepository($entityName);
        }

        return self::$repositories[$entityName];
    }

    public function persist($entity): void
    {
        MockPsEntities::addOrUpdate($entity);
    }
}

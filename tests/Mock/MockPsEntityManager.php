<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use ObjectModel;

final class MockPsEntityManager extends BaseMock implements StaticMockInterface
{
    private static $repositories = [];

    public static function reset(): void
    {
        self::$repositories = [];
    }

    public function flush(): void { }

    public function getConfiguration()
    {
        return new BaseMock();
    }

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

    public function isOpen(): bool
    {
        return true;
    }

    public function persist($entity): void
    {
        MockPsEntities::addOrUpdate($entity);
    }

    /**
     * Doctrine clears its identity map after a batch flush. The in-memory MockPsEntities
     * store has no identity map to clear, so this is a no-op kept for interface parity
     * with the migration batching code.
     */
    public function clear(): void
    {
    }
}

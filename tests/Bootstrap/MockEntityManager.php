<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

class MockEntityManager extends EntityManager
{
    /**
     * @param  object $entity
     *
     * @return void
     */
    public function flush($entity = null): void
    {
        // do nothing
    }

    /**
     * @param  string $entityName
     *
     * @return \MyParcelNL\PrestaShop\Tests\Bootstrap\MockEntityRepository
     */
    public function getRepository($entityName): MockEntityRepository
    {
        return new MockEntityRepository($this, new ClassMetadata($entityName));
    }

    /**
     * @param  object $entity
     *
     * @return void
     */
    public function persist($entity = null): void
    {
        // do nothing

    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Sdk\src\Support\Arr;
use ObjectModel;

final class MockPsRepository
{
    private $entities = [];

    /**
     * @param  mixed    $id
     * @param  int|null $lockMode
     * @param  int|null $lockVersion
     *
     * @return null|object
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        return $this->entities[$id] ?? null;
    }

    /**
     * @return array<object>
     */
    public function findAll(): array
    {
        return $this->entities;
    }

    /**
     * @param  array      $criteria
     * @param  array|null $orderBy
     * @param             $limit
     * @param             $offset
     *
     * @return array<object>
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): array
    {
        return Arr::where($this->entities, static function (ObjectModel $entity) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if ($entity->{$key} === $value) {
                    continue;
                }

                return false;
            }

            return true;
        });
    }

    /**
     * @param  array      $criteria
     * @param  array|null $orderBy
     *
     * @return null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        return Arr::first($this->findBy($criteria, $orderBy));
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;

/**
 * @template T of EntityInterface
 */
final class MockPsEntityRepository
{
    /**
     * @var class-string<T>
     */
    private $entity;

    /**
     * @param  class-string<T> $entity
     */
    public function __construct(string $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param  mixed    $id
     * @param  int|null $lockMode
     * @param  int|null $lockVersion
     *
     * @return null|object
     */
    public function find($id, $lockMode = null, $lockVersion = null): ?object
    {
        $entry = MockPsEntities::get($id);

        if (! $entry instanceof $this->entity) {
            return null;
        }

        return $entry;
    }

    /**
     * @return array<object>
     */
    public function findAll(): array
    {
        return MockPsEntities::getByClass($this->entity)
            ->all();
    }

    /**
     * @param  array      $criteria
     * @param  array|null $orderBy
     * @param  null       $limit
     * @param  null       $offset
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection<T>
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): Collection
    {
        return MockPsEntities::getByClass($this->entity)
            ->filter(
                static function (object $object) use ($criteria) {
                    foreach ($criteria as $key => $value) {
                        if ($object->{$key} === $value) {
                            continue;
                        }

                        return false;
                    }

                    return true;
                }
            );
    }

    /**
     * @param  array      $criteria
     * @param  array|null $orderBy
     *
     * @return null|object
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        return $this->findBy($criteria, $orderBy)
            ->first();
    }

    public function persist(): void
    {
        MockPsEntities::add($this->entity);
    }
}

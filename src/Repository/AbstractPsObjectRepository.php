<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use Doctrine\ORM\EntityNotFoundException;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Contract\PsObjectRepositoryInterface;
use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

/**
 * @template T of \MyParcelNL\PrestaShop\Entity\Contract\EntityInterface
 */
abstract class AbstractPsObjectRepository implements PsObjectRepositoryInterface
{
    /**
     * @var class-string<T>
     */
    protected $entity;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $entityRepository;

    public function __construct()
    {
        $this->entityManager    = Pdk::get('ps.entityManager');
        $this->entityRepository = $this->entityManager->getRepository($this->entity);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function all(): Collection
    {
        return new Collection(array_values($this->entityRepository->findAll()));
    }

    /**
     * @param  array $values
     *
     * @return null|T
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(array $values)
    {
        return $this->updateOrCreate([], $values);
    }

    /**
     * @return T
     */
    public function createEntity(): EntityInterface
    {
        return new $this->entity();
    }

    /**
     * @param  \MyParcelNL\PrestaShop\Entity\Contract\EntityInterface $entity
     *
     * @return void
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function delete(EntityInterface $entity): void
    {
        $this->entityManager->remove($entity);
    }

    /**
     * @param  int $id
     *
     * @return null|T
     */
    public function find(int $id): EntityInterface
    {
        return $this->entityRepository->find($id);
    }

    /**
     * @param  array $criteria
     *
     * @return null|T
     */
    public function findOneBy(array $criteria)
    {
        return $this->entityRepository->findOneBy($criteria);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return null|T
     */
    public function firstWhere(string $key, $value)
    {
        return $this->entityRepository->findOneBy([$key => $value]);
    }

    /**
     * @param  array $values
     * @param  array $where
     *
     * @return null|T
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(array $values, array $where): ?EntityInterface
    {
        $entity = $this->entityRepository->findOneBy($where);

        if (! $entity) {
            throw new EntityNotFoundException('Entity not found');
        }

        return $this->updateOrCreate($where, $values);
    }

    /**
     * @param  array $where
     * @param  array $values
     *
     * @return T
     * @throws \Doctrine\ORM\ORMException
     */
    public function updateOrCreate(array $where, array $values)
    {
        try {
            $entity = empty($where) ? null : $this->entityRepository->findOneBy($where);
        } catch (Throwable $e) {
            $entity = null;
        }

        if (! $entity) {
            $entity = $this->createEntity();
        }

        foreach (array_replace($where, $values) as $key => $value) {
            $setter = sprintf('set%s', Str::studly($key));
            $entity->{$setter}($value);
        }

        if ($entity instanceof EntityWithTimestampsInterface) {
            $entity->updateTimestamps();
        }

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection<T>
     */
    public function where(string $key, $value): Collection
    {
        return new Collection($this->entityRepository->findBy([$key => $value]));
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return Str::snake(Utils::classBasename($this->entity));
    }
}

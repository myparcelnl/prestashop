<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping\Entity;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Sdk\src\Support\Collection;
use MyParcelNL\Sdk\src\Support\Str;
use Throwable;

/**
 * @template-covariant T of object
 */
abstract class AbstractPsObjectRepository extends Repository
{
    /**
     * @var \Doctrine\ORM\Mapping\Entity
     */
    protected $entity = Entity::class;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $entityRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);
        $this->entityManager    = Pdk::get('ps.entityManager');
        $this->entityRepository = $this->entityManager->getRepository($this->entity);

        // on shutdown
        register_shutdown_function(function () {
            try {
                $this->entityManager->flush();
            } catch (Throwable $e) {
                Logger::error($e);
            }
        });
    }

    /**
     * @return \MyParcelNL\Sdk\src\Support\Collection
     */
    public function all(): Collection
    {
        return new Collection($this->entityRepository->findAll());
    }

    /**
     * @param  array $values
     *
     * @return null|\Doctrine\ORM\Mapping\Entity|object
     * @throws \Doctrine\ORM\ORMException
     */
    public function create(array $values)
    {
        return $this->updateOrCreate([], $values);
    }

    /**
     * @return T
     */
    public function createEntity()
    {
        return new $this->entity();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function delete(Entity $entity): void
    {
        $this->entityManager->remove($entity);
    }

    /**
     * @param  mixed $id
     *
     * @return null|T
     */
    public function find($id): Entity
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
     * @param  null|\Doctrine\ORM\Mapping\Entity $entity
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(?Entity $entity = null): void
    {
        $this->entityManager->flush($entity);
    }

    /**
     * @param  array $values
     * @param  array $where
     *
     * @return null
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(array $values, array $where)
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
            $entity          = $this->createEntity();
            $entity->created = new DateTime();
        }

        $entity->updated = new DateTime();

        foreach (array_merge($where, $values) as $key => $value) {
            $entity->{Str::camel("set_$key")}($value);
        }

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return \MyParcelNL\Sdk\src\Support\Collection
     */
    public function where(string $key, $value): Collection
    {
        return new Collection($this->entityRepository->findBy([$key => $value]));
    }
}

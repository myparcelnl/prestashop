<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Country as T;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Contract\PsSpecificObjectModelServiceInterface;
use ObjectModel;

/**
 * @template T of ObjectModel
 * @extends \MyParcelNL\PrestaShop\Contract\PsSpecificObjectModelServiceInterface<T>
 */
abstract class PsSpecificObjectModelService implements PsSpecificObjectModelServiceInterface
{
    private PsObjectModelServiceInterface $psObjectModelService;

    /**
     * @param  \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface $psObjectModelService
     */
    public function __construct(PsObjectModelServiceInterface $psObjectModelService)
    {
        $this->psObjectModelService = $psObjectModelService;
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getClass(): string;

    /**
     * @param  T $model
     *
     * @return T
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function add(ObjectModel $model): bool
    {
        return $this->psObjectModelService->add($model);
    }

    /**
     * @param  null|int $id
     *
     * @return T
     */
    public function create(?int $id = null): ObjectModel
    {
        return $this->psObjectModelService->create($this->getClass(), $id);
    }

    /**
     * @param  int|T $input
     * @param  bool  $soft
     *
     * @return bool
     */
    public function delete($input, bool $soft = false): bool
    {
        return $this->psObjectModelService->delete($this->getClass(), $input, $soft);
    }

    public function deleteMany($input, bool $soft = false): bool
    {
        return $this->psObjectModelService->deleteMany($this->getClass(), $input, $soft);
    }

    /**
     * @param  int|T $input
     *
     * @return bool
     */
    public function exists($input): bool
    {
        return $this->psObjectModelService->exists($this->getClass(), $input);
    }

    /**
     * @param  int|T $input
     *
     * @return null|T
     */
    public function get($input): ?ObjectModel
    {
        return $this->psObjectModelService->get($this->getClass(), $input);
    }

    /**
     * @param  int|T $input
     *
     * @return null|int
     */
    public function getId($input): ?int
    {
        return $this->psObjectModelService->getId($this->getClass(), $input);
    }

    /**
     * @param  T $model
     *
     * @return void
     * @throws \PrestaShopException
     */
    public function save(ObjectModel $model): void
    {
        $action = $model->id ? 'Saved' : 'Updated';

        $model->save();

        Logger::debug(sprintf('%s %s with id %s', $action, $this->getClass(), $model->id));
    }

    /**
     * @param  T $model
     *
     * @return T
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function update(ObjectModel $model): bool
    {
        return $this->psObjectModelService->update($model);
    }

    /**
     * @param  T         $model
     * @param  null|bool $existing
     *
     * @return T
     */
    public function updateOrAdd(ObjectModel $model, ?bool $existing = null): ObjectModel
    {
        return $this->psObjectModelService->updateOrAdd($model, $existing);
    }
}

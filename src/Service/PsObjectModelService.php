<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use ObjectModel;
use PrestaShopCollection;
use RuntimeException;

final class PsObjectModelService implements PsObjectModelServiceInterface
{
    /**
     * @param  \ObjectModel $model
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function add(ObjectModel $model): bool
    {
        $class   = get_class($model);
        $success = $model->add();

        if ($success) {
            Logger::debug("Created $class with id $model->id");
        } else {
            Logger::error("Failed to create $class");
        }

        return (bool) $success;
    }

    public function create(string $class, $input = null): ObjectModel
    {
        return $input ? new $class($input) : new $class();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function delete(string $class, $input, bool $soft = false): bool
    {
        $id = $this->getId($class, $input);

        $modelText = "$class with id $id";

        if (! $this->exists($class, $input)) {
            Logger::error("Failed to delete $modelText: does not exist");
        }

        /** @var ObjectModel $model */
        $model = $this->get($class, $input);

        if ($soft ? $model->softDelete() : $model->delete()) {
            Logger::debug("Deleted $modelText");

            return true;
        }

        Logger::error("Failed to delete $modelText");

        return false;
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function deleteMany(string $class, $input, bool $soft = false): bool
    {
        if ($input instanceof PrestaShopCollection) {
            $array = $input->getResults();
        } else {
            $array = Arr::wrap($input instanceof Collection ? $input->all() : $input);
        }

        return array_reduce(
            $array,
            function (bool $success, $id) use ($class, $soft): bool {
                return $success && $this->delete($class, $id, $soft);
            },
            true
        );
    }

    public function exists(string $class, $input): bool
    {
        $model = $this->get($class, $input);

        return $model && $model->id;
    }

    public function get(string $class, $input): ?ObjectModel
    {
        if ($this->isModel($class, $input)) {
            return $input;
        }

        $id = $this->getId($class, $input);

        if ($id) {
            $model = new $class($id);

            return $this->exists($class, $model) ? $model : null;
        }

        return null;
    }

    public function getId(string $class, $input): ?int
    {
        if ($this->isModel($class, $input)) {
            return $input->id;
        }

        return $input ? (int) $input : null;
    }

    public function getWithFallback(string $class, $input): ObjectModel
    {
        return $this->get($class, $input) ?? $this->create($class);
    }

    /**
     * @param  \ObjectModel $model
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function update(ObjectModel $model): bool
    {
        $class   = get_class($model);
        $success = $model->update();

        if ($success) {
            Logger::debug("Updated $class with id $model->id");
        } else {
            Logger::error("Failed to update $class with id $model->id");
        }

        return (bool) $success;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function updateOrAdd(ObjectModel $model, ?bool $existing = null): ObjectModel
    {
        $exists = $existing ?? $this->exists(get_class($model), $model);
        $result = $exists ? $this->update($model) : $this->add($model);

        if (! $result) {
            throw new RuntimeException(sprintf('Could not %s %s', $exists ? 'update' : 'create', get_class($model)));
        }

        return $model;
    }

    /**
     * @param  string          $class
     * @param  int|ObjectModel $input
     *
     * @return bool
     */
    protected function isModel(string $class, $input): bool
    {
        return is_object($input) && get_class($input) === $class;
    }
}

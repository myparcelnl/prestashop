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
    public function create(string $class, $input = null): ObjectModel
    {
        return $input ? new $class($input) : new $class();
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function delete(string $class, $input, bool $soft = false): bool
    {
        $id = $this->getId($class, $input);

        $modelText = "$class with id $id";

        if (! $this->exists($class, $input)) {
            Logger::error("Failed to delete $modelText: does not exist");
        }

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
        return (bool) $this->get($class, $input)->id;
    }

    public function get(string $class, $input): ObjectModel
    {
        return $this->isModel($class, $input) ? $input : $this->create($class, $input);
    }

    public function getId(string $class, $input): ?int
    {
        if ($this->isModel($class, $input)) {
            return $input->id;
        }

        return $input ? (int) $input : null;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function updateOrAdd(ObjectModel $model, ?bool $existing = null): ObjectModel
    {
        $exists = $existing ?? (bool) $model->id;
        $result = $exists ? $model->update() : $model->add();

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

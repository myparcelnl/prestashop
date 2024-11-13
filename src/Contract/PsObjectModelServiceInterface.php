<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use ObjectModel;

interface PsObjectModelServiceInterface
{
    /**
     * @param  \ObjectModel $model
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function add(ObjectModel $model): bool;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     *
     * @return Model
     */
    public function create(string $class, $input = null): ObjectModel;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     * @param  bool                $soft
     *
     * @return bool
     */
    public function delete(string $class, $input, bool $soft = false): bool;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model>                                                            $class
     * @param  (int|Instance)[]|\MyParcelNL\Pdk\Base\Support\Collection|\PrestaShopCollection $input
     * @param  bool                                                                           $soft
     *
     * @return bool
     */
    public function deleteMany(string $class, $input, bool $soft = false): bool;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     *
     * @return bool
     */
    public function exists(string $class, $input): bool;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     *
     * @return null|Model
     */
    public function get(string $class, $input): ?ObjectModel;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     *
     * @return null|int
     */
    public function getId(string $class, $input): ?int;

    /**
     * @template Model of \ObjectModel
     * @template Instance of Model
     * @param  class-string<Model> $class
     * @param  int|Instance        $input
     *
     * @return Model
     */
    public function getWithFallback(string $class, $input): ObjectModel;

    /**
     * @param  \ObjectModel $model
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function update(ObjectModel $model): bool;

    /**
     * @template Model of \ObjectModel
     * @param  Model     $model
     * @param  null|bool $existing
     *
     * @return Model
     */
    public function updateOrAdd(ObjectModel $model, ?bool $existing = null): ObjectModel;
}

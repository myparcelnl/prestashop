<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use ObjectModel;

interface PsObjectModelServiceInterface
{
    /**
     * @template T of ObjectModel
     * @param  T|class-string<T> $class
     * @param  null|int|T        $input
     *
     * @return T
     */
    public function create(string $class, $input = null): ObjectModel;

    /**
     * @template T of ObjectModel
     * @param  T|class-string<T> $class
     * @param  int|T             $input
     * @param  bool              $soft
     *
     * @return bool
     */
    public function delete(string $class, $input, bool $soft = false): bool;

    /**
     * @template T of ObjectModel
     * @param  T|class-string<T>                                                              $class
     * @param  (int|T)[]|\MyParcelNL\Pdk\Base\Support\Collection<int|T>|\PrestaShopCollection $input
     * @param  bool                                                                           $soft
     *
     * @return bool
     */
    public function deleteMany(string $class, $input, bool $soft = false): bool;

    /**
     * @template T of ObjectModel
     * @param  T|class-string<T> $class
     * @param  int|T             $input
     *
     * @return bool
     */
    public function exists(string $class, $input): bool;

    /**
     * @template T of ObjectModel
     * @param  T|class-string<T> $class
     * @param  int|T             $input
     *
     * @return T
     */
    public function get(string $class, $input): ObjectModel;

    /**
     * @template T of ObjectModel
     * @param  string $class
     * @param  int|T  $input
     *
     * @return null|int
     */
    public function getId(string $class, $input): ?int;

    /**
     * @template T of ObjectModel
     * @param  \T        $model
     * @param  null|bool $existing
     *
     * @return \T
     */
    public function updateOrAdd(ObjectModel $model, ?bool $existing = null): ObjectModel;
}

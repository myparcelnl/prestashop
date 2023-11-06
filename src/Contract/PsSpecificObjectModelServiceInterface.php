<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use ObjectModel;

/**
 * @template T of ObjectModel
 */
interface PsSpecificObjectModelServiceInterface
{
    /**
     * @param  null|int $id
     *
     * @return T
     */
    public function create(?int $id = null): ObjectModel;

    /**
     * @param  int|T $input
     * @param  bool  $soft
     *
     * @return bool
     */
    public function delete($input, bool $soft = false): bool;

    /**
     * @param  (int|T)[]|\MyParcelNL\Pdk\Base\Support\Collection<int|T> $input
     * @param  bool                                                     $soft
     *
     * @return bool
     */
    public function deleteMany($input, bool $soft = false): bool;

    /**
     * @param  int|T $input
     *
     * @return bool
     */
    public function exists($input): bool;

    /**
     * @param  int|ObjectModel $input
     *
     * @return null|T
     */
    public function get($input): ?ObjectModel;

    /**
     * @param  int|T $input
     *
     * @return null|int
     */
    public function getId($input): ?int;
}

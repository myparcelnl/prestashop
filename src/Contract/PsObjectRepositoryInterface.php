<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use MyParcelNL\Pdk\Base\Support\Collection;

interface PsObjectRepositoryInterface
{
    /**
     * Fetch records by their identifier. Following Laravel's findMany() convention, an empty id set
     * returns an empty collection (never "all") — use all() to retrieve everything.
     *
     * @param  int[] $ids
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function findAll(array $ids = []): Collection;
}

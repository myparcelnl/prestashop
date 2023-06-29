<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Storage;

use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use PrestaShop\PrestaShop\Adapter\Entity\Cache;

final class PsCacheStorage implements StorageInterface
{
    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        Cache::clean($storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return mixed
     */
    public function get(string $storageKey)
    {
        return Cache::retrieve($storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return Cache::isStored($storageKey);
    }

    /**
     * @param  string $storageKey
     * @param         $item
     *
     * @return void
     */
    public function set(string $storageKey, $item): void
    {
        Cache::store($storageKey, $item);
    }
}

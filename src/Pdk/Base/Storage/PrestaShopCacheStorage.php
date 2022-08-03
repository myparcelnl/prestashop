<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Storage;

use MyParcelNL\Pdk\Storage\AbstractStorage;
use PrestaShop\PrestaShop\Adapter\Entity\Cache;

class PrestaShopCacheStorage extends AbstractStorage
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
     * @param         $item
     *
     * @return void
     */
    public function set(string $storageKey, $item): void
    {
        Cache::store($storageKey, $item);
    }
}

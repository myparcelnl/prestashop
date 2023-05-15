<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Cache as PsCache;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\Logger;

class Cache
{
    /**
     * @param  string $key
     *
     * @return void
     */
    public static function forget(string $key): void
    {
        PsCache::clean(self::getKey($key));
    }

    /**
     * @param  string   $key
     * @param  callable $callback
     *
     * @return mixed
     */
    public static function remember(string $key, callable $callback)
    {
        $key = self::getKey($key);

        if (PsCache::isStored($key)) {
            return PsCache::retrieve($key);
        }

        $newValue = $callback();
        PsCache::store($key, $newValue);

        return $newValue;
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    private static function getKey(string $key): string
    {
        return MyParcelNL::MODULE_NAME . '_' . $key;
    }
}

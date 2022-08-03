<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Entity;

use Cache as PsCache;
use MyParcelBE;

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
        return MyParcelBE::MODULE_NAME . '_' . $key;
    }
}

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE;

use Gett\MyparcelBE\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk;

class Boot
{
    /**
     * @return void
     * @throws \Throwable
     */
    public static function setupPdk(): Pdk
    {
        $config = include sprintf("%s/../config/pdk.php", __DIR__);

        return PdkFactory::create($config);
    }

    /**
     * @return bool
     */
    public static function useDevJs(): bool
    {
        return _PS_MODE_DEV_ && @curl_init('http://localhost:9420') !== false;
    }
}

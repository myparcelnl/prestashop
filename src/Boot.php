<?php

declare(strict_types=1);

namespace Gett\MyparcelBE;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk;

class Boot
{
    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var \MyParcelNL\Pdk\Base\Pdk
     */
    private static $pdk;

    /**
     * @return void
     * @throws \Throwable
     */
    public static function setupPdk(\MyParcelBE $module): ?Pdk
    {
        if (! self::$initialized) {
            self::$initialized = true;
            self::$pdk         = PdkFactory::create($module->getLocalPath() . 'config/pdk.php');
        }

        return self::$pdk;
    }

    /**
     * @return bool
     */
    public static function useDevJs(): bool
    {
        return _PS_MODE_DEV_ && @curl_init('http://localhost:9420') !== false;
    }
}

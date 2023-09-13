<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Facade;

use MyParcelNL;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\PrestaShop\Service\ModuleService;

/**
 * @method static bool install()
 * @method static MyParcelNL getInstance()
 * @method static void registerHooks()
 * @see \MyParcelNL\PrestaShop\Service\ModuleService
 */
final class MyParcelModule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModuleService::class;
    }
}

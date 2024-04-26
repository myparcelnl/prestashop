<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Facade;

use Module;
use MyParcelNL;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\PrestaShop\Service\ModuleService;

/**
 * @method static MyParcelNL getInstance()
 * @method static void registerHooks()
 * @method static bool install(Module $module)
 * @see \MyParcelNL\PrestaShop\Service\ModuleService
 */
final class MyParcelModule extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModuleService::class;
    }
}

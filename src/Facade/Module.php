<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\PrestaShop\Service\ModuleService;

/**
 * @method static bool install()
 * @see \MyParcelNL\PrestaShop\Service\ModuleService
 */
final class Module extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModuleService::class;
    }
}

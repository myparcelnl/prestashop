<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\PrestaShop\Service\PsEntityManagerService;

/**
 * @see PsEntityManagerService
 * @method static void flush()
 */
final class EntityManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PsEntityManagerService::class;
    }
}

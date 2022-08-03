<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static isNl(): bool
 * @method static isBe(): bool
 * @method static getContent(): string
 * @method static getHooks(): array
 * @method static getMigrations(): array
 * @method static getModuleCountry(): string
 * @method static getOrderShippingCost($cart, $shippingCost)
 * @implements \Gett\MyparcelBE\Module\Service\ModuleService
 */
class ModuleService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Gett\MyparcelBE\Module\Service\ModuleService::class;
    }
}

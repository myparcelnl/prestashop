<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Facade;

use MyParcelNL\Pdk\Base\Facade;

/**
 * @method static isNl(): bool
 * @method static isBe(): bool
 * @method static getContent(): string
 * @method static getHooks(): array
 * @method static getMigrations(): array
 * @method static getModuleCountry(): string
 * @method static getOrderShippingCost($cart, $shippingCost)
 * @implements \MyParcelNL\PrestaShop\Module\Service\ModuleService
 */
class ModuleService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MyParcelNL\PrestaShop\Module\Service\ModuleService::class;
    }
}

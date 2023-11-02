<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShop;

/**
 * @method $this withIdParent(int $idParent)
 * @method $this withActive(bool $active)
 * @method $this withClassName(string $className)
 * @method $this withModule(string $module)
 * @method $this withName(array $name)
 * @method $this withRouteName(string $routeName)
 */
final class TabFactory extends AbstractPsObjectModelFactory implements WithLang, WithShop
{
    protected function getObjectModelClass(): string
    {
        return Tab::class;
    }
}

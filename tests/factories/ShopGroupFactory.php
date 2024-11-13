<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @extends AbstractPsObjectModelFactory<ShopGroup>
 * @see \ShopGroupCore
 */
final class ShopGroupFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return ShopGroup::class;
    }
}

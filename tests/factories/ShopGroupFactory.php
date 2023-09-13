<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 */
final class ShopGroupFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return ShopGroup::class;
    }
}

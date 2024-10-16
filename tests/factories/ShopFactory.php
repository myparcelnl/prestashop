<?php

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @extends AbstractPsObjectModelFactory<Shop>
 * @see \ShopCore
 */
final class ShopFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Shop::class;
    }
}

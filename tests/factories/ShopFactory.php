<?php

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 */
final class ShopFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Shop::class;
    }
}

<?php

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 */
final class CartFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Cart::class;
    }
}

<?php

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 */
final class CustomerFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Customer::class;
    }
}

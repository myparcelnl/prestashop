<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \CountryCore
 */
final class CountryFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Country::class;
    }
}

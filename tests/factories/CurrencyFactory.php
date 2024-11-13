<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @extends AbstractPsObjectModelFactory<Currency>
 * @see \CurrencyCore
 */
final class CurrencyFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Currency::class;
    }
}

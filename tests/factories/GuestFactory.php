<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @extends AbstractPsObjectModelFactory<\Guest>
 * @see \GuestCore
 */
final class GuestFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Guest::class;
    }
}

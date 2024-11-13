<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \ManufacturerCore
 * @extends AbstractPsObjectModelFactory<Manufacturer>
 * @see \ManufacturerCore
 */
final class ManufacturerFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Manufacturer::class;
    }
}

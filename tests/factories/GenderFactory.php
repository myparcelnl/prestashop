<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \GenderCore
 */
final class GenderFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Gender::class;
    }
}

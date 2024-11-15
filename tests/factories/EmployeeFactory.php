<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \EmployeeCore
 */
final class EmployeeFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Employee::class;
    }
}

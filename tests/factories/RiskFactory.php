<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \RiskCore
 * @extends AbstractPsObjectModelFactory<Risk>
 * @see \RiskCore
 */
final class RiskFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Risk::class;
    }
}

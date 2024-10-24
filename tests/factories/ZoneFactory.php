<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithActive;

/**
 * @method $this withName(string $name)
 * @extends AbstractPsObjectModelFactory<Zone>
 * @see \ZoneCore
 */
final class ZoneFactory extends AbstractPsObjectModelFactory implements WithActive
{
    protected function getObjectModelClass(): string
    {
        return Zone::class;
    }
}

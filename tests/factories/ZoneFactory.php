<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @method $this withName(string $name)
 * @method $this withActive(int $active)
 */
final class ZoneFactory extends AbstractPsObjectModelFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withActive(1);
    }

    protected function getObjectModelClass(): string
    {
        return Zone::class;
    }
}

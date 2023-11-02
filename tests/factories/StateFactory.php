<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCountry;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithZone;

/**
 * @see \StateCore
 * @method $this withIsoCode(string $isoCode)
 * @method $this withName(string $name)
 * @method $this withActive(bool $active)
 */
final class StateFactory extends AbstractPsObjectModelFactory implements WithCountry, WithZone
{
    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withActive(true)
            ->withIdCountry(1)
            ->withIdZone(1);
    }

    protected function getObjectModelClass(): string
    {
        return State::class;
    }
}

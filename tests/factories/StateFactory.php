<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithActive;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCountry;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithZone;

/**
 * @see \StateCore
 * @method $this withIsoCode(string $isoCode)
 * @method $this withName(string $name)
 * @extends AbstractPsObjectModelFactory<State>
 * @see \StateCore
 */
final class StateFactory extends AbstractPsObjectModelFactory implements WithCountry, WithZone, WithActive
{
    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withIdCountry(1)
            ->withIdZone(1);
    }

    protected function getObjectModelClass(): string
    {
        return State::class;
    }
}

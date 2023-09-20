<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \StateCore
 * @method self withIdCountry(int $idCountry)
 * @method self withIdZone(int $idZone)
 * @method self withIsoCode(string $isoCode)
 * @method self withName(string $name)
 * @method self withActive(bool $active)
 */
final class StateFactory extends AbstractPsObjectModelFactory
{
    /**
     * @param  Country|\CountryFactory $country
     *
     * @return $this
     */
    public function withCountry($country): self
    {
        return $this->withModel('country', $country);
    }

    /**
     * @param  Zone|\ZoneFactory $zone
     *
     * @return $this
     */
    public function withZone($zone): self
    {
        return $this->withModel('zone', $zone);
    }

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

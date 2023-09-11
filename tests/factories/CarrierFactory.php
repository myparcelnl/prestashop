<?php

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;

/**
 * @method CarrierFactory withId(int $id)
 */
final class CarrierFactory extends AbstractPsClassFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    protected function getEntityClass(): string
    {
        return Carrier::class;
    }
}

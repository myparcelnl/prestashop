<?php

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;

/**
 * @method ShopFactory withId(int $id)
 */
final class ShopFactory extends AbstractPsClassFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    protected function getEntityClass(): string
    {
        return Shop::class;
    }
}

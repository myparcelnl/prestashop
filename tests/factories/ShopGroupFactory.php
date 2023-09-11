<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;

/**
 * @method ShopGroupFactory withId(int $id)
 */
final class ShopGroupFactory extends AbstractPsClassFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    protected function getEntityClass(): string
    {
        return ShopGroup::class;
    }
}

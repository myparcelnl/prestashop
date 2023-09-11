<?php

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;

/**
 * @method LangFactory withId(int $id)
 */
final class LangFactory extends AbstractPsClassFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    protected function getEntityClass(): string
    {
        return Lang::class;
    }
}

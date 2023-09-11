<?php

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;

/**
 * @method AddressInvoiceFactory withId(int $id)
 */
final class AddressInvoiceFactory extends AbstractPsClassFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withId($this->getNextId());
    }

    protected function getEntityClass(): string
    {
        return AddressInvoice::class;
    }
}

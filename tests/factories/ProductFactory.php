<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \ProductCore
 * @method $this withName(array $names)
 */
final class ProductFactory extends AbstractPsObjectModelFactory
{
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withName([
                1 => 'Test product',
                2 => 'Test product 2',
            ]);
    }

    protected function getObjectModelClass(): string
    {
        return Product::class;
    }
}

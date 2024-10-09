<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \ProductCore
 * @method $this withIsActive(bool $isActive)
 * @method $this withIsAvailableForOrder(bool $isAvailableForOrder)
 * @method $this withIsVirtual(bool $isVirtual)
 * @method $this withName(array $names)
 * @method $this withPrice(int $price)
 * @method $this withWeight(float|string $weight)
 * @extends AbstractPsObjectModelFactory<Product>
 * @see \ProductCore
 */
final class ProductFactory extends AbstractPsObjectModelFactory
{
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withName([
                1 => 'Test product',
                2 => 'Test product 2',
            ])
            ->withIsActive(true)
            ->withIsAvailableForOrder(true)
            ->withIsVirtual(false);
    }

    protected function getObjectModelClass(): string
    {
        return Product::class;
    }
}

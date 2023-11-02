<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithGroup;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithTimestamps;

/**
 * @method $this withReduction(float $reduction)
 * @method $this withPriceDisplayMethod(int $priceDisplayMethod)
 * @method $this withShowPrices(int $showPrices)
 */
final class GroupFactory extends AbstractPsObjectModelFactory implements WithTimestamps, WithGroup
{
    protected function getObjectModelClass(): string
    {
        return Group::class;
    }
}

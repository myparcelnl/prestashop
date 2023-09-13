<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @method self withIdGroup(int $idGroup)
 * @method self withReduction(float $reduction)
 * @method self withPriceDisplayMethod(int $priceDisplayMethod)
 * @method self withShowPrices(int $showPrices)
 * @method self withDateAdd(string $dateAdd)
 * @method self withDateUpd(string $dateUpd)
 */
final class GroupFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Group::class;
    }
}

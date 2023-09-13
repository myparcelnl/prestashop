<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @method self withIdCarrier(int $id)
 * @method self withIdReference(int $id)
 * @method self withName(string $name)
 * @method self withUrl(string $url)
 * @method self withActive(int $active)
 * @method self withDeleted(int $deleted)
 * @method self withShippingHandling(int $shippingHandling)
 * @method self withRangeBehavior(int $rangeBehavior)
 * @method self withIsModule(int $isModule)
 * @method self withIsFree(int $isFree)
 * @method self withShippingExternal(int $shippingExternal)
 * @method self withNeedRange(int $needRange)
 * @method self withExternalModuleName(string $externalModuleName)
 * @method self withShippingMethod(string $shippingMethod)
 * @method self withPosition(int $position)
 * @method self withMaxWidth(int $maxWidth)
 * @method self withMaxHeight(int $maxHeight)
 * @method self withMaxDepth(int $maxDepth)
 * @method self withMaxWeight(int $maxWeight)
 * @method self withGrade(int $grade)
 */
final class CarrierFactory extends AbstractPsObjectModelFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this->withIdCarrier($this->getNextId());
    }

    protected function getObjectModelClass(): string
    {
        return Carrier::class;
    }
}

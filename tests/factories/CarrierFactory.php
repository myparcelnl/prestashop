<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCarrier;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithSoftDeletes;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithTimestamps;

/**
 * @see \CarrierCore
 * @method $this withName(string $name)
 * @method $this withUrl(string $url)
 * @method $this withActive(int $active)
 * @method $this withShippingHandling(int $shippingHandling)
 * @method $this withRangeBehavior(int $rangeBehavior)
 * @method $this withIsModule(int $isModule)
 * @method $this withIsFree(int $isFree)
 * @method $this withShippingExternal(int $shippingExternal)
 * @method $this withNeedRange(int $needRange)
 * @method $this withExternalModuleName(string $externalModuleName)
 * @method $this withShippingMethod(string $shippingMethod)
 * @method $this withPosition(int $position)
 * @method $this withMaxWidth(int $maxWidth)
 * @method $this withMaxHeight(int $maxHeight)
 * @method $this withMaxDepth(int $maxDepth)
 * @method $this withMaxWeight(int $maxWeight)
 * @method $this withGrade(int $grade)
 */
final class CarrierFactory extends AbstractPsObjectModelFactory implements WithTimestamps, WithSoftDeletes, WithCarrier
{
    /**
     * Defined manually to avoid this being treated as the id of a class.
     *
     * @param  int $id
     *
     * @return self
     */
    public function withIdReference(int $id): self
    {
        return $this->with(['id_reference' => $id]);
    }

    protected function createDefault(): FactoryInterface
    {
        return $this->withActive(1);
    }

    protected function getObjectModelClass(): string
    {
        return Carrier::class;
    }
}

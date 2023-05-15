<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Model\PdkShippingMethod;
use MyParcelNL\Pdk\App\ShippingMethod\Repository\AbstractPdkShippingMethodRepository;

class PsShippingMethodRepository extends AbstractPdkShippingMethodRepository
{
    public function all(): PdkShippingMethodCollection
    {
        // TODO: Implement all() method.
        return new PdkShippingMethodCollection();
    }

    public function get($input): PdkShippingMethod
    {
        // TODO: Implement get() method.
        return new PdkShippingMethod();
    }

    public function getMany($input): PdkShippingMethodCollection
    {
        // TODO: Implement all() method.
        return new PdkShippingMethodCollection();
    }
}

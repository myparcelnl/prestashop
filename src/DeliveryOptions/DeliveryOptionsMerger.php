<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\DeliveryOptions;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Collection;

class DeliveryOptionsMerger
{
    private const DEFAULT_VALUES = [
        'deliveryType' => AbstractConsignment::DELIVERY_TYPE_STANDARD_NAME,
    ];

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions|array ...$deliveryOptionsAdapters
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     * @throws \Exception
     */
    public static function create(...$deliveryOptionsAdapters): DeliveryOptions
    {
        $adapters = (new Collection($deliveryOptionsAdapters))
            ->filter()
            ->map(static function ($adapter) {
                $array = is_array($adapter) ? $adapter : $adapter->toArray();
                return (new Collection($array))->toArrayWithoutNull();
            })
            ->toArrayWithoutNull();

        return new DeliveryOptions(array_merge(self::DEFAULT_VALUES, ...$adapters));
    }
}

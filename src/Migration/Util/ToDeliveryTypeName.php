<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

final class ToDeliveryTypeName extends TransformValue
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param  mixed $defaultValue
     */
    public function __construct($defaultValue = DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME)
    {
        parent::__construct([$this, 'convert']);
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param  mixed $value
     *
     * @return string
     */
    protected function convert($value): string
    {
        $mapper = ApiMapperService::forDeliveryType();
        $id     = is_numeric($value) ? (int) $value : $mapper->idFromLegacyName((string) $value);
        $name   = null === $id ? null : $mapper->legacyNameFromId($id);

        return $name ?? $this->defaultValue;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

final class ToPackageTypeName extends TransformValue
{
    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @param  mixed $defaultValue
     */
    public function __construct($defaultValue = DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME)
    {
        parent::__construct([$this, 'convert']);
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function convert($value)
    {
        return Utils::convertToName($value, DeliveryOptions::PACKAGE_TYPES_NAMES_IDS_MAP) ?? $this->defaultValue;
    }
}


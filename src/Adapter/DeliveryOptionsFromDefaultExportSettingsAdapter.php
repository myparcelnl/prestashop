<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\ShipmentOptionsV3Adapter;
use MyParcelNL\Sdk\src\Support\Collection;

class DeliveryOptionsFromDefaultExportSettingsAdapter extends AbstractDeliveryOptionsAdapter
{
    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     *
     * @throws \Exception
     */
    public function __construct(Collection $data)
    {
        $packageType           = $this->intOrNull($data, Constant::PACKAGE_TYPE_CONFIGURATION_NAME);
        $this->packageType     = $packageType ? (new PackageTypeCalculator())->convertToName($packageType) : null;
        $this->shipmentOptions = new ShipmentOptionsV3Adapter([
            'age_check'      => $this->boolOrNull($data, Constant::AGE_CHECK_CONFIGURATION_NAME),
            'insurance'      => $this->intOrNull($data, Constant::INSURANCE_CONFIGURATION_NAME),
            'large_format'   => $this->getLargeFormat($data),
            'only_recipient' => $this->boolOrNull($data, Constant::ONLY_RECIPIENT_CONFIGURATION_NAME),
            'return'         => $this->boolOrNull($data, Constant::RETURN_PACKAGE_CONFIGURATION_NAME),
            'signature'      => $this->boolOrNull($data, Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME),
        ]);
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     * @param  string                                 $name
     *
     * @return null|bool
     */
    private function boolOrNull(Collection $data, string $name): ?bool
    {
        $value = $this->getValue($data, $name);

        return $value ? (bool) $value : null;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     *
     * @return null|bool
     */
    private function getLargeFormat(Collection $data): ?bool
    {
        $format = $this->getValue($data, Constant::PACKAGE_FORMAT_CONFIGURATION_NAME);

        return $format ? $format === Constant::PACKAGE_FORMAT_LARGE_INDEX : null;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     * @param  string                                 $name
     *
     * @return mixed
     */
    private function getValue(Collection $data, string $name)
    {
        return $data->firstWhere('name', $name)['value'];
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     * @param  string                                 $name
     *
     * @return null|int
     */
    private function intOrNull(Collection $data, string $name): ?int
    {
        $value = $this->getValue($data, $name);

        return $value ? (int) $value : null;
    }
}

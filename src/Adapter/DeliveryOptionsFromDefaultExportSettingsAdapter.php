<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Adapter;

use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Collection;

class DeliveryOptionsFromDefaultExportSettingsAdapter extends DeliveryOptions
{
    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $data
     */
    public function __construct(Collection $data)
    {
        $packageType = $this->intOrNull($data, Constant::PACKAGE_TYPE_CONFIGURATION_NAME);

        parent::__construct([
            'carrier'         => null,
            'date'            => null,
            'deliveryType'    => null,
            'packageType'     => $packageType ? (new PackageTypeCalculator())->convertToName($packageType) : null,
            'pickupLocation'  => null,
            'shipmentOptions' => [
                'ageCheck'      => $this->boolOrNull($data, Constant::AGE_CHECK_CONFIGURATION_NAME),
                'insurance'     => $this->intOrNull($data, Constant::INSURANCE_CONFIGURATION_NAME),
                'largeFormat'   => $this->getLargeFormat($data),
                'onlyRecipient' => $this->boolOrNull($data, Constant::ONLY_RECIPIENT_CONFIGURATION_NAME),
                'return'        => $this->boolOrNull($data, Constant::RETURN_PACKAGE_CONFIGURATION_NAME),
                'signature'     => $this->boolOrNull($data, Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME),

            ],
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
        return $data->firstWhere('name', $name)['value'] ?? null;
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

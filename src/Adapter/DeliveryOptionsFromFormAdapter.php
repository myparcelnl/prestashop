<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\ShipmentOptionsV3Adapter;

class DeliveryOptionsFromFormAdapter extends AbstractDeliveryOptionsAdapter
{
    /**
     * Only set the fields that can be updated from the\ form.
     *
     * @param  array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        $this->packageType     = (new PackageTypeCalculator())->convertToName($data['packageType']);
        $this->shipmentOptions = new ShipmentOptionsV3Adapter([
            'insurance'      => $this->getInsurance($data),
            'large_format'   => $this->isLargeFormat($data),
            'age_check'      => $this->isEnabled($data['ageCheck'] ?? null),
            'only_recipient' => $this->isEnabled($data['onlyRecipient'] ?? null),
            'return'         => $this->isEnabled($data['returnUndelivered'] ?? null),
            'signature'      => $this->isEnabled($data['signatureRequired'] ?? null),
        ]);
    }

    /**
     * @param  array $data
     *
     * @return null|int
     */
    private function getInsurance(array $data): int
    {
        $insurance = 0;

        if (isset($data['insuranceAmount']) && $this->isEnabled($data['insurance'] ?? null)) {
            $amount    = str_replace('amount', '', $data['insuranceAmount']);
            $insurance = (int) $amount;
        }

        return $insurance;
    }

    /**
     * @param  mixed $value
     *
     * @return bool
     */
    private function isEnabled($value): bool
    {
        return '1' === $value;
    }

    /**
     * @param  array $data
     *
     * @return bool
     */
    private function isLargeFormat(array $data = []): bool
    {
        $packageFormat = $data['packageFormat'] ?? null;

        return 'large' === Constant::PACKAGE_FORMATS[$packageFormat];
    }
}

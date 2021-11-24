<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\PickupLocationV3Adapter;
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
        $deliveryOptions = $data['deliveryOptions'] ?? [];
        foreach ($deliveryOptions as $key => $value) {
            $this->{$key} = $value;
        }

        $this->packageType = (new PackageTypeCalculator())->convertToName($deliveryOptions['packageType'] ?? null);

        $shipmentOptions       = $deliveryOptions['shipmentOptions'] ?? [];
        $this->shipmentOptions = new ShipmentOptionsV3Adapter([
            'insurance'      => $this->getInsurance($shipmentOptions),
            'large_format'   => $this->isLargeFormat($data['labelOptions'] ?? []),
            'age_check'      => $this->isEnabled($shipmentOptions['age_check'] ?? null),
            'only_recipient' => $this->isEnabled($shipmentOptions['only_recipient'] ?? null),
            'return'         => $this->isEnabled($shipmentOptions['return'] ?? null),
            'signature'      => $this->isEnabled($shipmentOptions['signature'] ?? null),
        ]);

        $this->pickupLocation = $this->isPickup()
            ? new PickupLocationV3Adapter($deliveryOptions['pickupLocation'] ?? [])
            : null;
    }

    /**
     * @param  array $data
     *
     * @return null|int
     */
    private function getInsurance(array $data): int
    {
        $insurance = 0;

        if ($data['insurance'] ?? null) {
            $amount    = $data['insurance'];
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
        return 'true' === $value;
    }

    /**
     * @param  array $data
     *
     * @return bool
     */
    private function isLargeFormat(array $data = []): bool
    {
        $packageFormat = $data['package_format'] ?? null;

        return Constant::PACKAGE_FORMAT_LARGE === (int) $packageFormat;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Adapter;

use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;

class DeliveryOptionsFromFormAdapter extends DeliveryOptions
{
    public function __construct(?array $data = null)
    {
        $deliveryOptions = $data['deliveryOptions'] ?? [];
        $shipmentOptions = $deliveryOptions['shipmentOptions'] ?? [];

        parent::__construct([
            'carrier'         => $data['carrier'],
            'date'            => $data['date'],
            'deliveryType'    => $data['deliveryType'],
            'packageType'     => (new PackageTypeCalculator())->convertToName($deliveryOptions['packageType'] ?? null),
            'pickupLocation'  => $this->isPickup()
                ? new RetailLocation($deliveryOptions['pickupLocation'] ?? [])
                : null,
            'shipmentOptions' => [
                'insurance'     => $this->getInsurance($shipmentOptions),
                'largeFormat'   => $this->isLargeFormat($data['labelOptions'] ?? []),
                'ageCheck'      => $this->isEnabled($shipmentOptions['age_check'] ?? null),
                'onlyRecipient' => $this->isEnabled($shipmentOptions['only_recipient'] ?? null),
                'return'        => $this->isEnabled($shipmentOptions['return'] ?? null),
                'signature'     => $this->isEnabled($shipmentOptions['signature'] ?? null),

            ],
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

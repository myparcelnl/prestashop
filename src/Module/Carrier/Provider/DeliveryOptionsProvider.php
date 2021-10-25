<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Carrier\Provider;

use DateTime;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;

class DeliveryOptionsProvider
{
    /**
     * @var DateTime
     */
    protected $deliveryDate;

    /**
     * @var DateTime
     */
    protected $nextDeliveryDate;

    /**
     * @param  int  $orderId
     *
     * @return array - Delivery options array
     * @throws \Exception
     */
    public function provide(int $orderId): array
    {
        $deliveryOptions = DeliveryOptions::getFromOrder($orderId);

        if (! $deliveryOptions) {
            $deliveryOptions = new DeliveryOptionsV3Adapter();
        }

        $deliveryOptionsArray   = $deliveryOptions->toArray();
        $this->nextDeliveryDate = new DateTime('tomorrow'); // TODO: get next available delivery date

        if ($deliveryOptions->getDate()) {
            $this->deliveryDate = new DateTime($deliveryOptions->getDate());

            if ($this->nextDeliveryDate > $this->deliveryDate) {
                $deliveryOptionsArray['date'] = $this->nextDeliveryDate->format('Y-m-d');
            }
        } else {
            $deliveryOptionsArray['date'] = $this->nextDeliveryDate->format('Y-m-d');
        }

        // Prestashop's formatDate function, (which is used in templates) can't handle our date formats.
        $deliveryOptionsArray['date'] = (new DateTime($deliveryOptionsArray['date']))->format('Y-m-d');

        return $deliveryOptionsArray;
    }

    /**
     * @param  int  $orderId
     *
     * @return bool
     * @throws \Exception
     */
    public function provideWarningDisplay(int $orderId): bool
    {
        if (! $this->nextDeliveryDate) {
            $this->provide($orderId);
        }

        return $this->nextDeliveryDate > $this->deliveryDate;
    }
}

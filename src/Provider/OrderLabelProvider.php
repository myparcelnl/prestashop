<?php

namespace MyParcelNL\PrestaShop\Provider;

use MyParcelNL\PrestaShop\Module\Carrier\Provider\CarrierSettingsProvider;
use Order;
use OrderLabel;

/**
 * @deprecated
 */
class OrderLabelProvider
{
    /**
     * @param  int   $orderId
     * @param  array $labelIds
     *
     * @return null|array|bool|\mysqli_result|\PDOStatement|resource
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function provideLabels(int $orderId, array $labelIds = [])
    {
        $labels                  = OrderLabel::getOrderLabels($orderId, $labelIds);
        $order                   = new Order($orderId);
        $carrierSettingsProvider = new CarrierSettingsProvider();
        $carrierSettings         = $carrierSettingsProvider->provide($order->id_carrier);

        if (! empty($labels)) {
            foreach ($labels as &$label) {
                $label['ALLOW_DELIVERY_FORM'] = $carrierSettings['delivery']['ALLOW_FORM'];
                $label['ALLOW_RETURN_FORM']   = $carrierSettings['return']['ALLOW_FORM'];
            }
        }

        return $labels;
    }

    public function provideOrderId(int $labelId): int
    {
        return OrderLabel::getOrderIdByLabelId($labelId);
    }
}

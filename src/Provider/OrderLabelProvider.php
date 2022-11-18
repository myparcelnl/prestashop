<?php

namespace Gett\MyparcelBE\Provider;

use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Order;
use OrderLabel;

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
                if ("1" === $label['is_return']) {
                    $label['is_return'] = true;
                    continue;
                }
                $label['is_return']           = false;
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

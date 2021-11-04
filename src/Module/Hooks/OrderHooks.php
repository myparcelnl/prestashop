<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Db;
use Exception;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Service\CarrierName;
use Gett\MyparcelBE\Service\Order\OrderDeliveryDate;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use stdClass;

trait OrderHooks
{
    /**
     * @param $params array [
     *      'cart' => $this->context->cart,
     *      'order' => $order,
     *      'customer' => $this->context->customer,
     *      'currency' => $this->context->currency,
     *     'orderStatus' => $order_status,
     * ]
     **/
    public function hookActionValidateOrder(array $params)
    {
        $order = $params['order'];
        $cart = $params['cart'];
        $packageTypeCalculator = new PackageTypeCalculator();
        $enableDeliveryOptions = $packageTypeCalculator->allowDeliveryOptions($cart, $this->getModuleCountry());
        if ($enableDeliveryOptions) {
            return;
        }
        $packageTypeId = $packageTypeCalculator->getOrderPackageType((int) $order->id, (int) $order->id_carrier);
        $packageType = Constant::PACKAGE_TYPES[$packageTypeId] ?? AbstractConsignment::DEFAULT_PACKAGE_TYPE_NAME;
        $optionsObj = new stdClass();
        $optionsObj->isPickup = false;
        $optionsObj->date = (new OrderDeliveryDate())->get((int) $order->id_carrier);
        $optionsObj->carrier = (new CarrierName())->get((int) $order->id_carrier);
        $optionsObj->packageType = $packageType;
        $optionsObj->deliveryType = 'standard';
        $optionsObj->shipmentOptions = new stdClass();
        $options = json_encode($optionsObj);
        try {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->insert(
                Table::TABLE_DELIVERY_SETTINGS,
                ['id_cart' => (int) $order->id_cart, 'delivery_settings' => pSQL($options)],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            Logger::addLog($exception->getMessage(), true, true);
            Logger::addLog($exception->getFile(), true, true);
            Logger::addLog($exception->getLine(), true, true);
        }
    }
}

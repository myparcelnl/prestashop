<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Exception;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\CarrierName;
use Gett\MyparcelBE\Service\Order\OrderDeliveryDate;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use stdClass;

trait OrderHooks
{
    /**
     * @param  array $params
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookActionValidateOrder(array $params): void
    {
        $order = new Order($params['order']->id);
        /** @var \Cart $cart */
        $cart = $params['cart'];

        $packageTypeCalculator = new PackageTypeCalculator();
        $enableDeliveryOptions = $packageTypeCalculator->deliveryOptionsAllowed($cart, $this->getModuleCountry());

        if ($enableDeliveryOptions) {
            return;
        }

        $packageTypeId = $packageTypeCalculator->getOrderPackageType($order);

        if (! $packageTypeId) {
            $packageTypeId = Constant::PACKAGE_TYPE_PACKAGE;
        }

        $packageType = Constant::PACKAGE_TYPES[$packageTypeId] ?? AbstractConsignment::DEFAULT_PACKAGE_TYPE_NAME;

        $carrierId       = $order->getIdOrderCarrier();
        $deliveryOptions = new DeliveryOptionsV3Adapter([
            'carrier'     => (new CarrierName())->get($carrierId),
            'date'        => (new OrderDeliveryDate())->get($carrierId),
            'packageType' => $packageType,
        ]);

        try {
            DeliveryOptions::save($order->getIdCart(), $deliveryOptions->toArray());
        } catch (Exception $exception) {
            Logger::addLog($exception->getMessage(), true, true);
            Logger::addLog($exception->getFile(), true, true);
            Logger::addLog($exception->getLine(), true, true);
        }
    }
}

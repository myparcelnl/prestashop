<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Exception;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsMerger;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Logger\OrderLogger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\CarrierName;
use Gett\MyparcelBE\Service\Order\OrderDeliveryDate;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

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

        $packageTypeCalculator = new PackageTypeCalculator();

        $packageTypeId = $packageTypeCalculator->getOrderPackageType($order);

        if (! $packageTypeId) {
            $packageTypeId = Constant::PACKAGE_TYPE_PACKAGE;
        }

        $packageType      = Constant::PACKAGE_TYPES[$packageTypeId] ?? AbstractConsignment::DEFAULT_PACKAGE_TYPE_NAME;
        $carrierId        = $order->getIdOrderCarrier();
        $deliveryOptions  = new DeliveryOptionsV3Adapter([
            'carrier'     => (new CarrierName())->get($carrierId),
            'date'        => (new OrderDeliveryDate())->get($carrierId),
            'packageType' => $packageType,
        ]);
        $optionsFromOrder = OrderSettingsFactory::create($order)->getDeliveryOptions();

        $deliveryOptions = DeliveryOptionsMerger::create(
            $deliveryOptions,
            (new LabelOptionsResolver())->getDeliveryOptions($order, $optionsFromOrder),
            $optionsFromOrder
        );

        try {
            DeliveryOptions::save($order->getIdCart(), $deliveryOptions->toArray());
        } catch (Exception $e) {
            OrderLogger::addLog(['message' => $e, 'order' => $order,], OrderLogger::ERROR);
        }
    }
}

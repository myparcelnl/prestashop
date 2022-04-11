<?php

namespace Gett\MyparcelBE\Label;

use Gett\MyparcelBE\Carrier\PackageFormatCalculator;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Support\Arr;

class LabelOptionsResolver
{
    /**
     * Map of array key to array of 0: config entry and 1: AbstractShipmentOptionsAdapter method.
     */
    private const SHIPMENT_OPTIONS_MAP = [
        'only_recipient' => [Constant::ONLY_RECIPIENT_CONFIGURATION_NAME, 'hasOnlyRecipient',],
        'signature'      => [Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME, 'hasSignature',],
        'age_check'      => [Constant::AGE_CHECK_CONFIGURATION_NAME],
        'insurance'      => [Constant::INSURANCE_CONFIGURATION_NAME, 'getInsurance'],
        'return'         => [Constant::RETURN_PACKAGE_CONFIGURATION_NAME],
    ];

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return false|string
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getLabelOptionsJson(Order $order)
    {
        return json_encode($this->getLabelOptions($order));
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function getLabelOptions(Order $order): array
    {
        $deliveryOptions = OrderSettingsFactory::create($order)
            ->getDeliveryOptions();

        return array_merge(
            $this->getShipmentOptions($deliveryOptions, $order->getProducts(), $order->getIdCarrier()),
            [
                'package_type'   => $this->getPackageType($order, $deliveryOptions),
                'package_format' => $this->getPackageFormat($order, $deliveryOptions),
            ]
        );
    }

    /**
     * @param \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter|null $deliveryOptions
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getDeliveryOptions(Order $order, ?AbstractDeliveryOptionsAdapter $deliveryOptions): array
    {
        $shipmentOptions = $this->getShipmentOptions(
            $deliveryOptions,
            $order->getProducts(),
            $order->getIdCarrier()
        );

        if (isset($shipmentOptions['insurance'])) {
            $shipmentOptions['insurance'] = false === $shipmentOptions['insurance'] ? 0 : 500;
        }

        return [
            'shipmentOptions' => $shipmentOptions,
            'package_type'    => $this->getPackageType($order, $deliveryOptions),
            'package_format'  => $this->getPackageFormat($order, $deliveryOptions),
        ];
    }

    /**
     * @param  array  $products
     * @param  string $setting
     *
     * @return bool
     */
    private function anyProductHasSetting(array $products, string $setting): bool
    {
        $product = Arr::first($products, function (array $product) use ($setting) {
            return ProductConfigurationProvider::get(
                $product['product_id'],
                $setting,
                false
            );
        });

        return (bool) $product;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getPackageFormat(Order $order, ?AbstractDeliveryOptionsAdapter $deliveryOptions): int
    {
        $shipmentOptions = $deliveryOptions ? $deliveryOptions->getShipmentOptions() : null;
        $largeFormat     = null;

        if ($shipmentOptions && $shipmentOptions->hasLargeFormat()) {
            $largeFormat = Constant::PACKAGE_FORMAT_LARGE_INDEX;
        }

        return $largeFormat ?? (new PackageFormatCalculator())->getOrderPackageFormat($order);
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *v
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getPackageType(Order $order, ?AbstractDeliveryOptionsAdapter $deliveryOptions): int
    {
        $packageType = $deliveryOptions && $deliveryOptions->getPackageType()
            ? $deliveryOptions->getPackageTypeId()
            : null;
        return $packageType ?? (new PackageTypeCalculator())->getOrderPackageType($order);
    }

    /**
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     * @param  null|string                                                                     $shipmentOptionsMethod
     *
     * @return bool
     */
    private function getShipmentOption(
        ?AbstractDeliveryOptionsAdapter $deliveryOptions,
        ?string                         $shipmentOptionsMethod = null
    ): bool {
        return $shipmentOptionsMethod
            && $deliveryOptions
            && $deliveryOptions->getShipmentOptions()
            && $deliveryOptions
                ->getShipmentOptions()
                ->{$shipmentOptionsMethod}();
    }

    /**
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     * @param  array                                                                           $products
     * @param  int                                                                             $carrierId
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getShipmentOptions(
        ?AbstractDeliveryOptionsAdapter $deliveryOptions,
        array                           $products,
        int                             $carrierId
    ): array {
        $options = [];

        foreach (self::SHIPMENT_OPTIONS_MAP as $key => $array) {
            $settingName           = $array[0];
            $shipmentOptionsMethod = $array[1] ?? null;

            $setInShipmentOptions = $this->getShipmentOption($deliveryOptions, $shipmentOptionsMethod);
            $setInProduct         = $this->anyProductHasSetting($products, $settingName);
            $setByDefault         = $this->isSetByDefault($carrierId, $settingName);

            $options[$key] = $setInShipmentOptions || $setInProduct || $setByDefault;
        }

        return $options;
    }

    /**
     * @param  int    $psCarrierId
     * @param  string $setting
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    private function isSetByDefault(int $psCarrierId, string $setting): bool
    {
        return CarrierConfigurationProvider::get($psCarrierId, $setting, false);
    }
}

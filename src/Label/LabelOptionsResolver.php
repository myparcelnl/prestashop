<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Label;

use MyParcelNL\PrestaShop\Carrier\PackageFormatCalculator;
use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Factory\Consignment\ConsignmentFactory;
use MyParcelNL\PrestaShop\Factory\OrderSettingsFactory;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Service\CarrierConfigurationProvider;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\PrestaShop\Service\ProductConfigurationProvider;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use Throwable;

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
     * @var \MyParcelNL\Pdk\Base\Service\CountryService
     */
    private $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order $order
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
     * @param \MyParcelNL\PrestaShop\Model\Core\Order                                               $order
     * @param \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions|null $deliveryOptions
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getDeliveryOptions(Order $order, ?DeliveryOptions $deliveryOptions): array
    {
        $packageType     = $this->getPackageType($order, $deliveryOptions);
        $shipmentOptions = $this->getShipmentOptions(
            $deliveryOptions,
            $order->getProducts(),
            $order->getIdCarrier()
        );

        if ($deliveryOptions
            && isset($shipmentOptions[AbstractConsignment::SHIPMENT_OPTION_INSURANCE])
            && is_bool($shipmentOptions[AbstractConsignment::SHIPMENT_OPTION_INSURANCE])
        ) {
            $shipmentOptions[AbstractConsignment::SHIPMENT_OPTION_INSURANCE] = $this->getInsurance(
                $deliveryOptions,
                $packageType,
                $order
            );
        }

        return [
            'shipmentOptions' => $shipmentOptions,
            'package_type'    => $packageType,
            'package_format'  => $this->getPackageFormat($order, $deliveryOptions),
        ];
    }

    /**
     * @param \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     * @param int                                                                        $packageType
     * @param \MyParcelNL\PrestaShop\Model\Core\Order                                          $order
     *
     * @return int the amount in euro for which the package should be insured
     */
    public function getInsurance(DeliveryOptions $deliveryOptions, int $packageType, Order $order): int
    {
        $psCarrierId = $order->getIdCarrier();

        try {
            $fromPrice   = CarrierConfigurationProvider::get($psCarrierId, Constant::INSURANCE_CONFIGURATION_FROM_PRICE);
            $maxAmount   = CarrierConfigurationProvider::get($psCarrierId, Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT);
            $consignment = (new ConsignmentFactory(Constant::CONSIGNMENT_INIT_PARAMS_FOR_CHECKING_ONLY))
                ->fromOrder(
                    $order,
                    OrderSettingsFactory::create($order)->getDeliveryOptions()
                )
                ->first();
            $consignment->setPackageType($packageType);
        } catch (Throwable $e) {
            return Constant::INSURANCE_CONFIGURATION_NONE;
        }

        if ($this->countryService->isPostNLShipmentFromNLToBE($consignment)) {
            try {
                if (CarrierConfigurationProvider::get($psCarrierId, Constant::INSURANCE_CONFIGURATION_BELGIUM)) {
                    return Constant::INSURANCE_CONFIGURATION_BELGIUM_AMOUNT;
                }
            } catch (Throwable $e) {
                return Constant::INSURANCE_CONFIGURATION_NONE;
            }
        }

        if (! CarrierConfigurationProvider::get($psCarrierId, Constant::INSURANCE_CONFIGURATION_NAME)) {
            return Constant::INSURANCE_CONFIGURATION_NONE;
        }

        $grandTotal = $order->getTotalProductsWithTaxes();

        if ($grandTotal < $fromPrice || ! $consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_INSURANCE)) {
            return Constant::INSURANCE_CONFIGURATION_NONE;
        }

        if ($deliveryOptions->getShipmentOptions()) {
            $insuredAmount = $deliveryOptions->getShipmentOptions()->getInsurance();
        }

        $insuredAmount = (int) min($insuredAmount ?? $grandTotal, $maxAmount ?? Constant::INSURANCE_CONFIGURATION_NONE);

        return $this->getHighestAllowedValue($insuredAmount, $consignment->getInsurancePossibilities());
    }

    /**
     * @param int   $threshold
     * @param array $allowedValues this must be an indexed array with values sorted from low to high
     *
     * @return int lowest allowed value that is higher than or equal to threshold, or the highest allowed value
     */
    private function getHighestAllowedValue(int $threshold, array $allowedValues): int
    {
        foreach ($allowedValues as $allowedValue) {
            if ($allowedValue < $threshold) {
                continue;
            }
            return $allowedValue;
        }

        return Arr::last($allowedValues);
    }

    /**
     * @param  array  $products
     * @param  string $setting
     *
     * @return bool
     */
    private function anyProductHasSetting(array $products, string $setting): bool
    {
        $product = Arr::first($products, static function (array $product) use ($setting) {
            return ProductConfigurationProvider::get(
                (int) $product['product_id'],
                $setting,
                false
            );
        });

        return (bool) $product;
    }

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getPackageFormat(Order $order, ?DeliveryOptions $deliveryOptions): int
    {
        $shipmentOptions = $deliveryOptions ? $deliveryOptions->getShipmentOptions() : null;
        $largeFormat     = null;

        if ($shipmentOptions && $shipmentOptions->hasLargeFormat()) {
            $largeFormat = Constant::PACKAGE_FORMAT_LARGE_INDEX;
        }

        return $largeFormat ?? (new PackageFormatCalculator())->getOrderPackageFormat($order);
    }

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *v
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getPackageType(Order $order, ?DeliveryOptions $deliveryOptions): int
    {
        $packageType = $deliveryOptions && $deliveryOptions->getPackageType()
            ? $deliveryOptions->getPackageTypeId()
            : null;
        return $packageType ?? (new PackageTypeCalculator())->getOrderPackageType($order);
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     * @param  null|string                                                                     $shipmentOptionsMethod
     *
     * @return bool
     */
    private function getShipmentOption(
        ?DeliveryOptions $deliveryOptions,
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
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     * @param  array                                                                           $products
     * @param  int                                                                             $carrierId
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getShipmentOptions(
        ?DeliveryOptions $deliveryOptions,
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
        return (bool) CarrierConfigurationProvider::get($psCarrierId, $setting, false);
    }
}

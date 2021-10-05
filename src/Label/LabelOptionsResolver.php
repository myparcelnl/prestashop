<?php

namespace Gett\MyparcelBE\Label;

use Gett\MyparcelBE\Carrier\PackageFormatCalculator;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliverySettings\DeliveryOptions;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use OrderLabel;

class LabelOptionsResolver
{
    public function getLabelOptions(array $params)
    {
        $delivery_settings = DeliveryOptions::queryByOrderId((int) $params['id_order']);

        $order_products = OrderLabel::getOrderProducts($params['id_order']);

        $packageType = $delivery_settings['packageType'] ??
            (new PackageTypeCalculator())->getOrderPackageType($params['id_order'], $params['id_carrier']);
        $packageFormat = ($delivery_settings['shipmentOptions']['large_format'] ?? false) ? 2 :
            (new PackageFormatCalculator())->getOrderPackageFormat($params['id_order'], $params['id_carrier']);
        // packageType is a string in delivery options, but we need the packageType int constant for the application
        if (! (int) $packageType) {
            $packageType = Constant::PACKAGE_TYPES_LEGACY_NAMES_IDS_MAP[$packageType] ?? AbstractConsignment::DEFAULT_PACKAGE_TYPE;
        }

        return json_encode([
            'package_type' => $packageType,
            'package_format' => $packageFormat,
            'only_to_recipient' => $this->getOnlyToRecipient($delivery_settings, $order_products, $params['id_carrier']),
            'age_check' => $this->getAgeCheck($delivery_settings, $order_products, $params['id_carrier']),
            'signature' => $this->getSignature($delivery_settings, $order_products, $params['id_carrier']),
            'insurance' => $this->getInsurance($delivery_settings, $order_products, $params['id_carrier']),
            'return_undelivered' => $this->getReturnUndelivered($order_products, $params['id_carrier']),
        ]);
    }

    private function getOnlyToRecipient(array $delivery_settings, array $products, int $id_carrier)
    {
        if (isset($delivery_settings['shipmentOptions']['only_recipient']) && true === $delivery_settings['shipmentOptions']['only_recipient']) {
            return true;
        }

        foreach ($products as $product) {
            if (ProductConfigurationProvider::get($product['product_id'], Constant::ONLY_RECIPIENT_CONFIGURATION_NAME, false)) {
                return true;
            }
        }

        return CarrierConfigurationProvider::get($id_carrier, Constant::ONLY_RECIPIENT_CONFIGURATION_NAME, false);
    }

    private function getAgeCheck(array $delivery_settings, array $products, int $id_carrier)
    {
        if (isset($delivery_settings['shipmentOptions']['age_check']) && $delivery_settings['shipmentOptions']['age_check']) {
            return true;
        }

        foreach ($products as $product) {
            if (ProductConfigurationProvider::get($product['product_id'], Constant::AGE_CHECK_CONFIGURATION_NAME)) {
                return true;
            }
        }

        return CarrierConfigurationProvider::get($id_carrier, Constant::AGE_CHECK_CONFIGURATION_NAME, false);
    }

    private function getSignature(array $delivery_settings, array $products, int $id_carrier)
    {
        if (isset($delivery_settings['shipmentOptions']['signature']) && true === $delivery_settings['shipmentOptions']['signature']) {
            return true;
        }

        foreach ($products as $product) {
            if (ProductConfigurationProvider::get(
                $product['product_id'],
                Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME
            )) {
                return true;
            }
        }

        return CarrierConfigurationProvider::get($id_carrier, Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME, false);
    }

    private function getInsurance($delivery_settings, array $products, int $id_carrier)
    {
        if (isset($delivery_settings['shipmentOptions']['insurance']) && $delivery_settings['shipmentOptions']['insurance']) {
            return $delivery_settings['shipmentOptions']['insurance'];
        }

        foreach ($products as $product) {
            if (ProductConfigurationProvider::get($product['product_id'], Constant::INSURANCE_CONFIGURATION_NAME)) {
                return true;
            }
        }

        return CarrierConfigurationProvider::get($id_carrier, Constant::INSURANCE_CONFIGURATION_NAME, false);
    }

    private function getReturnUndelivered(array $products, int $id_carrier)
    {
        foreach ($products as $product) {
            if (ProductConfigurationProvider::get($product['product_id'], Constant::RETURN_PACKAGE_CONFIGURATION_NAME)) {
                return true;
            }
        }

        return CarrierConfigurationProvider::get($id_carrier, Constant::RETURN_PACKAGE_CONFIGURATION_NAME, false);
    }
}

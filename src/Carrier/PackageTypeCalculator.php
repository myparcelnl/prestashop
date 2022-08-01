<?php

namespace Gett\MyparcelBE\Carrier;

use Carrier;
use Cart;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\ExclusiveField;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

class PackageTypeCalculator extends AbstractPackageCalculator
{
    /**
     * @param  null|string|int $packageType
     *
     * @return null|string
     */
    public function convertToName($packageType): ?string
    {
        $packageTypeName = $packageType;

        if (is_numeric($packageType)) {
            $packageType = (int) $packageType;
            $map         = array_flip(AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP);

            if (! array_key_exists($packageType, $map)) {
                return null;
            }

            $packageTypeName = $map[$packageType];
        } elseif (! array_key_exists($packageType, AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP)) {
            $packageTypeName = null;
        }

        return $packageTypeName;
    }

    /**
     * @param  null|int|string $packageType
     *
     * @return int
     */
    public function convertToId($packageType): int
    {
        $packageTypeName = $this->convertToName($packageType);

        return $packageTypeName
            ? AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP[$packageTypeName]
            : AbstractConsignment::PACKAGE_TYPE_PACKAGE;
    }

    /**
     * @param  \Cart  $cart
     * @param  string $countryIso
     *
     * @return bool
     */
    public function deliveryOptionsAllowed(Cart $cart, string $countryIso): bool
    {
        if (empty($cart->id) || empty($cart->id_carrier)) {
            return false;
        }

        $carrier = new Carrier($cart->id_carrier);

        if (empty($carrier->id)) {
            return false;
        }

        $carrierPackageTypes = $this->getCarrierPackageTypes($carrier, $countryIso);
        if (empty($carrierPackageTypes)) {
            return false;
        }

        // If only parcel type is set then return true
        if (1 === count($carrierPackageTypes) && $carrierPackageTypes[0] === Constant::PACKAGE_TYPE_PACKAGE) {
            return true;
        }

        $productsPackageTypes = $this->getProductsPackageTypes($cart);
        if (empty($productsPackageTypes)) {
            return true;
        }

        // 1. At least 1 product in cart is of type parcel, regardless of weight: order is considered parcel
        if (in_array(Constant::PACKAGE_TYPE_PACKAGE, $productsPackageTypes, true)) {
            return true; // delivery options
        }

        // 2. Only products in cart of type letter, regardless of total weight: order is considered letter
        if (1 === count($productsPackageTypes)
            && in_array(
                Constant::PACKAGE_TYPE_LETTER,
                $productsPackageTypes,
                true
            )) {
            return false; // no delivery options
        }

        return true;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderPackageType(Order $order): int
    {
        $packageTypes = array_unique($this->getOrderProductsPackageTypes($order->getId()));

        if (! empty($packageTypes)) {
            $cart   = new Cart($order->getIdCart());
            $weight = $cart->getTotalWeight();

            return $this->getProductsPackageType($packageTypes, $weight);
        }

        $packageType = (int) CarrierConfigurationProvider::get(
            $order->getIdCarrier(),
            Constant::PACKAGE_TYPE_CONFIGURATION_NAME
        );

        return $packageType ?: 1;
    }

    private function getCarrierPackageTypes(Carrier $carrier, string $countryIso): array
    {
        $exclusiveField = new ExclusiveField();
        $carrierType    = $exclusiveField->getCarrierType($carrier);
        $packageTypes   = [];
        foreach (AbstractConsignment::PACKAGE_TYPES_IDS as $packageType) {
            if ($exclusiveField->isAvailable(
                $countryIso,
                $carrierType,
                Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
                $packageType
            )) {
                $packageTypes[] = $packageType;
            }
        }

        return $packageTypes;
    }

    private function getOrderProductsPackageTypes(int $id_order): array
    {
        $result        = $this->getOrderProductsConfiguration($id_order);
        $package_types = [];
        foreach ($result as $item) {
            if ('MYPARCELBE_PACKAGE_TYPE' === $item['name'] && $item['value']) {
                $package_types[$item['id_product']] = (int) $item['value'];
            }
        }

        return $package_types;
    }

    /**
     * @param  array $productsPackageTypes
     * @param        $weight
     *
     * @return int
     */
    private function getProductsPackageType(array $productsPackageTypes, $weight): int
    {
        // 1. At least 1 product in cart is of type parcel, regardless of weight: order is considered parcel
        if (in_array(Constant::PACKAGE_TYPE_PACKAGE, $productsPackageTypes)) {
            return Constant::PACKAGE_TYPE_PACKAGE;
        }

        // 2. Only products in cart of type letter, regardless of total weight: order is considered letter
        if (1 === count($productsPackageTypes) && in_array(Constant::PACKAGE_TYPE_LETTER, $productsPackageTypes)) {
            return Constant::PACKAGE_TYPE_LETTER;
        }

        // 3. Total weight is above 2 Kg, regardless of package types, order is considered parcel
        if ($weight >= Constant::PACKAGE_TYPE_WEIGHT_LIMIT) {
            return Constant::PACKAGE_TYPE_PACKAGE;
        }

        // 4. At least 1 product in cart is of type mailbox package AND total weight is less than 2 Kg: order is considered mailbox package
        if (in_array(Constant::PACKAGE_TYPE_MAILBOX, $productsPackageTypes)) {
            return Constant::PACKAGE_TYPE_MAILBOX;
        }

        // 5. At least 1 product in cart is of type digital stamp AND total weight is less than 2 Kg: order is considered digital stamp
        if (in_array(Constant::PACKAGE_TYPE_DIGITAL_STAMP, $productsPackageTypes)) {
            return Constant::PACKAGE_TYPE_DIGITAL_STAMP;
        }

        // Fall back to Package
        return Constant::PACKAGE_TYPE_PACKAGE;
    }

    /**
     * @param  \Cart $cart
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getProductsPackageTypes(Cart $cart): array
    {
        $products = $cart->getProducts();

        if (empty($products)) {
            return [];
        }

        $types = [];

        foreach ($products as $product) {
            $type = (int) ProductConfigurationProvider::get(
                (int) $product['id_product'],
                Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
                Constant::PACKAGE_TYPE_PACKAGE
            );
            if (! in_array($type, $types)) {
                $types[] = $type;
            }
        }

        return $types;
    }
}

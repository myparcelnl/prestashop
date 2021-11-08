<?php

namespace Gett\MyparcelBE\Carrier;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;

class PackageFormatCalculator extends AbstractPackageCalculator
{
    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    public function getOrderPackageFormat(Order $order): int
    {
        $productPackageFormats = array_unique($this->getOrderProductsPackageFormats($order));
        $largePackageTypeIndex = Constant::PACKAGE_FORMAT_LARGE_INDEX;

        if ($productPackageFormats) {
            if (isset($productPackageFormats[$largePackageTypeIndex])) {
                return $largePackageTypeIndex;
            }

            return min($productPackageFormats);
        }

        $packageFormat = CarrierConfigurationProvider::get(
            $order->getIdCarrier(),
            Constant::PACKAGE_FORMAT_CONFIGURATION_NAME
        );

        return $packageFormat ?: 1;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     */
    private function getOrderProductsPackageFormats(Order $order): array
    {
        $result         = $this->getOrderProductsConfiguration($order->getId());
        $packageFormats = [];

        foreach ($result as $item) {
            if (Constant::PACKAGE_FORMAT_CONFIGURATION_NAME === $item['name'] && $item['value']) {
                $packageFormats[$item['id_product']] = (int) $item['value'];
            }
        }

        return $packageFormats;
    }
}

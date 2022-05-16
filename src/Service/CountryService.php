<?php

namespace Gett\MyparcelBE\Service;

use Country;
use Gett\MyparcelBE\Entity\Cache;
use Gett\MyparcelBE\Model\Core\Order;
use MyParcelBE;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use PrestaShop\PrestaShop\Adapter\Entity\Address;
use Throwable;

class CountryService
{
    /**
     * @param \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return string
     */
    public static function getShippingCountryIso2(Order $order): ?string
    {
        try {
            $countryId = (new Address($order->id_address_delivery))->id_country;
        } catch (Throwable $e) {
            return null;
        }

        return Cache::remember(
            'myparcelbe_country_iso2_' . $countryId,
            static function () use ($countryId) {
                return Country::getIsoById($countryId);
            }
        );
    }

    /**
     * @param \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return bool
     */
    public static function isPostNLShipmentFromNLToBE(AbstractConsignment $consignment): bool
    {
        return AbstractConsignment::CC_BE === $consignment->getCountry()
            && MyParcelBE::getModule()->isNL()
            && CarrierPostNL::NAME === $consignment->getCarrierName();
    }
}

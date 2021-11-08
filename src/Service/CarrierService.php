<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Carrier;
use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use Validate;

class CarrierService
{
    /**
     * @param  int $carrierId
     *
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     * @throws \Exception
     */
    public static function getMyParcelCarrier(int $carrierId): AbstractCarrier
    {
        return CarrierFactory::createFromId(self::getMyParcelCarrierId($carrierId));
    }

    /**
     * @param  int $carrierId
     *
     * @return int
     * @throws \Exception
     */
    public static function getMyParcelCarrierId(int $carrierId): int
    {
        $carrier = new Carrier($carrierId);

        if (! Validate::isLoadedObject($carrier)) {
            throw new Exception('No carrier found.');
        }

        $carrierType = CarrierConfigurationProvider::get($carrierId, 'carrierType');

        if ($carrierType === Constant::POSTNL_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::POSTNL_CONFIGURATION_NAME)) {
            return PostNLConsignment::CARRIER_ID;
        }

        if ($carrierType === Constant::BPOST_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::BPOST_CONFIGURATION_NAME)) {
            return BpostConsignment::CARRIER_ID;
        }

        if ($carrierType === Constant::DPD_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::DPD_CONFIGURATION_NAME)) {
            return DPDConsignment::CARRIER_ID;
        }

        throw new Exception('No carrier found for id ' . $carrierId);
    }
}

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Carrier;
use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\FileLogger;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
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
            return CarrierPostNL::ID;
        }

        if ($carrierType === Constant::BPOST_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::BPOST_CONFIGURATION_NAME)) {
            return CarrierBpost::ID;
        }

        if ($carrierType === Constant::DPD_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::DPD_CONFIGURATION_NAME)) {
            return CarrierDPD::ID;
        }

        FileLogger::addLog('Falling back to default carrier.');
        return PlatformServiceFactory::create()
            ->getDefaultCarrier()
            ->getId();
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $carrier
     *
     * @return null|int
     * @throws \PrestaShopDatabaseException
     */
    public static function getPrestashopCarrierId(AbstractCarrier $carrier): ?int
    {
        $matchingCarrier = CarrierConfigurationProvider::all()
            ->where(CarrierConfigurationProvider::COLUMN_NAME, Constant::CARRIER_CONFIGURATION_FIELD_CARRIER_TYPE)
            ->where(CarrierConfigurationProvider::COLUMN_VALUE, $carrier->getName())
            ->first();

        return $matchingCarrier['id_carrier'] ? (int) $matchingCarrier['id_carrier'] : null;
    }
}

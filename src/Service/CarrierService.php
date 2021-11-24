<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Carrier;
use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use Validate;

class CarrierService
{
    /**
     * @return int[]
     */
    public static function carriersWithMyParcel(): array
    {
        return array_map(
            static function (string $carrierName) {
                return (int) Configuration::get($carrierName);
            },
            self::getMyParcelCarrierNames()
        );
    }

    /**
     * @param  int $psCarrierId
     *
     * @return \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier
     * @throws \Exception
     */
    public static function getMyParcelCarrier(int $psCarrierId): AbstractCarrier
    {
        $carrierId = self::getMyParcelCarrierId($psCarrierId)
            ?? PlatformServiceFactory::create()
                ->getDefaultCarrier()
                ->getId();

        return CarrierFactory::createFromId($carrierId);
    }

    /**
     * @return string[]
     */
    public static function getMyParcelCarrierNames(): array
    {
        return array_map(
            static function (string $carrierClass) {
                return $carrierClass::NAME;
            },
            CarrierFactory::CARRIER_CLASSES
        );
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

    /**
     * @param  int $psCarrierId
     *
     * @return bool
     * @throws \Exception
     */
    public static function hasMyParcelCarrier(int $psCarrierId): bool
    {
        return (bool) self::getMyParcelCarrierId($psCarrierId);
    }

    /**
     * @param  int $psCarrierId
     *
     * @return null|int
     * @throws \Exception
     */
    private static function getMyParcelCarrierId(int $psCarrierId): ?int
    {
        $carrier = new Carrier($psCarrierId);

        if (! Validate::isLoadedObject($carrier)) {
            Logger::addLog("PrestaShop carrier with id $psCarrierId could not be found");
            return null;
        }

        $carrierType = CarrierConfigurationProvider::get($psCarrierId, 'carrierType');

        foreach (Constant::CARRIER_CONFIGURATION_MAP as $myParcelCarrier => $configuration) {
            if (
                $carrierType === $myParcelCarrier::NAME
                || $carrier->id_reference === (int) Configuration::get($configuration)
            ) {
                return $myParcelCarrier::ID;
            }
        }

        return null;
    }
}

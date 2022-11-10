<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Carrier;
use Configuration;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Service\Platform\PlatformServiceFactory;
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
     * @param  string|int|class-string|\MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $myParcelCarrier
     *
     * @return null|int
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     * @deprecated this cannot work reliably, since you can link a MyParcel carrier to several Prestashop carriers
     */
    public static function getPrestaShopCarrierId($myParcelCarrier): ?int
    {
        $carrierClass    = CarrierFactory::create($myParcelCarrier);
        $matchingCarrier = CarrierConfigurationProvider::all()
            ->where(CarrierConfigurationProvider::COLUMN_NAME, Constant::CARRIER_CONFIGURATION_FIELD_CARRIER_TYPE)
            ->where(CarrierConfigurationProvider::COLUMN_VALUE, $carrierClass->getName())
            ->first();

        return $matchingCarrier['id_carrier'] ?? null
                ? (int) $matchingCarrier['id_carrier']
                : null;
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

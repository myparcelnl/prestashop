<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Carrier\Provider;

use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Service\CarrierService;

class CarrierSettingsProvider
{
    /**
     * @param  int $carrierId
     *
     * @return array
     * @throws \Exception
     */
    public function provide(int $carrierId): array
    {
        $carrier = CarrierService::getMyParcelCarrier($carrierId);

        $countryIso      = ModuleService::getModuleCountry();
        $carrierSettings = Constant::CARRIER_EXCLUSIVE[strtoupper($carrier->getName())];

        $carrierLabelSettings = [
            'delivery' => [],
            'return'   => [],
        ];

        foreach (Constant::SINGLE_LABEL_CREATION_OPTIONS as $key => $field) {
            $carrierLabelSettings['delivery'][$key] = $carrierSettings[$field][$countryIso];
            $carrierLabelSettings['return'][$key]   = $carrierSettings['return_' . $field][$countryIso];
        }

        $carrierLabelSettings['delivery']['ALLOW_FORM'] = $carrierSettings['ALLOW_DELIVERY_FORM'][$countryIso];
        $carrierLabelSettings['return']['ALLOW_FORM']   = $carrierSettings['ALLOW_RETURN_FORM'][$countryIso];

        return $carrierLabelSettings;
    }
}

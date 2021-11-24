<?php

namespace Gett\MyparcelBE\Module\Carrier\Provider;

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Service\CarrierService;
use Module;
use MyParcelBE;

class CarrierSettingsProvider
{
    protected $module;

    /**
     * @param  \Module|null $module
     *
     * @throws \Exception
     */
    public function __construct(Module $module = null)
    {
        $this->module = $module ?? MyParcelBE::getModule();
    }

    /**
     * @param  int $carrierId
     *
     * @return array
     * @throws \Exception
     */
    public function provide(int $carrierId): array
    {
        $carrier = CarrierService::getMyParcelCarrier($carrierId);

        $countryIso      = $this->module->getModuleCountry();
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

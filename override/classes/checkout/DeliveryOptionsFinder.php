<?php

use Gett\MyparcelBE\Service\CarrierConfigurationProvider;

class DeliveryOptionsFinder extends DeliveryOptionsFinderCore
{
    public function getDeliveryOptions()
    {
        $carriers_available = parent::getDeliveryOptions();
        $module = Module::getInstanceByName('myparcelbe');

        foreach ($carriers_available as $key => $carrier) {
            $carrierId = $carrier['id'];

            $configurationPsCarriers = CarrierConfigurationProvider::get($carrierId, 'carrierType');

            if(!is_null($configurationPsCarriers) && empty($carrier['extraContent'])) {
                $carriers_available[$key]['extraContent'] = Hook::exec('displayCarrierExtraContent', ['carrier' => $carrier], $module->id);
            }
        }
        return $carriers_available;
    }
}
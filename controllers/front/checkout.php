<?php

use Gett\MyparcelNL\Constant;
use Gett\MyparcelNL\Service\CarrierConfigurationProvider;
use Gett\MyparcelNL\Service\DeliverySettingsProvider;

class MyParcelNLCheckoutModuleFrontController extends ModuleFrontController
{
    public $requestOriginalShippingCost = false;

    public function postProcess()
    {
        $id_carrier = intval(Tools::getValue('id_carrier'));
        $params = (new DeliverySettingsProvider($this->module, $id_carrier, $this->context))->get();

        echo json_encode($params);
        exit;
    }
}

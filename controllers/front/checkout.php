<?php

use Gett\MyparcelBE\Service\DeliverySettingsProvider;

class MyParcelBECheckoutModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool
     */
    public $requestOriginalShippingCost = false;

    /**
     * Called when doing a request to `myparcel_carrier_init_url` from frontend.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    public function postProcess(): void
    {
        $carriers  = [];
        $carrierId = (int) Tools::getValue('carrier_id');

        if ($carrierId) {
            $carriers = [$carrierId];
        }

        $params = (new DeliverySettingsProvider($this->module, $carriers, $this->context))->get();

        echo json_encode($params);
        exit;
    }
}

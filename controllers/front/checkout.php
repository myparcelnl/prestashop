<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Service\DeliveryOptionsConfigProvider;

class MyParcelNLCheckoutModuleFrontController extends ModuleFrontController
{
    /**
     * @var bool
     */
    public $requestOriginalShippingCost = false;

    /**
     * Called when doing a request to MyParcelActions.deliveryOptionsUrl from frontend.
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function postProcess(): bool
    {
        $postValues = Tools::getAllValues();

        $psCarrierId = $postValues['carrier'] ?? $postValues['carrierId'] ?? $postValues['carrier_id'] ?? null;
        $addressId   = $postValues['addressId'] ?? $postValues['address_id'] ?? null;

        $params = (new DeliveryOptionsConfigProvider($this->context, $psCarrierId))->get($addressId ? (int) $addressId : null);

        echo json_encode(['data' => $params]);
        return true;
    }
}

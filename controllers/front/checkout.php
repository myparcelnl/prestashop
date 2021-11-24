<?php

declare(strict_types=1);

use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\DeliverySettingsProvider;
use MyParcelNL\Sdk\src\Support\Arr;

class MyParcelBECheckoutModuleFrontController extends ModuleFrontController
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

        $carriers  = [];
        $carrier   = $postValues['carrier'] ?? $postValues['carrierId'] ?? $postValues['carrier_id'] ?? null;
        $addressId = $postValues['addressId'] ?? $postValues['address_id'] ?? null;

        if ($carrier) {
            $carriers = [$carrier];
        }

        if (! empty($carriers)) {
            $carriers = array_map(static function (string $psCarrierId) {
                return CarrierService::getMyParcelCarrier((int) $psCarrierId)->getName();
            }, $carriers);
        }

        $params = (new DeliverySettingsProvider($this->context, $carriers))->get($addressId ? (int) $addressId : null);

        echo json_encode(['data' => $params]);
        return true;
    }
}

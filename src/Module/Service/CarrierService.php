<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Service;

use Db;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;

class CarrierService
{
    private function getMyParcelCarrier(int $psCarrierId): Carrier
    {
        $db          = Db::getInstance();
        $carrierName = $db->getValue(
            "SELECT `myparcel_carrier` from `ps_myparcelnl_carrier_configuration` WHERE `ps_carrier_id` = '${psCarrierId}'"
        );

        if (! $carrierName) {
            // TODO: throw/log error when operation fails
            return Pdk::get('defaultCarrier');
        }

        return new Carrier(['name' => $carrierName]);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Adapter;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;

final class PsCarrierAdapter
{
    /**
     * @param  int $psCarrierId
     *
     * @return mixed|string
     */
    public function getCarrierName(int $psCarrierId)
    {
        /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $carrierRepository */
        $carrierRepository = Pdk::get(PsCarrierMappingRepository::class);
        $found             = $carrierRepository->findOneBy(['idCarrier' => $psCarrierId]);

        if ($found) {
            return $found->getMyparcelCarrier();
        }

        return Platform::get('defaultCarrier');
    }
}

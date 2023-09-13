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
     * @return string
     */
    public function getCarrierName(int $psCarrierId): string
    {
        /** @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository $carrierRepository */
        $carrierRepository = Pdk::get(PsCarrierMappingRepository::class);
        $found             = $carrierRepository->findOneBy(['id_carrier' => $psCarrierId]);

        return $found->myparcelCarrier ?? Platform::get('defaultCarrier');
    }
}

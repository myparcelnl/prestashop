<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

interface PsCarrierServiceInterface
{
    /**
     * @return void
     */
    public function deleteCarriers(): void;

    /**
     * @return void
     */
    public function updateCarriers(): void;
}

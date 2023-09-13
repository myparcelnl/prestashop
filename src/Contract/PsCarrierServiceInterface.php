<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

interface PsCarrierServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection $carriers
     *
     * @return void
     */
    public function createOrUpdateCarriers(CarrierCollection $carriers): void;

    /**
     * @return void
     */
    public function deleteCarriers(): void;

    /**
     * @param $carriers
     *
     * @return void
     */
    public function deleteUnusedCarriers($carriers): void;

    /**
     * @param  int|PsCarrier $input
     *
     * @return PsCarrier
     */
    public function get($input): PsCarrier;

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|int|\Carrier
     */
    public function getId($input): int;

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|\MyParcelNL\Pdk\Carrier\Model\Carrier
     */
    public function getMyParcelCarrier($input): ?Carrier;

    /**
     * @param  int|PsCarrier $input
     *
     * @return null|string
     */
    public function getMyParcelCarrierIdentifier($input): ?string;

    /**
     * @param  int|PsCarrier $input
     *
     * @return bool
     */
    public function isMyParcelCarrier($input): bool;

    /**
     * @return void
     */
    public function updateCarriers(): void;
}

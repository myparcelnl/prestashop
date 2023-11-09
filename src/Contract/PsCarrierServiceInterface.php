<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Contract;

use Carrier as PsCarrier;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

/**
 * @extends \MyParcelNL\PrestaShop\Contract\PsSpecificObjectModelServiceInterface<PsCarrier>
 */
interface PsCarrierServiceInterface extends PsSpecificObjectModelServiceInterface
{
    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    public function carrierIsActive(Carrier $carrier): bool;

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Collection\CarrierCollection $carriers
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function createOrUpdateCarriers(CarrierCollection $carriers): Collection;

    /**
     * @param  int $reference
     *
     * @return null|PsCarrier
     */
    public function getByReference(int $reference): ?PsCarrier;

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
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $myParcelCarrier
     *
     * @return null|\Carrier
     */
    public function getPsCarrier(Carrier $myParcelCarrier): ?PsCarrier;

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getPsCarriers(): Collection;

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

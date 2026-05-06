<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * Note: legacy names are currently used for storage but should be replaced with CONSTANT_CASE and migration in the future.
 *
 * @method $this withCarrierId(int $carrierId)
 * @method $this withMyparcelCarrier(string $myparcelCarrier)
 */
final class MyparcelnlCarrierMappingFactory extends AbstractPsEntityFactory
{
    public function fromBpost(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::BPOST, $contractId);
    }

    public function fromDhlEuroplus(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_EUROPLUS, $contractId);
    }

    public function fromDhlForYou(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_FOR_YOU, $contractId);
    }

    public function fromDhlParcelConnect(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::DHL_PARCEL_CONNECT, $contractId);
    }

    public function fromDpd(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::DPD, $contractId);
    }

    public function fromPostNL(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::POSTNL, $contractId);
    }

    public function fromUps(int $contractId = null): self
    {
        return $this->fromCarrier(RefCapabilitiesSharedCarrierV2::UPS_STANDARD, $contractId);
    }

    protected function getEntityClass(): string
    {
        return MyparcelnlCarrierMapping::class;
    }

    /**
     * @param  string   $name
     * @param  null|int $contractId
     *
     * @return $this
     */
    private function fromCarrier(string $name, ?int $contractId): self
    {
        $identifier = $contractId ? "$name:$contractId" : $name;

        return $this->withMyparcelCarrier($identifier);
    }
}

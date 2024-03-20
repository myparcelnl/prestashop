<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method $this withCarrierId(int $carrierId)
 * @method $this withMyparcelCarrier(string $myparcelCarrier)
 */
final class MyparcelnlCarrierMappingFactory extends AbstractPsEntityFactory
{
    public function fromBpost(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_BPOST_NAME, $contractId);
    }

    public function fromDhlEuroplus(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME, $contractId);
    }

    public function fromDhlForYou(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME, $contractId);
    }

    public function fromDhlParcelConnect(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME, $contractId);
    }

    public function fromDpd(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DPD_NAME, $contractId);
    }

    public function fromPostNL(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_POSTNL_NAME, $contractId);
    }

    public function fromUps(int $contractId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_UPS_NAME, $contractId);
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

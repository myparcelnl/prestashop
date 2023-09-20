<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withCarrierId(int $carrierId)
 * @method self withMyparcelCarrier(string $myparcelCarrier)
 */
final class MyparcelnlCarrierMappingFactory extends AbstractPsEntityFactory
{
    public function fromBpost(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_BPOST_NAME, $subscriptionId);
    }

    public function fromDhlEuroplus(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_EUROPLUS_NAME, $subscriptionId);
    }

    public function fromDhlForYou(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME, $subscriptionId);
    }

    public function fromDhlParcelConnect(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME, $subscriptionId);
    }

    public function fromDpd(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_DPD_NAME, $subscriptionId);
    }

    public function fromPostNL(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_POSTNL_NAME, $subscriptionId);
    }

    public function fromUps(int $subscriptionId = null): self
    {
        return $this->fromCarrier(Carrier::CARRIER_UPS_NAME, $subscriptionId);
    }

    protected function getEntityClass(): string
    {
        return MyparcelnlCarrierMapping::class;
    }

    /**
     * @param  string   $name
     * @param  null|int $subscriptionId
     *
     * @return $this
     */
    private function fromCarrier(string $name, ?int $subscriptionId): self
    {
        $identifier = $subscriptionId ? "$name:$subscriptionId" : $name;

        return $this->withMyparcelCarrier($identifier);
    }
}

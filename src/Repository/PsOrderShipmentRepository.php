<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlOrderShipment>
 */
final class PsOrderShipmentRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderShipment::class;

    /**
     * A shipment is identified by its shipmentId (the entity's primary key), so the inherited
     * findAll() matches against that. Note: fetching the shipments that belong to a set of orders is
     * a foreign-key lookup, not findAll() — use where('orderId', $orderIds) for that.
     *
     * @return string
     */
    protected function getIdentifierColumn(): string
    {
        return 'shipmentId';
    }
}

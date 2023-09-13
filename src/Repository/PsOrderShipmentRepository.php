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
}

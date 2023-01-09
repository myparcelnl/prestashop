<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;

/**
 * @see \MyParcelNL\PrestaShop\Boot::resolvePrestaShopRepositories()
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment
 */
class PsOrderShipmentRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderShipment::class;
}

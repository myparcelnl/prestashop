<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment
 * @method null|MyparcelnlOrderShipment findOneBy(array $criteria)
 * @method null|MyparcelnlOrderShipment firstWhere(string $key, $value)
 * @method null|MyparcelnlOrderShipment find($id)
 * @method Collection|MyparcelnlOrderShipment[] findAll()
 */
class PsOrderShipmentRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderShipment::class;
}

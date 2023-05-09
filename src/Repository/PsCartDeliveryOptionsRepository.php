<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions
 * @method null|MyparcelnlCartDeliveryOptions findOneBy(array $criteria)
 * @method null|MyparcelnlCartDeliveryOptions firstWhere(string $key, $value)
 * @method null|MyparcelnlCartDeliveryOptions find($id)
 * @method Collection|MyparcelnlCartDeliveryOptions[] findAll()
 */
class PsCartDeliveryOptionsRepository extends
    AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCartDeliveryOptions::class;
}

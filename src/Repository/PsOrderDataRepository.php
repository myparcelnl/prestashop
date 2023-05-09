<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @template-covariant T of MyparcelnlOrderData
 * @see                \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 * @method null|MyparcelnlOrderData findOneBy(array $criteria)
 * @method null|MyparcelnlOrderData firstWhere(string $key, $value)
 * @method null|MyparcelnlOrderData find($id)
 * @method Collection|MyparcelnlOrderData[] findAll()
 */
class PsOrderDataRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderData::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping
 * @method null|MyparcelnlCarrierMapping findOneBy(array $criteria)
 * @method null|MyparcelnlCarrierMapping firstWhere(string $key, $value)
 * @method null|MyparcelnlCarrierMapping find($id)
 * @method Collection|MyparcelnlCarrierMapping[] findAll()
 */
class PsCarrierMappingRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCarrierMapping::class;
}

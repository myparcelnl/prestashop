<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration
 * @method null|MyparcelnlCarrierConfiguration findOneBy(array $criteria)
 * @method null|MyparcelnlCarrierConfiguration firstWhere(string $key, $value)
 * @method null|MyparcelnlCarrierConfiguration find($id)
 * @method Collection|MyparcelnlCarrierConfiguration[] findAll()
 */
class PsCarrierConfigurationRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCarrierConfiguration::class;
}

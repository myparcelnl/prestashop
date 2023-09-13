<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierMapping;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlCarrierMapping>
 */
final class PsCarrierMappingRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCarrierMapping::class;
}

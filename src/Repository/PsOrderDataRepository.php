<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlOrderData>
 */
final class PsOrderDataRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderData::class;
}

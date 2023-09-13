<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlCartDeliveryOptions>
 */
final class PsCartDeliveryOptionsRepository extends
    AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCartDeliveryOptions::class;
}

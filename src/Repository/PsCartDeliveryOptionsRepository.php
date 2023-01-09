<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions;

/**
 * @see \MyParcelNL\PrestaShop\Boot::resolvePrestaShopRepositories()
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCartDeliveryOptions
 */
class PsCartDeliveryOptionsRepository extends
    AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCartDeliveryOptions::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration;

/**
 * @see \MyParcelNL\PrestaShop\Boot::resolvePrestaShopRepositories()
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlCarrierConfiguration
 */
class PsCarrierConfigurationRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlCarrierConfiguration::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;

/**
 * @see \MyParcelNL\PrestaShop\Boot::resolvePrestaShopRepositories()
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings
 */
class PsProductSettingsRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlProductSettings::class;
}

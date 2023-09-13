<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;

/**
 * @extends AbstractPsObjectRepository<MyparcelnlProductSettings>
 */
final class PsProductSettingsRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlProductSettings::class;
}

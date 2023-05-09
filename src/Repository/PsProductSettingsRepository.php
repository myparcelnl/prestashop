<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;

/**
 * @see \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings
 * @method null|MyparcelnlProductSettings findOneBy(array $criteria)
 * @method null|MyparcelnlProductSettings firstWhere(string $key, $value)
 * @method null|MyparcelnlProductSettings find($id)
 * @method Collection|MyparcelnlProductSettings[] findAll()
 */
class PsProductSettingsRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlProductSettings::class;
}

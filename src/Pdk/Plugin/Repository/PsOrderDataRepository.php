<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;

/**
 * @template-covariant T of MyparcelnlOrderData
 * @see                \MyParcelNL\PrestaShop\Boot::resolvePrestaShopRepositories()
 * @see                \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData
 */
class PsOrderDataRepository extends AbstractPsObjectRepository
{
    protected $entity = MyparcelnlOrderData::class;
}

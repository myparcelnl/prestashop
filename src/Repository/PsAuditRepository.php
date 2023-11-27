<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Repository;

use MyParcelNL\PrestaShop\Entity\MyparcelnlAudit;

/**
 * @extends AbstractPsObjectRepository<\MyParcelNL\PrestaShop\Entity\MyparcelnlAudit>
 */
final class PsAuditRepository extends
    AbstractPsObjectRepository
{
    protected $entity = MyparcelnlAudit::class;
}

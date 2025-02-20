<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Audit\Repository;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlAudit;
use MyParcelNL\PrestaShop\Repository\PsAuditRepository;

/**
 * @deprecated This class is deprecated and will be removed in the next major release.
 */
final class PsPdkAuditRepository extends Repository implements PdkAuditRepositoryInterface
{
    /**
     * @deprecated This method is a no-op, retained for compatibility only.
     */
    public function all(): AuditCollection
    {
        return new AuditCollection([]);
    }

    /**
     * @deprecated This method is a no-op, retained for compatibility only.
     * @throws \Doctrine\ORM\ORMException
     */
    public function store(Audit $audit): void
    {
        // no-op
    }

    protected function getKeyPrefix(): string
    {
        return Audit::class;
    }
}

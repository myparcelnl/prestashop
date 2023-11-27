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

final class PsPdkAuditRepository extends Repository implements PdkAuditRepositoryInterface
{
    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsAuditRepository
     */
    private $psAuditRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface   $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsAuditRepository $psAuditRepository
     */
    public function __construct(StorageInterface $storage, PsAuditRepository $psAuditRepository)
    {
        parent::__construct($storage);
        $this->psAuditRepository = $psAuditRepository;
    }

    public function all(): AuditCollection
    {
        return new AuditCollection(
            $this->psAuditRepository->all()
                ->map(function (MyparcelnlAudit $audit) {
                    return [
                        'id'              => $audit->getId(),
                        'action'          => $audit->getAction(),
                        'model'           => $audit->getModel(),
                        'modelIdentifier' => $audit->getModelIdentifier(),
                        'arguments'       => $audit->getData(),
                        'created'         => $audit->getDateAdd(),
                    ];
                })
        );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function store(Audit $audit): void
    {
        $this->psAuditRepository->create([
            'id'              => $audit->id,
            'action'          => $audit->action,
            'model'           => $audit->model,
            'modelIdentifier' => $audit->modelIdentifier,
            'data'            => json_encode($audit->arguments),
            'dateAdd'         => $audit->created,
        ]);

        $this->save($audit->id ?? '', $audit);
    }

    protected function getKeyPrefix(): string
    {
        return Audit::class;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Throwable;

final class PsEntityManagerService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct()
    {
        $this->entityManager = Pdk::get('ps.entityManager');
    }

    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Throwable
     * @throws \Doctrine\ORM\Exception\ORMException
     */
    public function flush(): void
    {
        if (! $this->entityManager->isOpen()) {
            Logger::info('Entity manager is closed');

            return;
        }

        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            Logger::error('Failed to flush entity manager', ['exception' => $e]);

            throw $e;
        }
    }
}

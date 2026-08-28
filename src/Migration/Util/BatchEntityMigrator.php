<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Repository\AbstractPsObjectRepository;
use Throwable;

/**
 * Walks a repository in batches so a migration can touch every row without exhausting memory or time.
 *
 * PrestaShop has no working scheduler — PsCronService::schedule() is an unimplemented stub — so the work
 * runs inline and stays survivable by being resumable instead. Keyset pagination gives a stable cursor
 * worth persisting, and it is written only once a batch is committed, so a run killed by a timeout
 * resumes where it stopped.
 *
 * Mirrors Migration5_3_0::migrateInBatches() on purpose: that one is private to a migration merchants
 * have already run, and reaching into it risks changing behaviour for shops mid-upgrade.
 */
class BatchEntityMigrator
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(PdkSettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * Hand every row of $repository to $callback, in committed batches, resuming from a stored cursor.
     *
     * The callback receives one entity and may mutate it; flushing is handled here. Return values are
     * ignored, so a callback that decides a row needs no change can simply return. A row whose callback
     * throws is logged and left unchanged, and the cursor still advances past it, so one unparseable
     * record cannot block the migration forever.
     *
     * @param  AbstractPsObjectRepository $repository
     * @param  string                     $idField    Doctrine identifier field, e.g. "orderId"
     * @param  string                     $cursorName Unique per migration, so two cannot read each
     *                                                other's progress
     * @param  callable                   $callback   fn(object $entity): void
     * @param  int                        $batchSize
     *
     * @return int The number of rows visited
     */
    public function each(
        AbstractPsObjectRepository $repository,
        string                     $idField,
        string                     $cursorName,
        callable                   $callback,
        int                        $batchSize = 500
    ): int {
        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Pdk::get('ps.entityManager');
        $cursorKey     = $this->cursorKey($cursorName);
        $idGetter      = 'get' . ucfirst($idField);
        $cursor        = (int) $this->settingsRepository->get($cursorKey);
        $visited       = 0;

        do {
            $batch = $entityManager->getRepository($repository->getEntityClass())
                ->createQueryBuilder('e')
                ->where(sprintf('e.%s > :cursor', $idField))
                ->setParameter('cursor', $cursor)
                ->orderBy(sprintf('e.%s', $idField), 'ASC')
                ->setMaxResults($batchSize)
                ->getQuery()
                ->getResult();

            foreach ($batch as $entity) {
                try {
                    $callback($entity);
                } catch (Throwable $exception) {
                    // Drop it from the unit of work, so whatever the callback changed before throwing is
                    // not flushed with the rest of the batch. Skipped has to mean unchanged.
                    $entityManager->detach($entity);

                    Logger::warning('Skipping entity during migration', [
                        'entity' => get_class($entity),
                        'cursor' => $cursorName,
                        'error'  => $exception->getMessage(),
                    ]);
                }

                // Advance regardless of the callback's outcome, so a poison row is not retried forever.
                $cursor = $entity->{$idGetter}();
                $visited++;
            }

            EntityManager::flush();
            $entityManager->clear();

            // Persist progress only once the batch is committed; a resumed run picks up from here.
            $this->settingsRepository->store($cursorKey, $cursor);
        } while (count($batch) === $batchSize);

        return $visited;
    }

    /**
     * Forget a cursor, so a later migration cannot skip rows because of a stale one.
     */
    public function clearCursor(string $cursorName): void
    {
        $this->settingsRepository->store($this->cursorKey($cursorName), null);
    }

    private function cursorKey(string $cursorName): string
    {
        return Pdk::get('createSettingsKey')('batch_entity_migrator_cursor_' . $cursorName);
    }
}

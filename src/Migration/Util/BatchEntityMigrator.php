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
 * PrestaShop has no working scheduler — PsCronService::schedule() is an unimplemented stub — so a
 * migration that has to visit every order cannot hand the work to cron the way the WooCommerce plugin
 * does. It runs inline instead, and stays survivable by being resumable.
 *
 * Keyset pagination (`WHERE id > :cursor ORDER BY id`) is used rather than OFFSET, both to avoid the
 * deep-offset scan cost on large tables and to give a stable cursor worth persisting. The cursor is
 * written only after a batch is committed, so it can never point past uncommitted data, and a run killed
 * by a timeout resumes where it stopped rather than starting over.
 *
 * A row whose callback throws is logged and skipped, and the cursor still advances past it, so one
 * unparseable record cannot block the migration forever.
 *
 * This mirrors Migration5_3_0::migrateInBatches(), which is private to a migration that has already run
 * for merchants and is therefore left untouched. The duplication is deliberate: reaching into a released
 * migration to extract a helper risks changing behaviour for shops that are mid-upgrade.
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
     * ignored, so a callback that decides a row needs no change can simply return.
     *
     * @param  AbstractPsObjectRepository $repository
     * @param  string                     $idField    Doctrine identifier field, e.g. "orderId"
     * @param  string                     $cursorName Unique name for the persisted progress cursor
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

    /**
     * The settings key a cursor is stored under.
     *
     * Callers should pass a cursor name that is unique per migration (for example include the migration
     * id), so independent migrations cannot read each other's progress.
     */
    private function cursorKey(string $cursorName): string
    {
        return Pdk::get('createSettingsKey')('batch_entity_migrator_cursor_' . $cursorName);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Migration\Pdk\AbstractPsPdkMigration;
use MyParcelNL\PrestaShop\Repository\AbstractPsObjectRepository;
use MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use Throwable;

final class Migration5_3_0 extends AbstractPsPdkMigration
{
    /**
     * @var \MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface
     */
    private $accountRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCarrierMappingRepository
     */
    private $carrierMappingRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository
     */
    private $cartDeliveryOptionsRepository;

    /**
     * @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository
     */
    private $carrierCapabilitiesRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $orderDataRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(
        PdkSettingsRepositoryInterface    $settingsRepository,
        PsCarrierMappingRepository        $carrierMappingRepository,
        PsCartDeliveryOptionsRepository   $cartDeliveryOptionsRepository,
        PsOrderDataRepository             $orderDataRepository,
        PsOrderShipmentRepository         $orderShipmentRepository,
        PdkAccountRepositoryInterface     $accountRepository,
        CarrierCapabilitiesRepository     $carrierCapabilitiesRepository
    ) {
        parent::__construct();
        $this->settingsRepository            = $settingsRepository;
        $this->carrierMappingRepository      = $carrierMappingRepository;
        $this->cartDeliveryOptionsRepository = $cartDeliveryOptionsRepository;
        $this->orderDataRepository           = $orderDataRepository;
        $this->orderShipmentRepository       = $orderShipmentRepository;
        $this->accountRepository             = $accountRepository;
        $this->carrierCapabilitiesRepository = $carrierCapabilitiesRepository;
    }

    public function getVersion(): string
    {
        return '5.3.0';
    }

    public function up(): void
    {
        $this->migrateAccountData();
        $this->migrateCarrierSettings();
        $this->migrateCarrierMappings();
        $this->migrateCartDeliveryOptions();
        $this->migrateOrderData();
        $this->migrateShipmentData();
        $this->clearCursors();
    }

    /**
     * Re-fetch carrier definitions from the API to update account carrier data.
     * Wrapped in try/catch since it depends on a valid API key being configured.
     */
    private function migrateAccountData(): void
    {
        try {
            $account = $this->accountRepository->getAccount(true);

            if (! $account) {
                return;
            }

            $shop = $account->shops->first();

            $shop->carriers = $this->carrierCapabilitiesRepository->getContractDefinitions();

            $this->accountRepository->store($account);
        } catch (Throwable $e) {
            Logger::warning('Could not refresh account carrier data during migration', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'class'   => get_class($e),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Remap carrier setting keys from legacy lowercase names to V2 SCREAMING_SNAKE_CASE.
     */
    private function migrateCarrierSettings(): void
    {
        $settingsKey     = Pdk::get('createSettingsKey')('carrier');
        $currentSettings = $this->settingsRepository->get($settingsKey);

        if (empty($currentSettings) || ! is_array($currentSettings)) {
            return;
        }

        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        $migratedSettings = [];
        foreach ($currentSettings as $key => $carrierData) {
            $newKey                    = $legacyToNewMap[$key] ?? $key;
            $migratedSettings[$newKey] = $carrierData;
        }

        $this->settingsRepository->store($settingsKey, $migratedSettings);
    }

    /**
     * Update myparcel_carrier column values in the carrier mapping table from legacy to V2 names.
     *
     * The myparcel_carrier column is UNIQUE. A shop that carried both the old (legacy-named) and new
     * (V2-named) PrestaShop carriers across the transition has both rows present, so renaming a legacy
     * row to a V2 name that another row already holds would violate the constraint and abort the whole
     * migration. Such a legacy row is left untouched (and logged) rather than remapped.
     */
    private function migrateCarrierMappings(): void
    {
        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);
        $mappings       = $this->carrierMappingRepository->all();

        $usedNames = [];
        foreach ($mappings as $mapping) {
            $usedNames[$mapping->getMyparcelCarrier()] = true;
        }

        foreach ($mappings as $mapping) {
            $currentName = $mapping->getMyparcelCarrier();
            $newName     = $legacyToNewMap[$currentName] ?? null;

            if (! $newName) {
                continue;
            }

            if (isset($usedNames[$newName])) {
                Logger::warning('Skipping carrier mapping migration; target name already in use', [
                    'from' => $currentName,
                    'to'   => $newName,
                ]);
                continue;
            }

            $mapping->setMyparcelCarrier($newName);
            unset($usedNames[$currentName]);
            $usedNames[$newName] = true;
        }

        EntityManager::flush();
    }

    /**
     * Normalise the carrier field in cart delivery options from legacy formats to V2 SCREAMING_SNAKE_CASE.
     */
    private function migrateCartDeliveryOptions(): void
    {
        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        $this->migrateInBatches($this->cartDeliveryOptionsRepository, 'cartId', 'cart_delivery_options', function ($cartOption) use ($legacyToNewMap) {
            $data   = $cartOption->getData();
            $parsed = $this->parseLegacyCarrier($data['carrier'] ?? null);

            if (! $parsed) {
                return;
            }

            [$legacyName] = $parsed;
            $newName = $legacyToNewMap[$legacyName] ?? $legacyName;

            if ($newName === ($data['carrier'] ?? null)) {
                return;
            }

            $data['carrier'] = $newName;
            $cartOption->setData(json_encode($data));
        });
    }

    /**
     * Normalise the carrier field in order data rows from legacy formats to V2 SCREAMING_SNAKE_CASE.
     */
    private function migrateOrderData(): void
    {
        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        $this->migrateInBatches($this->orderDataRepository, 'orderId', 'order_data', function ($orderData) use ($legacyToNewMap) {
            $data   = $orderData->getData();
            $parsed = $this->parseLegacyCarrier($data['deliveryOptions']['carrier'] ?? null);

            if (! $parsed) {
                return;
            }

            [$legacyName] = $parsed;
            $newName = $legacyToNewMap[$legacyName] ?? $legacyName;

            if ($newName === ($data['deliveryOptions']['carrier'] ?? null)) {
                return;
            }

            $data['deliveryOptions']['carrier'] = $newName;
            $orderData->setData(json_encode($data));
        });
    }

    /**
     * Normalise the carrier field in shipment data rows.
     * Migrates top-level carrier, nested deliveryOptions.carrier, and extracts contractId from :N suffix.
     */
    private function migrateShipmentData(): void
    {
        $legacyToNewMap = array_flip(Carrier::CARRIER_NAME_TO_LEGACY_MAP);

        $this->migrateInBatches($this->orderShipmentRepository, 'shipmentId', 'order_shipment', function ($shipment) use ($legacyToNewMap) {
            $data    = $shipment->getData();
            $changed = false;

            $parsed = $this->parseLegacyCarrier($data['carrier'] ?? null);

            if ($parsed) {
                [$legacyName, $contractId] = $parsed;
                $newName = $legacyToNewMap[$legacyName] ?? $legacyName;

                if ($newName !== ($data['carrier'] ?? null) || ! is_string($data['carrier'] ?? null)) {
                    $data['carrier'] = $newName;
                    $changed         = true;
                }

                if ($contractId && ! isset($data['contractId'])) {
                    $data['contractId'] = $contractId;
                    $changed            = true;
                }
            }

            if (isset($data['deliveryOptions']['carrier'])) {
                $parsedDo = $this->parseLegacyCarrier($data['deliveryOptions']['carrier']);

                if ($parsedDo) {
                    [$doLegacyName] = $parsedDo;
                    $doNewName = $legacyToNewMap[$doLegacyName] ?? $doLegacyName;

                    if ($doNewName !== $data['deliveryOptions']['carrier']) {
                        $data['deliveryOptions']['carrier'] = $doNewName;
                        $changed                            = true;
                    }
                }
            }

            if ($changed) {
                $shipment->setData(json_encode($data));
            }
        });
    }

    /**
     * Process entities from a repository in batches to limit memory usage.
     *
     * Uses keyset pagination on the entity identifier (WHERE id > :cursor ORDER BY id) rather than
     * OFFSET, both to avoid the deep-offset scan cost on large tables and to give a stable cursor to
     * persist. Progress is stored after each flushed batch so that a run interrupted by a timeout on a
     * very large shop resumes where it left off on the next upgrade attempt instead of restarting. The
     * cursor is only ever written after the batch is committed, so it can never point past uncommitted
     * data; combined with the idempotent transforms, re-processing an already-migrated row is harmless.
     *
     * @param  \MyParcelNL\PrestaShop\Repository\AbstractPsObjectRepository $repository
     * @param  string                                                       $idField    Doctrine identifier field, e.g. "orderId"
     * @param  string                                                       $cursorName Unique name for the persisted progress cursor
     * @param  callable                                                     $callback
     * @param  int                                                          $batchSize
     */
    private function migrateInBatches(
        AbstractPsObjectRepository $repository,
        string                     $idField,
        string                     $cursorName,
        callable                   $callback,
        int                        $batchSize = 500
    ): void {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em        = Pdk::get('ps.entityManager');
        $cursorKey = $this->cursorKey($cursorName);
        $idGetter  = 'get' . ucfirst($idField);
        $cursor    = (int) $this->settingsRepository->get($cursorKey);

        do {
            $batch = $em->getRepository($repository->getEntityClass())
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
                } catch (Throwable $e) {
                    Logger::warning('Skipping entity during migration', [
                        'entity' => get_class($entity),
                        'error'  => $e->getMessage(),
                    ]);
                }

                // Advance past this row regardless of callback outcome, so a poison row is not retried forever.
                $cursor = $entity->{$idGetter}();
            }

            EntityManager::flush();
            $em->clear();

            // Persist progress only after the batch is committed; re-runs resume from here.
            $this->settingsRepository->store($cursorKey, $cursor);
        } while (count($batch) === $batchSize);
    }

    /**
     * Build the settings key used to persist a per-table batch-migration progress cursor.
     */
    private function cursorKey(string $cursorName): string
    {
        return Pdk::get('createSettingsKey')('migration_5_3_0_cursor_' . $cursorName);
    }

    /**
     * Remove the persisted progress cursors once the migration has run to completion, so a future
     * migration does not skip rows based on a stale cursor.
     */
    private function clearCursors(): void
    {
        foreach (['cart_delivery_options', 'order_data', 'order_shipment'] as $cursorName) {
            $this->settingsRepository->store($this->cursorKey($cursorName), null);
        }
    }

    /**
     * Extracts the legacy carrier name (and optional contract ID) from the various stored formats.
     * Handles:
     *   - Plain string: "postnl"
     *   - String with contract ID: "postnl:123"
     *   - Object with externalIdentifier: {"externalIdentifier": "postnl"}
     *   - Object with carrier key: {"carrier": "postnl"}
     *
     * @param  mixed $carrier
     *
     * @return null|array [carrierName, contractId|null]
     */
    private function parseLegacyCarrier($carrier): ?array
    {
        if (is_array($carrier)) {
            $raw = $carrier['externalIdentifier'] ?? ($carrier['carrier'] ?? null);
        } elseif (is_string($carrier)) {
            $raw = $carrier;
        } else {
            return null;
        }

        if (! is_string($raw)) {
            return null;
        }

        $parts      = explode(':', $raw, 2);
        $name       = $parts[0];
        $contractId = $parts[1] ?? null;

        return [$name, $contractId];
    }
}

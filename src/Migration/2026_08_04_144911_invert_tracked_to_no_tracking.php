<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\App\Options\Definition\NoTrackingDefinition;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Migration\Util\BatchEntityMigrator;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;

/**
 * Converts stored tracking choices to the inverted no tracking option.
 *
 * The tracked option was replaced by its inverse, so every stored value has to flip: a merchant who had
 * tracking switched on must end up with the opt-out switched off. Reading an old value under the new key
 * would mean the opposite of what they chose, so it cannot be left to a read-time fallback.
 *
 * Four stores hold it. Carrier settings are a single record and are converted inline. Product settings,
 * order data and stored shipments are one row per record, so they are walked in resumable batches:
 * PrestaShop has no working scheduler, so the work runs inline and survives a timeout by resuming.
 *
 * Carts are deliberately skipped. A cart carrying the old key becomes an order through the PDK models,
 * which drop the unknown key, so the option resolves to inherit and picks up the migrated carrier
 * default — the current value rather than a stale snapshot. That holds because the consumer can never set
 * this option.
 */
return new class extends AbstractTimestampedMigration {
    /**
     * The keys the option used to be stored under, in settings and on a shipment's options. Literals on
     * purpose: NoTrackingDefinition replaced TrackedDefinition, so there is nothing left to derive from.
     */
    private const LEGACY_SETTING_KEY         = 'exportTracked';
    private const LEGACY_SHIPMENT_OPTION_KEY = 'tracked';

    /**
     * One cursor per pass, so a pass that stopped early resumes where it left off.
     */
    private const CURSOR_PRODUCT_SETTINGS = 'no_tracking_product_settings';
    private const CURSOR_ORDER_DATA       = 'no_tracking_order_data';
    private const CURSOR_ORDER_SHIPMENT   = 'no_tracking_order_shipment';

    public function up(): void
    {
        try {
            $this->invertCarrierSettings();
            $this->invertProductSettings();

            // The choice made for an order, then how each shipment created from it actually went out.
            $this->invertDeliveryOptionRows(PsOrderDataRepository::class, 'orderId', self::CURSOR_ORDER_DATA);
            $this->invertDeliveryOptionRows(
                PsOrderShipmentRepository::class,
                'shipmentId',
                self::CURSOR_ORDER_SHIPMENT
            );

            $this->clearCursors();
        } catch (Throwable $exception) {
            // Report rather than throw, so a failure cannot leave the shop unable to finish upgrading.
            // Progress is kept: each pass stores its cursor after every committed batch and the old key is
            // dropped as each record is written, so the retry resumes instead of redoing the work.
            $this->markFailed('Could not convert stored tracking choices to no tracking.', [
                'exception' => $exception->getMessage(),
                'class'     => get_class($exception),
            ]);
        }
    }

    /**
     * Carrier settings are one stored blob keyed by carrier, so this runs inline. The old key is dropped
     * once converted, so running it again is a no-op rather than a second flip.
     */
    private function invertCarrierSettings(): void
    {
        /** @var PdkSettingsRepositoryInterface $settingsRepository */
        $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);

        $settingsKey = Pdk::get('createSettingsKey')('carrier');
        $settings    = $settingsRepository->get($settingsKey);

        if (empty($settings) || ! is_array($settings)) {
            return;
        }

        $newKey    = (new NoTrackingDefinition())->getCarrierSettingsKey();
        $converted = [];

        foreach ($settings as $carrier => $carrierSettings) {
            if (! is_array($carrierSettings) || ! array_key_exists(self::LEGACY_SETTING_KEY, $carrierSettings)) {
                continue;
            }

            $carrierSettings[$newKey] = $this->invert($carrierSettings[self::LEGACY_SETTING_KEY]);
            unset($carrierSettings[self::LEGACY_SETTING_KEY]);

            $settings[$carrier] = $carrierSettings;
            $converted[]        = $carrier;
        }

        if (! $converted) {
            return;
        }

        $settingsRepository->store($settingsKey, $settings);

        Logger::debug('Inverted the tracking option in carrier settings', ['carriers' => $converted]);
    }

    /**
     * Product settings sit under a "settings" key inside the row's JSON, not at its root.
     */
    private function invertProductSettings(): void
    {
        $migrator = $this->batchMigrator();
        $newKey   = (new NoTrackingDefinition())->getProductSettingsKey();

        $migrator->each(
            Pdk::get(PsProductSettingsRepository::class),
            'productId',
            self::CURSOR_PRODUCT_SETTINGS,
            function ($entity) use ($newKey): void {
                $data     = $entity->getData();
                $settings = $data['settings'] ?? null;

                if (! is_array($settings) || ! array_key_exists(self::LEGACY_SETTING_KEY, $settings)) {
                    return;
                }

                $settings[$newKey] = $this->invert($settings[self::LEGACY_SETTING_KEY]);
                unset($settings[self::LEGACY_SETTING_KEY]);

                $data['settings'] = $settings;
                $entity->setData(json_encode($data));
            }
        );
    }

    /**
     * Both order data and stored shipments keep the option in the same place in their JSON, one row per
     * record, so they convert the same way.
     *
     * These records round-trip through the PDK models, which no longer know the old key, so leaving it
     * would drop it on the next read and erase it on the next save. Flipping it states the same fact in
     * the vocabulary the code now uses.
     */
    private function invertDeliveryOptionRows(string $repository, string $idField, string $cursorName): void
    {
        $migrator = $this->batchMigrator();

        $migrator->each(
            Pdk::get($repository),
            $idField,
            $cursorName,
            function ($entity): void {
                $data = $entity->getData();

                if ($this->invertShipmentOption($data)) {
                    $entity->setData(json_encode($data));
                }
            }
        );
    }

    private function batchMigrator(): BatchEntityMigrator
    {
        return Pdk::get(BatchEntityMigrator::class);
    }

    /**
     * Drop the cursors, once every pass is through.
     *
     * Clearing a cursor as its own pass ends would make a later failure re-walk the finished passes from
     * the start, which is the full table scan the batching exists to avoid.
     */
    private function clearCursors(): void
    {
        $migrator = $this->batchMigrator();

        foreach ([self::CURSOR_PRODUCT_SETTINGS, self::CURSOR_ORDER_DATA, self::CURSOR_ORDER_SHIPMENT] as $cursor) {
            $migrator->clearCursor($cursor);
        }
    }

    /**
     * Order data and stored shipments nest the option the same way, because both carry DeliveryOptions
     * which carries ShipmentOptions.
     *
     * @param  array $record Modified in place when it held an old value
     *
     * @return bool Whether anything was converted
     */
    private function invertShipmentOption(array &$record): bool
    {
        $options = $record['deliveryOptions']['shipmentOptions'] ?? null;

        if (! is_array($options) || ! array_key_exists(self::LEGACY_SHIPMENT_OPTION_KEY, $options)) {
            return false;
        }

        $newKey = (new NoTrackingDefinition())->getShipmentOptionsKey();

        $options[$newKey] = $this->invert($options[self::LEGACY_SHIPMENT_OPTION_KEY]);
        unset($options[self::LEGACY_SHIPMENT_OPTION_KEY]);

        $record['deliveryOptions']['shipmentOptions'] = $options;

        return true;
    }

    /**
     * Flip an explicit choice, leaving "not set" alone: inherit means the merchant never chose, so
     * inverting it would invent a preference.
     *
     * @param  mixed $value Cast, because older stored settings hold these as strings
     */
    private function invert($value): int
    {
        switch ((int) $value) {
            case TriStateService::ENABLED:
                return TriStateService::DISABLED;
            case TriStateService::DISABLED:
                return TriStateService::ENABLED;
            default:
                return TriStateService::INHERIT;
        }
    }
};

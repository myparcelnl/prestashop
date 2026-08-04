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
     * The key the option used to be stored under in settings. A literal on purpose: NoTrackingDefinition
     * replaced TrackedDefinition, so there is no class left to derive it from.
     */
    private const LEGACY_SETTING_KEY = 'exportTracked';

    /**
     * The same option on a shipment's options, which spelled it differently.
     */
    private const LEGACY_SHIPMENT_OPTION_KEY = 'tracked';

    public function up(): void
    {
        $this->invertCarrierSettings();
        $this->invertProductSettings();
        $this->invertOrderData();
        $this->invertOrderShipments();
    }

    /**
     * Carrier settings are one stored blob keyed by carrier, so this runs inline.
     *
     * Carriers without the old key are left alone, and the old key is dropped once converted, so running
     * this again is a no-op rather than a second flip.
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
            'product_settings',
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

        $migrator->clearCursor('product_settings');
    }

    /**
     * The choice made for an order, which still drives a re-export.
     */
    private function invertOrderData(): void
    {
        $migrator = $this->batchMigrator();

        $migrator->each(
            Pdk::get(PsOrderDataRepository::class),
            'orderId',
            'order_data',
            function ($entity): void {
                $data = $entity->getData();

                if ($this->invertShipmentOption($data)) {
                    $entity->setData(json_encode($data));
                }
            }
        );

        $migrator->clearCursor('order_data');
    }

    /**
     * How each shipment went out. One row per shipment here, unlike WooCommerce where a single record
     * holds them all, so there is no inner loop.
     *
     * These records round-trip through the PDK models, which no longer know the old key, so leaving it
     * would drop it on the next read and erase it on the next save. Flipping it states the same fact in
     * the vocabulary the code now uses.
     */
    private function invertOrderShipments(): void
    {
        $migrator = $this->batchMigrator();

        $migrator->each(
            Pdk::get(PsOrderShipmentRepository::class),
            'shipmentId',
            'order_shipment',
            function ($entity): void {
                $data = $entity->getData();

                if ($this->invertShipmentOption($data)) {
                    $entity->setData(json_encode($data));
                }
            }
        );

        $migrator->clearCursor('order_shipment');
    }

    private function batchMigrator(): BatchEntityMigrator
    {
        return Pdk::get(BatchEntityMigrator::class);
    }

    /**
     * Flip the option inside a record holding delivery options, in place.
     *
     * Order data and stored shipments nest it the same way, because both carry DeliveryOptions which
     * carries ShipmentOptions.
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
     * Flip an explicit choice, leaving "not set" alone.
     *
     * Inherit means the merchant never chose, so inverting it would invent a preference. Values are cast
     * because older stored settings hold them as strings.
     *
     * @param  mixed $value
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

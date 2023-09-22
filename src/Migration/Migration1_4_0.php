<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\PrestaShop\Upgrade\CarrierService;

final class Migration1_4_0 extends AbstractLegacyPsMigration
{
    /**
     * Maps carrier settings to the new checkout settings.
     */
    private const CARRIER_CHECKOUT_SETTINGS_MAP = [
        'addressNotFound'       => 'MYPARCELNL_ADDRESS_NOT_FOUND_TITLE',
        'city'                  => 'MYPARCELNL_CITY_TITLE',
        'closed'                => 'MYPARCELNL_CLOSED_TITLE',
        'deliveryEveningTitle'  => 'MYPARCELNL_DELIVERY_EVENING_TITLE',
        'deliveryMorningTitle'  => 'MYPARCELNL_DELIVERY_MORNING_TITLE',
        'deliveryStandardTitle' => 'MYPARCELNL_DELIVERY_STANDARD_TITLE',
        'deliveryTitle'         => 'MYPARCELNL_DELIVERY_TITLE',
        'discount'              => 'MYPARCELNL_DISCOUNT_TITLE',
        'free'                  => 'MYPARCELNL_FREE_TITLE',
        'from'                  => 'MYPARCELNL_FROM_TITLE',
        'houseNumber'           => 'MYPARCELNL_HOUSE_NUMBER_TITLE',
        'loadMore'              => 'MYPARCELNL_LOAD_MORE_TITLE',
        'onlyRecipientTitle'    => 'MYPARCELNL_ONLY_RECIPIENT_TITLE',
        'openingHours'          => 'MYPARCELNL_OPENING_HOURS_TITLE',
        'pickUpFrom'            => 'MYPARCELNL_PICKUP_TITLE',
        'pickupTitle'           => 'MYPARCELNL_PICK_UP_FROM_TITLE',
        'postcode'              => 'MYPARCELNL_POSTCODE_TITLE',
        'retry'                 => 'MYPARCELNL_RETRY_TITLE',
        'saturdayDeliveryTitle' => 'MYPARCELNL_SATURDAY_DELIVERY_TITLE',
        'signatureTitle'        => 'MYPARCELNL_SIGNATURE_TITLE',
        'wrongPostalCodeCity'   => 'MYPARCELNL_WRONG_POSTAL_CODE_CITY_TITLE',
    ];

    public function down(): void
    {
        // do nothing
    }

    public function getVersion(): string
    {
        return '1.4.0';
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function up(): void
    {
        $this->changeCarrierConfigurationValueColumnTypeToText();
        $this->migrateCarrierTitlesToCheckoutConfiguration();
    }

    /**
     * The carrier configuration table value column had a type that was too small to fit the json data from the
     * delivery days calendar. Change it to `text` to fix this.
     *
     * @return void
     */
    private function changeCarrierConfigurationValueColumnTypeToText(): void
    {
        $table = $this->getCarrierConfigurationTable();
        $query = "ALTER TABLE `$table` MODIFY value TEXT;";

        $this->db->execute($query);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $collection
     * @param  string                                  $key
     *
     * @return mixed
     */
    private function getFirstValue(Collection $collection, string $key)
    {
        $result = $collection->firstWhere('name', $key);

        return $result ? $result['value'] : null;
    }

    /**
     * Migrates CarrierSettings strings to CheckoutSettings. Values are determined as follows:
     * - Value from default carrier (if filled)
     * - Value from any other carrier (if filled)
     * - "" (default value)
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     */
    private function migrateCarrierTitlesToCheckoutConfiguration(): void
    {
        $table          = $this->getCarrierConfigurationTable();
        $defaultCarrier = Platform::get('defaultCarrier');

        $prestashopCarrierId = CarrierService::getPrestaShopCarrierId($defaultCarrier);

        $carrierConfigurationRows = $this->getAllRows(AbstractLegacyPsMigration::LEGACY_TABLE_CARRIER_CONFIGURATION);

        $anyCarrierNonEmpty = $carrierConfigurationRows
            ->whereIn('name', array_keys(self::CARRIER_CHECKOUT_SETTINGS_MAP))
            ->where('value', '!=', '');

        $defaultCarrierNonEmpty = $carrierConfigurationRows->where('id_carrier', $prestashopCarrierId);

        $newConfigurations            = [];
        $oldCarrierConfigurationNames = [];

        $existingConfigurations = $this->getAllRows('configuration');

        foreach (self::CARRIER_CHECKOUT_SETTINGS_MAP as $oldKey => $newKey) {
            $valueFromDefaultCarrier = $this->getFirstValue($defaultCarrierNonEmpty, $oldKey);
            $valueFromAnyCarrier     = $this->getFirstValue($anyCarrierNonEmpty, $oldKey);
            $defaultValue            = '';

            // Check configuration rows for any matching rows in case this migration is ever executed twice.
            $existingEntries = $existingConfigurations->where('name', $newKey);

            $newConfigurations[] = array_merge(
                [
                    'name'  => $newKey,
                    'value' => $valueFromDefaultCarrier ?? $valueFromAnyCarrier ?? $defaultValue,
                ],
                ...$existingEntries->toArray()
            );

            $oldCarrierConfigurationNames[] = $oldKey;
        }

        $this->insertRows('configuration', $newConfigurations);
        $this->deleteWhere($table, 'name', $oldCarrierConfigurationNames);
    }
}

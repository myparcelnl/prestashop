<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Upgrade;

use Db;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Module\Configuration\Form\CheckoutForm;
use MyParcelNL\PrestaShop\Service\CarrierService;
use MyParcelNL\Sdk\src\Support\Collection;

class Upgrade1_4_0 extends AbstractUpgrade
{
    /**
     * Maps carrier settings to the new checkout settings.
     */
    private const CARRIER_CHECKOUT_SETTINGS_MAP = [
        'addressNotFound'       => CheckoutForm::CONFIGURATION_ADDRESS_NOT_FOUND,
        'city'                  => CheckoutForm::CONFIGURATION_CITY,
        'closed'                => CheckoutForm::CONFIGURATION_CLOSED,
        'deliveryEveningTitle'  => CheckoutForm::CONFIGURATION_DELIVERY_EVENING_TITLE,
        'deliveryMorningTitle'  => CheckoutForm::CONFIGURATION_DELIVERY_MORNING_TITLE,
        'deliveryStandardTitle' => CheckoutForm::CONFIGURATION_DELIVERY_STANDARD_TITLE,
        'deliveryTitle'         => CheckoutForm::CONFIGURATION_DELIVERY_TITLE,
        'discount'              => CheckoutForm::CONFIGURATION_DISCOUNT,
        'free'                  => CheckoutForm::CONFIGURATION_FREE,
        'from'                  => CheckoutForm::CONFIGURATION_FROM,
        'houseNumber'           => CheckoutForm::CONFIGURATION_HOUSE_NUMBER,
        'loadMore'              => CheckoutForm::CONFIGURATION_LOAD_MORE,
        'onlyRecipientTitle'    => CheckoutForm::CONFIGURATION_ONLY_RECIPIENT_TITLE,
        'openingHours'          => CheckoutForm::CONFIGURATION_OPENING_HOURS,
        'pickUpFrom'            => CheckoutForm::CONFIGURATION_PICKUP_TITLE,
        'pickupTitle'           => CheckoutForm::CONFIGURATION_PICK_UP_FROM,
        'postcode'              => CheckoutForm::CONFIGURATION_POSTCODE,
        'retry'                 => CheckoutForm::CONFIGURATION_RETRY,
        'saturdayDeliveryTitle' => CheckoutForm::CONFIGURATION_SATURDAY_DELIVERY_TITLE,
        'signatureTitle'        => CheckoutForm::CONFIGURATION_SIGNATURE_TITLE,
        'wrongPostalCodeCity'   => CheckoutForm::CONFIGURATION_WRONG_POSTAL_CODE_CITY,
    ];

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function upgrade(): void
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
        $carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $query                     = "ALTER TABLE $carrierConfigurationTable MODIFY value TEXT;";

        $this->db->execute($query);
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Support\Collection $collection
     * @param  string                                 $key
     *
     * @return mixed
     */
    private function getFirstValue(Collection $collection, string $key)
    {
        $result = $collection->where('name', $key)
            ->first();

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
        $carrierConfigurationTable = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $configurationTable        = Table::withPrefix('configuration');
        $defaultCarrier            = $this->platformService->getDefaultCarrier();
        $prestashopCarrierId       = CarrierService::getPrestaShopCarrierId($defaultCarrier);

        $query = <<<SQL
SELECT * FROM $carrierConfigurationTable
SQL;

        $carrierConfigurationRows = new Collection($this->db->executeS($query));

        $anyCarrierNonEmpty     = $carrierConfigurationRows
            ->whereIn('name', array_keys(self::CARRIER_CHECKOUT_SETTINGS_MAP))
            ->where('value', '!=', '');
        $defaultCarrierNonEmpty = $carrierConfigurationRows->where('id_carrier', $prestashopCarrierId);

        $newCheckoutEntries           = [];
        $oldCarrierConfigurationNames = [];

        $existingConfigurations = (array) $this->db->executeS(
            "SELECT id_configuration, id_shop, id_shop_group, name, value FROM $configurationTable"
        );

        foreach (self::CARRIER_CHECKOUT_SETTINGS_MAP as $oldKey => $newKey) {
            $valueFromDefaultCarrier = $this->getFirstValue($defaultCarrierNonEmpty, $oldKey);
            $valueFromAnyCarrier     = $this->getFirstValue($anyCarrierNonEmpty, $oldKey);
            $defaultValue            = '';

            // Check configuration rows for any matching rows in case this migration is ever executed twice.
            $existingEntries = array_filter($existingConfigurations, static function (array $entry) use ($newKey) {
                return $entry['name'] === $newKey;
            });

            $newCheckoutEntries[] = array_merge(
                [
                    'name'  => $newKey,
                    'value' => $valueFromDefaultCarrier ?? $valueFromAnyCarrier ?? $defaultValue,
                ],
                ...$existingEntries
            );

            $oldCarrierConfigurationNames[] = $oldKey;
        }

        $this->db->insert('configuration', $newCheckoutEntries, false, true, Db::REPLACE);

        // Delete old records from carrier configurations.
        $nameString = implode('\', \'', $oldCarrierConfigurationNames);
        $query      = "DELETE FROM $carrierConfigurationTable WHERE name IN ('$nameString')";

        $this->db->execute($query);
    }
}

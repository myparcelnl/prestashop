<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Migration;

use DbQuery;
use Generator;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService;
use MyParcelNL\PrestaShop\Repository\PsCarrierConfigurationRepository;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use PrestaShop\PrestaShop\Core\Foundation\Database\Exception;

final class Migration2_0_0 extends AbstractPsMigration
{
    protected const         LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    protected const         LEGACY_TABLE_DELIVERY_SETTINGS     = 'myparcelnl_delivery_settings';
    protected const         LEGACY_TABLE_ORDER_LABEL           = 'myparcelnl_order_label';
    protected const         LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';
    private const           OLD_CARRIERS                       = [
        'postnl',
        'dhlforyou',
        'dhlparcelconnect',
        'dhleuroplus',
    ];
    private const           TRANSFORM_CAST_ARRAY               = 'array';
    private const           TRANSFORM_CAST_BOOL                = 'bool';
    private const           TRANSFORM_CAST_CENTS               = 'cents';
    private const           TRANSFORM_CAST_FLOAT               = 'float';
    private const           TRANSFORM_CAST_INT                 = 'int';
    private const           TRANSFORM_CAST_STRING              = 'string';
    private const           TRANSFORM_KEY_CAST                 = 'cast';
    private const           TRANSFORM_KEY_SOURCE               = 'source';
    private const           TRANSFORM_KEY_TARGET               = 'target';
    private const           TRANSFORM_KEY_TRANSFORM            = 'transform';

    public function down(): void
    {
        // TODO: Implement down() method.
    }

    public function getVersion(): string
    {
        return '2.0.0';
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    public function up(): void
    {
        $this->installCarriers();
        $this->migrateSettings();
        // $this->migrateCartDeliveryOptions();
        // $this->migrateOrderData();
        // $this->migrateOrderShipments();
    }

    /**
     * @return void
     * TODO: NEE!!!!!!!!!!
     */
    protected function dropOldTables(): void
    {
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getOrderDataTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getProductSettingsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getOrderShipmentsTable()}`");
        $this->db->execute("DROP TABLE IF EXISTS `{$this->getCartDeliveryOptionsTable()}`");
    }

    /**
     * @param  string $cast
     * @param         $value
     *
     * @return bool|float|int|string|array
     */
    private function castValue(string $cast, $value)
    {
        $currencyService = Pdk::get(CurrencyService::class);

        switch ($cast) {
            case self::TRANSFORM_CAST_BOOL:
                return (bool) $value;

            case self::TRANSFORM_CAST_INT:
                return (int) $value;

            case self::TRANSFORM_CAST_STRING:
                return (string) $value;

            case self::TRANSFORM_CAST_FLOAT:
                return (float) $value;

            case self::TRANSFORM_CAST_CENTS:
                return $currencyService->convertToCents((float) $value);

            case self::TRANSFORM_CAST_ARRAY:
                return (array) $value;

            default:
                return $value;
        }
    }

    /**
     * @return void
     * TODO use DatabaseMigrations
     * @see \MyParcelNL\PrestaShop\Database\DatabaseMigrations
     */
    private function createCarrierConfigurationTable(): void
    {
        $this->db->execute(
            "CREATE TABLE if NOT EXISTS ps_myparcel_carrier_configuration (
	ps_carrier_id INT(11) NOT NULL DEFAULT '0',
    myparcel_carrier VARCHAR(255) NOT NULL DEFAULT ''
) AUTO_INCREMENT=1;"
        );
    }

    /**
     * Creates and maps one PrestaShop carrier to a MyParcel carrier.
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    private function createPsCarriers(): void
    {
        $this->createCarrierConfigurationTable();

        $carriers   = ['PostNL' => 'postnl'];
        $carrierIds = [];

        foreach ($carriers as $carrierHuman => $carrierName) {
            $result = $this->db->insert('carrier', [
                'name'                 => sprintf('%s MyParcel', $carrierHuman),
                'active'               => 1,
                'is_module'            => 1,
                'shipping_external'    => 1,
                'need_range'           => 1,
                'external_module_name' => Pdk::get('platform'),
            ]);

            if (! $result) {
                throw new Exception('Cannot insert new carrier with name', compact($carrierHuman));
            }

            $request   = "SELECT id_carrier FROM ps_carrier WHERE name = '${carrierHuman} MyParcel'";
            $carrierId = $this->db->getValue($request);

            if (! $carrierId) {
                throw new Exception('Cannot retrieve carrierId while upgrading', compact($request));
            }

            $carrierIds[$carrierName] = $carrierId;

            /** @var PsCarrierConfigurationRepository $carrierConfigurationRepository */
            $carrierConfigurationRepository = Pdk::get(PsCarrierConfigurationRepository::class);

            $carrierConfigurationRepository->create([
                'idCarrier'       => $carrierId,
                'myparcelCarrier' => $carrierName,
            ]);
        }

        if (! $result) {
            throw new Exception('Cannot insert new generated carrier configuration for carrier', compact($carrierName));
        }
    }

    /**
     * @return array
     */
    private function getCarrierSettings(): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('myparcelnl_carrier_configuration');
        $query->groupBy('id_carrier');

        $result = $this->db->executeS($query);

        return $result;
    }

    /**
     * @return string
     */
    private function getCartDeliveryOptionsTable(): string
    {
        return Table::withPrefix(Table::TABLE_CART_DELIVERY_OPTIONS);
    }

    /**
     * @return array
     */
    private function getConfigurationSettings(): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('configuration');
        $query->where('name LIKE "myparcelnl_%"');

        return $this->db->executeS($query);
    }

    /**
     * @return string
     */
    private function getOrderDataTable(): string
    {
        return Table::withPrefix(Table::TABLE_ORDER_DATA);
    }

    /**
     * @return string
     */
    private function getOrderShipmentsTable(): string
    {
        return Table::withPrefix(Table::TABLE_ORDER_SHIPMENT);
    }

    /**
     * @return string
     */
    private function getProductSettingsTable(): string
    {
        return Table::withPrefix(Table::TABLE_PRODUCT_SETTINGS);
    }

    /**
     * @return \Generator
     */
    private function getTransformationMap(): Generator
    {
        /**
         * General
         */
        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_API_KEY',
            self::TRANSFORM_KEY_TARGET => 'general.apiKey',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        // TODO: Prestashop doesn't have PPS
        yield [
            self::TRANSFORM_KEY_SOURCE    => 'general.export_mode',
            self::TRANSFORM_KEY_TARGET    => 'general.orderMode',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): bool {
                return $value === 'pps';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_LABEL_OPEN_DOWNLOAD',
            self::TRANSFORM_KEY_TARGET    => 'label.output',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return $value === 'display' ? 'open' : 'download';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_LABEL_SIZE',
            self::TRANSFORM_KEY_TARGET    => 'label.format',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return $value === 'A6' ? 'a6' : 'a4';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_PROMPT_POSITION',
            self::TRANSFORM_KEY_TARGET => 'label.prompt',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_STATUS_CHANGE_MAIL',
            self::TRANSFORM_KEY_TARGET => 'general.trackTraceInEmail',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'general.track_trace_my_account',
            self::TRANSFORM_KEY_TARGET => 'general.trackTraceInAccount',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'general.show_delivery_day',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_CONCEPT_FIRST',
            self::TRANSFORM_KEY_TARGET    => 'general.conceptShipments',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): bool {
                return ! $value;
            },
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'general.order_status_automation',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_ORDER_NOTIFICATION_AFTER',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        // TODO
        // NOTE: Risky. Resulting value may not exist in array of order statuses.
        yield [
            self::TRANSFORM_KEY_SOURCE => 'general.automatic_order_status',
            self::TRANSFORM_KEY_TARGET => 'general.orderStatusOnLabelCreate',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'general.barcode_in_note',
        //            self::TRANSFORM_KEY_TARGET => 'general.barcodeInNote',
        //            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'general.barcode_in_note_title',
        //            self::TRANSFORM_KEY_TARGET => 'general.barcodeInNoteTitle',
        //            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING, // TODO: can also be null, is this a problem?
        //        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_API_LOGGING',
            self::TRANSFORM_KEY_TARGET => 'general.apiLogging',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        /**
         * Checkout
         */

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_USE_ADDRESS2_AS_STREET_NUMBER',
            self::TRANSFORM_KEY_TARGET => 'checkout.useSeparateAddressFields',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'checkout.delivery_options_enabled',
            self::TRANSFORM_KEY_TARGET => 'checkout.enableDeliveryOptions',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'checkout.delivery_options_enabled_for_backorders',
            self::TRANSFORM_KEY_TARGET => 'checkout.enableDeliveryOptionsWhenNotInStock',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'checkout.delivery_options_display',
            self::TRANSFORM_KEY_TARGET => 'checkout.deliveryOptionsDisplay',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
            // TODO check this setting, should be for specific shipping methods
        ];

        // TODO
        // NOTE: Risky. Resulting value may not exist in array of checkout hooks.
        yield [
            self::TRANSFORM_KEY_SOURCE => 'checkout.delivery_options_position',
            self::TRANSFORM_KEY_TARGET => 'checkout.deliveryOptionsPosition',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_DELIVERY_OPTIONS_PRICE_FORMAT',
            self::TRANSFORM_KEY_TARGET    => 'checkout.priceType',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return $value === 'total_price' ? 'included' : 'excluded';
            },
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE    => 'checkout.pickup_locations_default_view',
            self::TRANSFORM_KEY_TARGET    => 'checkout.pickupLocationsDefaultView',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return $value === 'map' ? 'map' : 'list';
            },
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'checkout.delivery_options_custom_css',
            self::TRANSFORM_KEY_TARGET => 'checkout.deliveryOptionsCustomCss',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'checkout.header_delivery_options_title',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERY_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERY_MORNING_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERY_STANDARD_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERY_EVENING_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'checkout.same_day_title',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_ONLY_RECIPIENT_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SIGNATURE_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_PICKUP_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_HOUSE_NUMBER_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '' // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_CITY_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '' // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_POSTCODE_TITLE',
        //            self::TRANSFORM_KEY_TARGET => '' // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'checkout.address_not_found_title',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        /**
         * Export defaults
         */

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_PACKAGE_TYPE',
            self::TRANSFORM_KEY_TARGET    => 'checkout.allowedShippingMethods',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): array {
                if (! is_array($value)) {
                    return [];
                }

                return array_reduce(
                    $value,
                    static function (array $carry, $shippingMethods): array {
                        if (is_array($shippingMethods)) {
                            foreach ($shippingMethods as $shippingMethod) {
                                $carry[] = $shippingMethod;
                            }
                        }

                        return $carry;
                    },
                    []
                );
            },
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SHARE_CUSTOMER_EMAIL',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SHARE_CUSTOMER_PHONE',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'export_defaults.save_customer_address',
            self::TRANSFORM_KEY_TARGET => 'order.saveCustomerAddress',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_DESCRIPTION',
            self::TRANSFORM_KEY_TARGET => 'label.description',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'export_defaults.empty_parcel_weight',
            self::TRANSFORM_KEY_TARGET => 'order.emptyParcelWeight',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'export_defaults.empty_digital_stamp_weight',
            self::TRANSFORM_KEY_TARGET => 'order.emptyDigitalStampWeight',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DEFAULT_CUSTOMS_CODE',
            self::TRANSFORM_KEY_TARGET => 'customs.customsCode',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        // TODO
        yield [
            self::TRANSFORM_KEY_SOURCE => 'export_defaults.package_contents',
            self::TRANSFORM_KEY_TARGET => 'customs.packageContents',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DEFAULT_CUSTOMS_ORIGIN',
            self::TRANSFORM_KEY_TARGET => 'customs.countryOfOrigin',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'export_defaults.export_automatic',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        //        yield [
        //            self::TRANSFORM_KEY_SOURCE => 'export_defaults.export_automatic_status',
        //            self::TRANSFORM_KEY_TARGET => '', // TODO
        //        ];

        /**
         * Carriers
         */
        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_AGE_CHECK',
            self::TRANSFORM_KEY_TARGET => 'exportAgeCheck',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_INSURANCE',
            self::TRANSFORM_KEY_TARGET => 'exportInsurance',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_INSURANCE_FROM_PRICE',
            self::TRANSFORM_KEY_TARGET => 'exportInsuranceAmount',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_INSURANCE_MAX_AMOUNT',
            self::TRANSFORM_KEY_TARGET => 'exportInsuranceUpTo',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_BE',
            self::TRANSFORM_KEY_TARGET => '',
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_INSURANCE_MAX_AMOUNT_EU',
            self::TRANSFORM_KEY_TARGET => '',
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_PACKAGE_FORMAT',
            self::TRANSFORM_KEY_TARGET => 'exportLargeFormat',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_RECIPIENT_ONLY',
            self::TRANSFORM_KEY_TARGET => 'exportOnlyRecipient',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_RETURN_PACKAGE',
            self::TRANSFORM_KEY_TARGET => 'exportReturn',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SIGNATURE_REQUIRED',
            self::TRANSFORM_KEY_TARGET => 'exportSignature',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_AGE_CHECK',
            self::TRANSFORM_KEY_TARGET => 'exportAgeCheck',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_INSURANCE',
            self::TRANSFORM_KEY_TARGET => 'exportInsurance',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_INSURANCE_FROM_PRICE',
            self::TRANSFORM_KEY_TARGET => 'exportInsuranceAmount',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT',
            self::TRANSFORM_KEY_TARGET => 'exportInsuranceUpTo',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_BE',
            self::TRANSFORM_KEY_TARGET => '',
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_INSURANCE_MAX_AMOUNT_EU',
            self::TRANSFORM_KEY_TARGET => '',
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_PACKAGE_FORMAT',
            self::TRANSFORM_KEY_TARGET => 'exportLargeFormat',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_RECIPIENT_ONLY',
            self::TRANSFORM_KEY_TARGET => 'exportOnlyRecipient',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_RETURN_PACKAGE',
            self::TRANSFORM_KEY_TARGET => 'exportReturn',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'return_MYPARCELNL_SIGNATURE_REQUIRED',
            self::TRANSFORM_KEY_TARGET => 'exportSignature',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'deliveryDaysWindow',
            self::TRANSFORM_KEY_TARGET => 'deliveryDaysWindow',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'dropOffDelay',
            self::TRANSFORM_KEY_TARGET => 'dropOffDelay',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowMondayDelivery',
            self::TRANSFORM_KEY_TARGET => 'allowMondayDelivery',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowMorningDelivery',
            self::TRANSFORM_KEY_TARGET => 'allowMorningDelivery',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'priceMorningDelivery',
            self::TRANSFORM_KEY_TARGET => 'priceDeliveryTypeMorning',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowEveningDelivery',
            self::TRANSFORM_KEY_TARGET => 'allowEveningDelivery',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'priceEveningDelivery',
            self::TRANSFORM_KEY_TARGET => 'priceDeliveryTypeEvening',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowOnlyRecipient',
            self::TRANSFORM_KEY_TARGET => 'allowOnlyRecipient',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'priceOnlyRecipient',
            self::TRANSFORM_KEY_TARGET => 'priceOnlyRecipient',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowSignature',
            self::TRANSFORM_KEY_TARGET => 'allowSignature',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'priceSignature',
            self::TRANSFORM_KEY_TARGET => 'priceSignature',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'allowPickupPoints',
            self::TRANSFORM_KEY_TARGET => 'allowPickupPoints',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'pricePickup',
            self::TRANSFORM_KEY_TARGET => 'priceDeliveryTypePickup',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_CENTS,
        ];
    }

    /**
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShop\PrestaShop\Core\Foundation\Database\Exception
     */
    private function installCarriers(): void
    {
        /** @var \MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService $service */
        $service = Pdk::get(PsPdkUpgradeService::class);

        $service->createPsCarriers();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \PrestaShopException
     */
    private function migrateCartDeliveryOptions(): void
    {
        $query = new DbQuery();

        $query->select('*');
        $query->from(self::LEGACY_TABLE_DELIVERY_SETTINGS);

        $oldValues = $this->db->executeS($query);
        $newValues = [];

        foreach ($oldValues as $deliveryOptions) {
            $data     = json_decode($deliveryOptions['delivery_settings'], true);
            $instance = (new DeliveryOptions())->fill($data);

            $newValues[] = [
                'cartId'          => $deliveryOptions['id_cart'],
                'shippingMethod'  => $deliveryOptions['id_delivery_setting'],
                'deliveryOptions' => json_encode($instance->toArray()),
            ];
        }

        $newValuesString      = implode(',', $newValues);
        $deliveryOptionsTable = Table::withPrefix(Table::TABLE_CART_DELIVERY_OPTIONS);

        $strr = array_reduce($newValues, static function ($acc, $val) {
            $acc .= sprintf("('%s'),\n", implode("','", $val));

            return $acc;
        }, '');

        $this->db->execute(
            "INSERT INTO `$deliveryOptionsTable` (`cartId`, `deliveryOptions`, `deliveryMethod`) VALUES $newValuesString"
        );

        Logger::debug('Migrated delivery options', compact('oldValues', 'newValues'));
    }

    private function migrateOrderData(): void
    {
        // from
    }

    private function migrateOrderShipments()
    {
        // from order_label to order_shipment
    }

    /**
     * @return void
     */
    private function migrateSettings(): void
    {
        $oldConfigurationSettings = $this->getConfigurationSettings();

        $settingsRepository = Pdk::get(PdkSettingsRepository::class);

        $newSettings = $this->transformSettings($oldConfigurationSettings);

        $newSettings['carrier'] = new SettingsModelCollection();

        foreach (self::OLD_CARRIERS as $carrier) {
            $transformed                         = $this->transformSettings($oldConfigurationSettings[$carrier] ?? []);
            $transformed['dropOffPossibilities'] = $this->transformDropOffPossibilities(
                $oldConfigurationSettings[$carrier] ?? []
            );

            $newSettings['carrier']->put($carrier, $transformed);
        }

        $settings = new Settings(array_replace_recursive(SettingsFacade::getDefaults(), $newSettings));

        $settingsRepository->storeAllSettings($settings);
    }

    /**
     * @param $setting
     * @param $array
     *
     * @return null|int|string
     */
    private function searchForValue($setting, $array)
    {
        foreach ($array as $key => $val) {
            if ($val['name'] === $setting) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param  array $oldSettings
     *
     * @return array
     */
    private function transformDropOffPossibilities(array $oldSettings): array
    {
        return [
            'dropOffDays' => array_map(static function ($weekday) use ($oldSettings) {
                $cutoffTime = $oldSettings['cutoff_time'] ?? null;

                switch ($weekday) {
                    case DropOffDay::WEEKDAY_FRIDAY:
                        $cutoffTime = $oldSettings['friday_cutoff_time'] ?? $cutoffTime;
                        break;

                    case DropOffDay::WEEKDAY_SATURDAY:
                        $cutoffTime = $oldSettings['saturday_cutoff_time'] ?? $cutoffTime;
                        break;
                }

                return [
                    'cutoffTime'        => $cutoffTime,
                    'sameDayCutoffTime' => $oldSettings['same_day_delivery_cutoff_time'] ?? null,
                    'weekday'           => $weekday,
                    'dispatch'          => in_array($weekday, $oldSettings['drop_off_days'] ?? [], true),
                ];
            }, DropOffDay::WEEKDAYS),
        ];
    }

    /**
     * @param  array $oldSettings
     *
     * @return array
     */
    private function transformSettings(array $oldSettings): array
    {
        $newSettings = [];

        foreach ($this->getTransformationMap() as $item) {
            if (! $this->searchForValue($item[self::TRANSFORM_KEY_SOURCE], $oldSettings)) {
                continue;
            }

            $value    = Arr::get($oldSettings, $item[self::TRANSFORM_KEY_SOURCE]);
            $newValue = $value;

            if ($item[self::TRANSFORM_KEY_TRANSFORM] ?? false) {
                $newValue = $item[self::TRANSFORM_KEY_TRANSFORM]($newValue);
            }

            if ($item[self::TRANSFORM_KEY_CAST] ?? false) {
                $newValue = $this->castValue($item[self::TRANSFORM_KEY_CAST], $newValue);
            }

            Arr::set($newSettings, $item[self::TRANSFORM_KEY_TARGET], $newValue);
        }

        return $newSettings;
    }
}

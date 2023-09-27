<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use DbQuery;
use Generator;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\PrestaShop\Migration\Util\CastValue;
use MyParcelNL\PrestaShop\Migration\Util\MigratableValue;
use MyParcelNL\PrestaShop\Migration\Util\PsConfigurationDataMigrator;
use MyParcelNL\PrestaShop\Migration\Util\TransformValue;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use Throwable;

final class PdkSettingsMigration extends AbstractPsPdkMigration
{
    private const OLD_CARRIERS        = [
        'postnl',
        'dhlforyou',
        'dhlparcelconnect',
        'dhleuroplus',
    ];
    private const WEEKDAY_SETTING_MAP = [
        'sundayCutoffTime',
        'mondayCutoffTime',
        'tuesdayCutoffTime',
        'wednesdayCutoffTime',
        'thursdayCutoffTime',
        'fridayCutoffTime',
        'saturdayCutoffTime',
    ];

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $orderDataRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository
     */
    private $pdkSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $productSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Migration\Util\PsConfigurationDataMigrator
     */
    private $valueMigrator;

    /**
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository              $orderDataRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository          $orderShipmentRepository
     * @param  \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository $pdkSettingsRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository        $productSettingsRepository
     * @param  \MyParcelNL\PrestaShop\Migration\Util\PsConfigurationDataMigrator    $valueMigrator
     */
    public function __construct(
        PsOrderDataRepository       $orderDataRepository,
        PsOrderShipmentRepository   $orderShipmentRepository,
        PdkSettingsRepository       $pdkSettingsRepository,
        PsProductSettingsRepository $productSettingsRepository,
        PsConfigurationDataMigrator $valueMigrator
    ) {
        parent::__construct();

        $this->orderDataRepository       = $orderDataRepository;
        $this->orderShipmentRepository   = $orderShipmentRepository;
        $this->pdkSettingsRepository     = $pdkSettingsRepository;
        $this->productSettingsRepository = $productSettingsRepository;
        $this->valueMigrator             = $valueMigrator;
    }

    public function up(): void
    {
        $this->migrateSettings();
    }

    /**
     * @return \Generator<MigratableValue>
     */
    private function getCarrierSettingsTransformationMap(): Generator
    {
        yield new MigratableValue(
            'MYPARCELNL_AGE_CHECK',
            CarrierSettings::EXPORT_AGE_CHECK,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE',
            CarrierSettings::EXPORT_INSURANCE,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE_FROM_PRICE',
            CarrierSettings::EXPORT_INSURANCE_FROM_AMOUNT,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE_MAX_AMOUNT',
            CarrierSettings::EXPORT_INSURANCE_UP_TO,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE_MAX_AMOUNT_BE',
            CarrierSettings::EXPORT_INSURANCE_UP_TO_UNIQUE,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'MYPARCELNL_INSURANCE_MAX_AMOUNT_EU',
            CarrierSettings::EXPORT_INSURANCE_UP_TO_EU,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'MYPARCELNL_PACKAGE_FORMAT',
            CarrierSettings::EXPORT_LARGE_FORMAT,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_RECIPIENT_ONLY',
            CarrierSettings::EXPORT_ONLY_RECIPIENT,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_RETURN_PACKAGE',
            CarrierSettings::EXPORT_RETURN,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_SIGNATURE_REQUIRED',
            CarrierSettings::EXPORT_SIGNATURE,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'deliveryDaysWindow',
            CarrierSettings::DELIVERY_DAYS_WINDOW,
            new CastValue(CastValue::CAST_INT)
        );

        yield new MigratableValue(
            'dropOffDelay',
            CarrierSettings::DROP_OFF_DELAY,
            new CastValue(CastValue::CAST_INT)
        );

        yield new MigratableValue(
            'allowMondayDelivery',
            CarrierSettings::ALLOW_MONDAY_DELIVERY,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'allowMorningDelivery',
            CarrierSettings::ALLOW_MORNING_DELIVERY,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'priceMorningDelivery',
            CarrierSettings::PRICE_DELIVERY_TYPE_MORNING,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'allowEveningDelivery',
            CarrierSettings::ALLOW_EVENING_DELIVERY,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'priceEveningDelivery',
            CarrierSettings::PRICE_DELIVERY_TYPE_EVENING,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'allowOnlyRecipient',
            CarrierSettings::ALLOW_ONLY_RECIPIENT,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'priceOnlyRecipient',
            CarrierSettings::PRICE_ONLY_RECIPIENT,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'allowSignature',
            CarrierSettings::ALLOW_SIGNATURE,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'priceSignature',
            CarrierSettings::PRICE_SIGNATURE,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'allowPickupPoints',
            CarrierSettings::ALLOW_PICKUP_LOCATIONS,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'pricePickup',
            CarrierSettings::PRICE_DELIVERY_TYPE_PICKUP,
            new CastValue(CastValue::CAST_CENTS)
        );

        /*
         * Returns
         */

        yield new MigratableValue(
            'return_MYPARCELNL_PACKAGE_FORMAT',
            CarrierSettings::EXPORT_RETURN_LARGE_FORMAT,
            new CastValue(CastValue::CAST_BOOL)
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     * @throws \PrestaShopDatabaseException
     */
    private function getConfigurationRows(): Collection
    {
        return $this->getAllRows('configuration', function (DbQuery $query) {
            $query->where('name LIKE "myparcelnl_%"');
        });
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    private function getMigratedCarrierSettings(): SettingsModelCollection
    {
        $oldCarrierSettings  = $this->getAllRows($this->getCarrierConfigurationTable());
        $carrierNamesAndIds  = [];
        $transformedSettings = [];

        foreach ($oldCarrierSettings as $setting) {
            if (! isset($setting['id_carrier'], $setting['name'], $setting['value'])) {
                continue;
            }

            $id_carrier = $setting['id_carrier'];
            $name       = $setting['name'];
            $value      = $setting['value'];

            if (! isset($transformedSettings[$id_carrier])) {
                $transformedSettings[$id_carrier] = [];
            }

            if ('carrierType' === $name) {
                $carrierNamesAndIds[$value] = $id_carrier;
            }

            $transformedSettings[$id_carrier][] = $setting;
        }

        $oldCarrierSettings = [];

        foreach ($carrierNamesAndIds as $name => $id_carrier) {
            $oldCarrierSettings[$name] = $transformedSettings[$id_carrier];
        }

        $newCarrierSettings = new SettingsModelCollection();

        foreach (self::OLD_CARRIERS as $carrier) {
            $settings    = $oldCarrierSettings[$carrier] ?? [];
            $transformed = $this->valueMigrator->transform($settings, $this->getCarrierSettingsTransformationMap());

            $transformed['dropOffPossibilities'] = $this->transformDropOffPossibilities($settings);

            $newCarrierSettings->put($carrier, $transformed);
        }

        return $newCarrierSettings;
    }

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getMigratedSettings(): array
    {
        $oldSettings = $this->getConfigurationRows();
        $newSettings = $this->valueMigrator->transform($oldSettings, $this->getSettingsTransformationMap());

        $newSettings[CarrierSettings::ID] = $this->getMigratedCarrierSettings();

        return $newSettings;
    }

    /**
     * @return \Generator<MigratableValue>
     */
    private function getSettingsTransformationMap(): Generator
    {
        /*
         * Account settings
         */

        yield new MigratableValue(
            'MYPARCELNL_API_KEY',
            implode('.', [AccountSettings::ID, AccountSettings::API_KEY]),
            new CastValue(CastValue::CAST_STRING)
        );

        /*
         * Order settings
         */

        yield new MigratableValue(
            'MYPARCELNL_SHARE_CUSTOMER_EMAIL',
            implode('.', [OrderSettings::ID, OrderSettings::SHARE_CUSTOMER_INFORMATION]),
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_CONCEPT_FIRST',
            implode('.', [OrderSettings::ID, OrderSettings::PROCESS_DIRECTLY]),
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_CREATED_ORDER_STATUS',
            implode('.', [OrderSettings::ID, OrderSettings::STATUS_ON_LABEL_CREATE]),
            new CastValue(CastValue::CAST_INT)
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_SCANNED_ORDER_STATUS',
            implode('.', [OrderSettings::ID, OrderSettings::STATUS_WHEN_LABEL_SCANNED]),
            new CastValue(CastValue::CAST_INT)
        );

        yield new MigratableValue(
            'MYPARCELNL_DELIVERED_ORDER_STATUS',
            implode('.', [OrderSettings::ID, OrderSettings::STATUS_WHEN_DELIVERED]),
            new CastValue(CastValue::CAST_INT)
        );

        /*
         * Label settings
         */

        yield new MigratableValue(
            'MYPARCELNL_LABEL_DESCRIPTION',
            implode('.', [LabelSettings::ID, LabelSettings::DESCRIPTION]),
            new CastValue(CastValue::CAST_STRING)
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_SIZE',
            implode('.', [LabelSettings::ID, LabelSettings::FORMAT]),
            new TransformValue(function ($value): string {
                return 'A6' === $value ? LabelSettings::FORMAT_A6 : LabelSettings::FORMAT_A4;
            })
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_POSITION',
            implode('.', [LabelSettings::ID, LabelSettings::POSITION]),
            new CastValue(CastValue::CAST_INT)
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_OPEN_DOWNLOAD',
            implode('.', [LabelSettings::ID, LabelSettings::OUTPUT]),
            new TransformValue(function ($value): string {
                return $value ? LabelSettings::OUTPUT_OPEN : LabelSettings::OUTPUT_DOWNLOAD;
            })
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_PROMPT_POSITION',
            implode('.', [LabelSettings::ID, LabelSettings::PROMPT]),
            new CastValue(CastValue::CAST_BOOL)
        );

        /*
         * Customs settings
         */

        yield new MigratableValue(
            'MYPARCELNL_DEFAULT_CUSTOMS_CODE',
            implode('.', [CustomsSettings::ID, CustomsSettings::CUSTOMS_CODE]),
            new CastValue(CastValue::CAST_STRING)
        );

        yield new MigratableValue(
            'MYPARCELNL_DEFAULT_CUSTOMS_ORIGIN',
            implode('.', [CustomsSettings::ID, CustomsSettings::COUNTRY_OF_ORIGIN]),
            new CastValue(CastValue::CAST_STRING)
        );

        /*
         * Checkout settings
         */

        yield new MigratableValue(
            'MYPARCELNL_USE_ADDRESS2_AS_STREET_NUMBER',
            implode('.', [CheckoutSettings::ID, CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS]),
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'MYPARCELNL_DELIVERY_OPTIONS_PRICE_FORMAT',
            implode('.', [CheckoutSettings::ID, CheckoutSettings::PRICE_TYPE]),
            new CastValue(CastValue::CAST_STRING)
        );

        yield new MigratableValue(
            'MYPARCELNL_DELIVERY_TITLE',
            implode('.', [CheckoutSettings::ID, CheckoutSettings::DELIVERY_OPTIONS_HEADER]),
            new CastValue(CastValue::CAST_STRING)
        );
    }

    /**
     * @return void
     */
    private function migrateSettings(): void
    {
        try {
            $settings = new Settings(
                array_replace_recursive(
                    SettingsFacade::getDefaults(),
                    $this->getMigratedSettings()
                )
            );

            $this->pdkSettingsRepository->storeAllSettings($settings);
        } catch (Throwable $e) {
            Logger::error('Failed to migrate settings', ['exception' => $e]);
        }
    }

    /**
     * @param  array $oldSettings
     *
     * @return array
     */
    private function transformDropOffPossibilities(array $oldSettings): array
    {
        $dropOffDaysAsString = $this->valueMigrator->getValue('dropOffDays', $oldSettings, '');
        $dropOffDays         = explode(',', $dropOffDaysAsString);

        $sameDayDeliveryCutoffTime = $this->valueMigrator->getValue(
            'sameDayDeliveryCutoffTime',
            $oldSettings,
            Pdk::get('defaultCutoffTimeSameDay')
        );

        return [
            'dropOffDays' => array_map(
                function (int $weekday) use ($oldSettings, $sameDayDeliveryCutoffTime, $dropOffDays) {
                    $cutoffTime = $this->valueMigrator->getValue(
                        self::WEEKDAY_SETTING_MAP[$weekday],
                        $oldSettings,
                        Pdk::get('defaultCutoffTime')
                    );

                    return [
                        'weekday'           => $weekday,
                        'dispatch'          => in_array((string) $weekday, $dropOffDays, true),
                        'cutoffTime'        => $cutoffTime,
                        'sameDayCutoffTime' => $sameDayDeliveryCutoffTime,
                    ];
                },
                array_keys(self::WEEKDAY_SETTING_MAP)
            ),
        ];
    }
}

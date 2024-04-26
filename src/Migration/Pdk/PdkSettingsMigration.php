<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Pdk;

use DateTime;
use DbQuery;
use Generator;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Settings\Model\CustomsSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\PrestaShop\Migration\AbstractPsMigration;
use MyParcelNL\PrestaShop\Migration\Util\CastValue;
use MyParcelNL\PrestaShop\Migration\Util\DataMigrator;
use MyParcelNL\PrestaShop\Migration\Util\MigratableValue;
use MyParcelNL\PrestaShop\Migration\Util\ToPackageTypeName;
use MyParcelNL\PrestaShop\Migration\Util\TransformValue;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository;
use Throwable;

final class PdkSettingsMigration extends AbstractPsPdkMigration
{
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
     * @var \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository
     */
    private $pdkSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Migration\Util\DataMigrator
     */
    private $valueMigrator;

    /**
     * @param  \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PsPdkSettingsRepository $pdkSettingsRepository
     * @param  \MyParcelNL\PrestaShop\Migration\Util\DataMigrator                     $valueMigrator
     */
    public function __construct(PsPdkSettingsRepository $pdkSettingsRepository, DataMigrator $valueMigrator)
    {
        parent::__construct();

        $this->pdkSettingsRepository = $pdkSettingsRepository;
        $this->valueMigrator         = $valueMigrator;
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
            'MYPARCELNL_PACKAGE_TYPE',
            CarrierSettings::DEFAULT_PACKAGE_TYPE,
            new ToPackageTypeName()
        );

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
            'priceMondayDelivery',
            CarrierSettings::PRICE_DELIVERY_TYPE_MONDAY,
            new CastValue(CastValue::CAST_CENTS)
        );

        yield new MigratableValue(
            'allowSaturdayDelivery',
            CarrierSettings::ALLOW_SATURDAY_DELIVERY,
            new CastValue(CastValue::CAST_BOOL)
        );

        yield new MigratableValue(
            'priceSaturdayDelivery',
            CarrierSettings::PRICE_DELIVERY_TYPE_SATURDAY,
            new CastValue(CastValue::CAST_CENTS)
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
            'return_MYPARCELNL_PACKAGE_TYPE',
            CarrierSettings::EXPORT_RETURN_PACKAGE_TYPE,
            new ToPackageTypeName()
        );

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
        return $this
            ->getAllRows('configuration', function (DbQuery $query) {
                $query->where('name LIKE "myparcelnl_%"');
            })
            ->pluck('value', 'name');
    }

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getMigratedCarrierSettings(): array
    {
        $oldCarrierSettings = $this->getAllRows(AbstractPsMigration::LEGACY_TABLE_CARRIER_CONFIGURATION);

        return $oldCarrierSettings
            ->where('name', 'carrierType')
            ->where('value', '!=', null)
            ->reduce(function (array $carry, array $item) use ($oldCarrierSettings): array {
                $carrierName = $item['value'];

                $oldSettings = $oldCarrierSettings
                    ->where('id_carrier', $item['id_carrier'])
                    ->pluck('value', 'name');

                $carry[$carrierName] = array_replace(
                    $this->valueMigrator->transform($oldSettings, $this->getCarrierSettingsTransformationMap()),
                    [
                        CarrierSettings::ALLOW_DELIVERY_OPTIONS => ! empty($oldSettings['dropOffDays']),
                        CarrierSettings::DROP_OFF_POSSIBILITIES => $this->transformDropOffPossibilities($oldSettings),
                    ]
                );

                return $carry;
            }, []);
    }

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getMigratedSettings(): array
    {
        $oldConfiguration = $this->getConfigurationRows();
        $newSettings      = $this->valueMigrator->transform($oldConfiguration, $this->getSettingsTransformationMap());

        return array_replace($newSettings, [CarrierSettings::ID => $this->getMigratedCarrierSettings()]);
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
            new CastValue(CastValue::CAST_TRI_STATE)
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_SCANNED_ORDER_STATUS',
            implode('.', [OrderSettings::ID, OrderSettings::STATUS_WHEN_LABEL_SCANNED]),
            new CastValue(CastValue::CAST_TRI_STATE)
        );

        yield new MigratableValue(
            'MYPARCELNL_DELIVERED_ORDER_STATUS',
            implode('.', [OrderSettings::ID, OrderSettings::STATUS_WHEN_DELIVERED]),
            new CastValue(CastValue::CAST_TRI_STATE)
        );

        /*
         * Label settings
         */

        yield new MigratableValue(
            'MYPARCELNL_LABEL_DESCRIPTION',
            implode('.', [LabelSettings::ID, LabelSettings::DESCRIPTION]),
            new TransformValue(function ($value): string {
                return strtr((string) $value, [
                    '{order.id}'        => '[ORDER_ID]',
                    '{order.reference}' => '[ORDER_ID]',
                ]);
            })
        );

        yield new MigratableValue(
            'MYPARCELNL_LABEL_SIZE',
            implode('.', [LabelSettings::ID, LabelSettings::FORMAT]),
            new TransformValue(function ($value): string {
                return is_string($value) && strtolower($value) === 'a6'
                    ? LabelSettings::FORMAT_A6
                    : LabelSettings::FORMAT_A4;
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
     * @throws \PrestaShopDatabaseException
     */
    private function migrateSettings(): void
    {
        $defaults               = SettingsFacade::getDefaults();
        $defaultCarrierSettings = Arr::get($defaults, CarrierSettings::ID, []);

        $migratedSettings        = $this->getMigratedSettings();
        $migratedCarrierSettings = Arr::get($migratedSettings, CarrierSettings::ID, []);

        try {
            $settings = new Settings(array_replace($defaults, $migratedSettings, [
                CarrierSettings::ID => array_replace($defaultCarrierSettings, $migratedCarrierSettings),
            ]));

            $this->pdkSettingsRepository->storeAllSettings($settings);
            Logger::debug('Migrated settings');
        } catch (Throwable $e) {
            Logger::error('Failed to migrate settings', ['exception' => $e]);
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $oldSettings
     *
     * @return array
     * @throws \Exception
     */
    private function transformDropOffPossibilities(Collection $oldSettings): array
    {
        $exceptions = json_decode($oldSettings->get('cutoff_exceptions') ?: '[]', true);

        $dropOffDaysAsString = $oldSettings->get('dropOffDays', '');
        $dropOffDays         = explode(',', $dropOffDaysAsString);

        $sameDayCutoffTime = $oldSettings->get('sameDayDeliveryCutoffTime', Pdk::get('defaultCutoffTimeSameDay'));

        return [
            'dropOffDays' => array_map(
                static function (int $weekday) use ($oldSettings, $sameDayCutoffTime, $dropOffDays) {
                    $cutoffTime = $oldSettings->get(self::WEEKDAY_SETTING_MAP[$weekday]);

                    return [
                        'weekday'           => $weekday,
                        'dispatch'          => in_array((string) $weekday, $dropOffDays, true),
                        'cutoffTime'        => $cutoffTime ? "$cutoffTime:00" : Pdk::get('defaultCutoffTime'),
                        'sameDayCutoffTime' => $sameDayCutoffTime,
                    ];
                },
                array_keys(self::WEEKDAY_SETTING_MAP)
            ),

            'dropOffDaysDeviations' => array_map(static function ($item, $key) {
                $dateTime = DateTime::createFromFormat('d-m-Y', $key)
                    ->setTime(0, 0);

                return [
                    'date'       => $dateTime,
                    'cutoffTime' => $item['cutoff'] ?? null,
                    'dispatch'   => array_key_exists('nodispatch', $item) ? ! $item['nodispatch'] : null,
                ];
            }, $exceptions, array_keys($exceptions)),
        ];
    }
}

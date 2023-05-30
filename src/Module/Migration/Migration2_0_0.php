<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Migration;

use DbQuery;
use Exception;
use Generator;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\PrestaShop\Database\DatabaseMigrations;
use MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService;
use MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;

final class Migration2_0_0 extends AbstractPsMigration
{
    protected const         LEGACY_TABLE_CARRIER_CONFIGURATION = 'myparcelnl_carrier_configuration';
    protected const         LEGACY_TABLE_DELIVERY_SETTINGS     = 'myparcelnl_delivery_settings';
    protected const         LEGACY_TABLE_ORDER_LABEL           = 'myparcelnl_order_label';
    protected const         LEGACY_TABLE_PRODUCT_CONFIGURATION = 'myparcelnl_product_configuration';
    private const           LEGACY_PRODUCT_SETTINGS_MAP        = [
        'MYPARCELNL_PACKAGE_TYPE'       => 'packageType',
        'MYPARCELNL_CUSTOMS_ORIGIN'     => 'countryOfOrigin',
        'MYPARCELNL_CUSTOMS_CODE'       => 'customsCode',
        'MYPARCELNL_INSURANCE'          => 'exportInsurance',
        'MYPARCELNL_SIGNATURE_REQUIRED' => 'exportSignature',
        'MYPARCELNL_RETURN_PACKAGE'     => 'exportReturn',
        'MYPARCELNL_PACKAGE_FORMAT'     => 'exportLargeFormat',
        'MYPARCELNL_ONLY_RECIPIENT'     => 'exportOnlyRecipient',
        'MYPARCELNL_AGE_CHECK'          => 'exportAgeCheck',
    ];
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

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $orderDataRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $orderShipmentRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Module\Installer\PsPdkUpgradeService
     */
    private $pdkUpgradeService;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $productSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Settings\Repository\PdkSettingsRepository
     */
    private $settingsRepository;

    public function __construct(
        PsOrderDataRepository       $orderDataRepository,
        PsOrderShipmentRepository   $orderShipmentRepository,
        PsPdkUpgradeService         $pdkUpgradeService,
        PdkSettingsRepository       $pdkSettingsRepository,
        PsProductSettingsRepository $productSettingsRepository
    ) {
        parent::__construct();

        $this->orderDataRepository       = $orderDataRepository;
        $this->orderShipmentRepository   = $orderShipmentRepository;
        $this->pdkUpgradeService         = $pdkUpgradeService;
        $this->settingsRepository        = $pdkSettingsRepository;
        $this->productSettingsRepository = $productSettingsRepository;
    }

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
     * @throws \PrestaShopException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function up(): void
    {
        $this->createDatabaseMigrations();
        $this->pdkUpgradeService->createPsCarriers();
        $this->migrateCarrierSettings();
        $this->migrateSettings();
        $this->migrateProductSettings();
        $this->migrateDeliveryOptions();
        $this->migrateOrderShipments();

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = Pdk::get('ps.entityManager');

        $entityManager->flush();
    }

    /**
     * @param  string $cast
     * @param         $value
     *
     * @return bool|float|int|string|array
     */
    private function castValue(string $cast, $value)
    {
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
                return (int) round((float) $value * 100);

            case self::TRANSFORM_CAST_ARRAY:
                return (array) $value;

            default:
                return $value;
        }
    }

    private function createDatabaseMigrations()
    {
        /** @var \MyParcelNL\PrestaShop\Database\DatabaseMigrations $migrations */
        $migrations = Pdk::get(DatabaseMigrations::class);

        foreach ($migrations->get() as $migration) {
            /** @var \MyParcelNL\PrestaShop\Database\AbstractDatabaseMigration $class */
            $class = Pdk::get($migration);
            $class->up();
        }
    }

    /**
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getCarrierSettings(): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('myparcelnl_carrier_configuration');
        $query->groupBy('id_carrier');

        return $this->db->executeS($query);
    }

    private function getCarrierSettingsTransformationMap()
    {
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
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getConfigurationSettings(): array
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from('configuration');
        $query->where('name LIKE "myparcelnl_%"');

        return $this->db->executeS($query);
    }

    private function getOrderIdByCartId($cartId)
    {
        $query = new DbQuery();
        $query->select('id_order');
        $query->from('orders');
        $query->where('id_cart = ' . $cartId);

        return $this->db->getValue($query);
    }

    /**
     * @return \Generator
     */
    private function getSettingsTransformationMap(): Generator
    {
        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_API_KEY',
            self::TRANSFORM_KEY_TARGET => 'account.apiKey',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_API_LOGGING',
            self::TRANSFORM_KEY_TARGET => 'general.apiLogging',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SHARE_CUSTOMER_EMAIL',
            self::TRANSFORM_KEY_TARGET => 'general.shareCustomerInformation',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_USE_ADDRESS2_AS_STREET_NUMBER',
            self::TRANSFORM_KEY_TARGET => 'checkout.useSeparateAddressFields',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_CONCEPT_FIRST',
            self::TRANSFORM_KEY_TARGET    => 'general.processDirectly',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): bool {
                return ! $value;
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_DELIVERY_OPTIONS_PRICE_FORMAT',
            self::TRANSFORM_KEY_TARGET    => 'checkout.priceType',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return 'total_price' === $value ? 'included' : 'excluded';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_DESCRIPTION',
            self::TRANSFORM_KEY_TARGET => 'label.description',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_LABEL_SIZE',
            self::TRANSFORM_KEY_TARGET    => 'label.format',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return 'A6' === $value ? 'a6' : 'a4';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_POSITION',
            self::TRANSFORM_KEY_TARGET => 'label.position',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE    => 'MYPARCELNL_LABEL_OPEN_DOWNLOAD',
            self::TRANSFORM_KEY_TARGET    => 'label.output',
            self::TRANSFORM_KEY_TRANSFORM => function ($value): string {
                return $value ? 'open' : 'download';
            },
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_PROMPT_POSITION',
            self::TRANSFORM_KEY_TARGET => 'label.prompt',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_CREATED_ORDER_STATUS',
            self::TRANSFORM_KEY_TARGET => 'order.orderstatusOnLabelCreate',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_LABEL_SCANNED_ORDER_STATUS',
            self::TRANSFORM_KEY_TARGET => 'order.orderstatusWhenLabelScanned',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERED_ORDER_STATUS',
            self::TRANSFORM_KEY_TARGET => 'order.orderstatusWhenDelivered',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        // MYPARCELNL_IGNORE_ORDER_STATUS is ignored.

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_STATUS_CHANGE_MAIL',
            self::TRANSFORM_KEY_TARGET => 'order.orderStatusMail',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_ORDER_NOTIFICATION_AFTER',
            self::TRANSFORM_KEY_TARGET => 'order.sendNotificationAfter',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS',
            self::TRANSFORM_KEY_TARGET => 'order.sendOrderStateForDigitalStamp',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_BOOL,
        ];

        // MYPARCELNL_CUSTOMS_FORM is ignored.

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DEFAULT_CUSTOMS_CODE',
            self::TRANSFORM_KEY_TARGET => 'customs.customsCode',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_INT,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DEFAULT_CUSTOMS_ORIGIN',
            self::TRANSFORM_KEY_TARGET => 'customs.countryOfOrigin',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

        yield [
            self::TRANSFORM_KEY_SOURCE => 'MYPARCELNL_DELIVERY_TITLE',
            self::TRANSFORM_KEY_TARGET => 'checkout.deliveryOptionsHeader',
            self::TRANSFORM_KEY_CAST   => self::TRANSFORM_CAST_STRING,
        ];

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
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    private function migrateCarrierSettings()
    {
        //$oldCarrierSettings = $this->getCarrierSettings();

        // TODO: Hier geldt hetzelfde als bij de product settings. Per rij in de tabel
        // is er maar één instelling met een id_carrier en dit zal geconverteerd moeten
        // worden naar één rij met een id_carrier en alle velden die behoren tot die carrier.

        //$newSettings = $this->transformSettings($oldCarrierSettings, $this->getCarrierSettingsTransformationMap());
        //        $newSettings['carrier'] = new SettingsModelCollection();
        //
        //        foreach (self::OLD_CARRIERS as $carrier) {
        //            $transformed                         = $this->transformSettings(
        //                $oldConfigurationSettings[$carrier] ?? [],
        //                $this->getCarrierSettingsTransformationMap()
        //            );
        //            $transformed['dropOffPossibilities'] = $this->transformDropOffPossibilities(
        //                $oldConfigurationSettings[$carrier] ?? []
        //            );
        //
        //            $newSettings['carrier']->put($carrier, $transformed);
        //        }
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     */
    private function migrateDeliveryOptions()
    {
        $query = new DbQuery();

        $query->select('*');
        $query->from(self::LEGACY_TABLE_DELIVERY_SETTINGS);

        $oldValues = $this->db->executeS($query);

        foreach ($oldValues as $oldTableRow) {
            $cartId           = json_decode($oldTableRow['id_cart'] ?? '', true);
            $deliverySettings = json_decode($oldTableRow['delivery_settings'] ?? '', true);
            $extraOptions     = json_decode($oldTableRow['extra_options'] ?? '', true);
            $shipmentOptions  = $deliverySettings ? $deliverySettings['shipmentOptions'] : null;
            $pickupLocation   = $deliverySettings ? $deliverySettings['pickupLocation'] : null;
            $orderId          = $this->getOrderIdByCartId($cartId);

            if (! $orderId) {
                continue;
            }

            $deliveryOptions = [
                'carrier'         => $deliverySettings ? $deliverySettings['carrier'] : null,
                'date'            => $deliverySettings ? $deliverySettings['date'] : null,
                'labelAmount'     => $extraOptions ? $extraOptions['labelAmount'] : null,
                'pickupLocation'  => $pickupLocation ? [
                    'boxNumber'            => $pickupLocation['box_number'] ?? null,
                    'cc'                   => $pickupLocation['cc'] ?? null,
                    'city'                 => $pickupLocation['city'] ?? null,
                    'number'               => $pickupLocation['number'] ?? null,
                    'numberSuffix'         => $pickupLocation['number_suffix'] ?? null,
                    'postalCode'           => $pickupLocation['postal_code'] ?? null,
                    'region'               => $pickupLocation['region'] ?? null,
                    'state'                => $pickupLocation['state'] ?? null,
                    'street'               => $pickupLocation['street'] ?? null,
                    'streetAdditionalInfo' => $pickupLocation['street_additional_info'] ?? null,
                    'locationCode'         => $pickupLocation['location_code'] ?? null,
                    'locationName'         => $pickupLocation['location_name'] ?? null,
                    'retailNetworkId'      => $pickupLocation['retail_network_id'] ?? null,
                ] : null,
                'shipmentOptions' => $shipmentOptions ? [
                    'ageCheck'         => $shipmentOptions['age_check'] ?? null,
                    'insurance'        => $shipmentOptions['insurance'] ?? null,
                    'labelDescription' => $shipmentOptions['label_description'] ?? null,
                    'largeFormat'      => $shipmentOptions['large_format'] ?? null,
                    'onlyRecipient'    => $shipmentOptions['only_recipient'] ?? null,
                    'return'           => $shipmentOptions['return'] ?? null,
                    'sameDayDelivery'  => $shipmentOptions['same_day_delivery'] ?? null,
                    'signature'        => $shipmentOptions['signature'] ?? null,
                ] : null,
                'deliveryType'    => $deliverySettings ? $deliverySettings['deliveryType'] : null,
                'packageType'     => $deliverySettings ? $deliverySettings['packageType'] : null,
            ];

            $this->orderDataRepository->updateOrCreate(
                [
                    'idOrder' => (string) $orderId,
                ],
                [
                    'data' => json_encode(['deliveryOptions' => $deliveryOptions]),
                ]
            );
        }
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    private function migrateOrderShipments()
    {
        $query = new DbQuery();

        $query->select('*');
        $query->from(self::LEGACY_TABLE_ORDER_LABEL);

        $oldOrderLabels = $this->db->executeS($query);

        foreach ($oldOrderLabels as $oldOrderLabel) {
            $shipment = [
                'id'                  => $oldOrderLabel['id_label'] ?? null,
                'orderId'             => $oldOrderLabel['id_order'] ?? null,
                'referenceIdentifier' => $oldOrderLabel['id_order'] ?? null,
                'barcode'             => $oldOrderLabel['barcode'] ?? null,
                'status'              => $oldOrderLabel['status'] ?? null,
            ];

            $this->orderShipmentRepository->updateOrCreate(
                [
                    'idShipment' => (int) $oldOrderLabel['id_label'],
                ],
                [
                    'idOrder' => (string) $oldOrderLabel['id_order'],
                    'data'    => json_encode($shipment),
                ]
            );
        }
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \Doctrine\ORM\ORMException
     */
    private function migrateProductSettings()
    {
        $query = new DbQuery();

        $query->select('*');
        $query->from(self::LEGACY_TABLE_PRODUCT_CONFIGURATION);

        $oldProductSettings = $this->db->executeS($query);

        $productsWithSettings = [];

        foreach ($oldProductSettings as $oldProductSetting) {
            if (! array_key_exists($oldProductSetting['name'], self::LEGACY_PRODUCT_SETTINGS_MAP)) {
                continue;
            }

            $productsWithSettings[$oldProductSetting['id_product']][self::LEGACY_PRODUCT_SETTINGS_MAP[$oldProductSetting['name']]] = $oldProductSetting['value'];
        }

        foreach ($productsWithSettings as $productId => $productSettings) {
            $this->productSettingsRepository->updateOrCreate(
                [
                    'idProduct' => (string) $productId,
                ],
                [
                    'data' => json_encode([
                        'id'   => 'product',
                        'data' => $productSettings,
                    ]),
                ]
            );
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function migrateSettings(): void
    {
        try {
            $oldConfigurationSettings = $this->getConfigurationSettings();
            $newSettings              = $this->transformSettings(
                $oldConfigurationSettings,
                $this->getSettingsTransformationMap()
            );
            $settings                 = new Settings(
                array_replace_recursive(SettingsFacade::getDefaults(), $newSettings)
            );
        } catch (Exception $e) {
            $settings = new Settings(SettingsFacade::getDefaults());
        }

        $this->settingsRepository->storeAllSettings($settings);
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
                    // Friday
                    case 5:
                        $cutoffTime = $oldSettings['friday_cutoff_time'] ?? $cutoffTime;
                        break;
                    // Saturday
                    case 6:
                        $cutoffTime = $oldSettings['saturday_cutoff_time'] ?? $cutoffTime;
                        break;
                }

                return [
                    'cutoffTime'        => $cutoffTime,
                    'sameDayCutoffTime' => $oldSettings['same_day_delivery_cutoff_time'] ?? null,
                    'weekday'           => $weekday,
                    'dispatch'          => in_array($weekday, $oldSettings['drop_off_days'] ?? [], true),
                ];
            }, [1, 2, 3, 4, 5, 6, 0]),
        ];
    }

    /**
     * @param  array      $oldSettings
     * @param  \Generator $transformationMap
     *
     * @return array
     */
    private function transformSettings(array $oldSettings, Generator $transformationMap): array
    {
        $newSettings = [];

        foreach ($transformationMap as $item) {
            $index = $this->searchForValue($item[self::TRANSFORM_KEY_SOURCE], $oldSettings);
            $value = Arr::get($oldSettings, $index)['value'] ?? null;

            if (! isset($index, $value)) {
                continue;
            }

            if ($item[self::TRANSFORM_KEY_TRANSFORM] ?? false) {
                $value = $item[self::TRANSFORM_KEY_TRANSFORM]($value);
            }

            if ($item[self::TRANSFORM_KEY_CAST] ?? false) {
                $value = $this->castValue($item[self::TRANSFORM_KEY_CAST], $value);
            }

            Arr::set($newSettings, $item[self::TRANSFORM_KEY_TARGET], $value);
        }

        return $newSettings;
    }
}

<?php

namespace Gett\MyparcelBE\Module\Hooks;

use Configuration;
use Country;
use Currency;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Model\Core\AccountSettings;
use Gett\MyparcelBE\Model\Core\AccountSettingsService;
use Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator;
use Gett\MyparcelBE\Module\Hooks\Helpers\ModuleSettings;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\ControllerService;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Collection;
use OrderState;
use PrestaShop\PrestaShop\Adapter\Entity\Context;

class ModuleSettingsRenderService extends RenderService
{
    private const ID_ADD_CARRIER_FORM = 'addCarrierForm';
    private const ID_ACCOUNT_SETTINGS = 'accountSettings';
    private const ID_FORM_LAYOUT      = 'moduleSettingsForm';
    private const ID_FORM_VALUES      = 'moduleSettingsValues';

    private $accountSettingsService;
    /**
     * @var string
     */
    private $currencySymbol;

    private $psCarriers;

    private $orderStateOptions;

    public function __construct(Context $context = null)
    {
        parent::__construct($context);
        $this->accountSettingsService = AccountSettingsService::getInstance();
        $this->currencySymbol         = Currency::getDefaultCurrency()
            ->getSymbol();
    }

    /**
     * @throws \Exception
     */
    public function renderModuleSettings(): string
    {
        return $this->renderWithContext('renderModuleSettings', [
            self::ID_ACCOUNT_SETTINGS => $this->getAccountSettingsContext(),
            self::ID_FORM_LAYOUT      => $this->getFormLayoutContext(),
            self::ID_FORM_VALUES      => $this->getFormValuesContext(),
            self::ID_ADD_CARRIER_FORM => $this->getAddCarrierFormLayoutContext(),
        ]);
    }

    public function getAccountSettingsContext(): array
    {
        try {
            $settings = $this->accountSettingsService->retrieveSettings();
            return (null === $settings)
                ? []
                : $settings->toArray();
        } catch (Exception $e) {
            ApiLogger::addLog($e);
            return [];
        }
    }

    private function expandFormLayout(array $form): array
    {
        return array_reduce($form, function (array $newForm, array $formItem) {
            if (! $this->showForPluginVariant($formItem['name'] ?? '')) {
                return $newForm;
            }

            if (isset($formItem['action']) && is_array($formItem['action'])) {
                try {
                    $adminBaseLink = $this->context->link->getAdminBaseLink();
                    $uri           = ControllerService::createActionPath($adminBaseLink,
                        ControllerService::BUTTON_ACTION);

                    $formItem['action'][0] = $uri;
                } catch (Exception $exception) {
                    // We don't care.
                }
            }

            if (isset($formItem['children']) && is_array($formItem['children'])) {
                $formItem['children'] = $this->expandFormLayout($formItem['children']);
            }

            foreach (['label', 'description'] as $translateLabel) {
                if (isset($formItem[$translateLabel])) {
                    $formItem[$translateLabel] = $this->module->l($formItem[$translateLabel]);
                }
            }

            if (isset($formItem['type']) && $formItem['type'] === ModuleSettings::FIELD_TYPE_SWITCH) {
                $formItem['attributes']            = $formItem['attributes'] ?? [];
                $formItem['attributes']['options'] = [
                    $this->module->l($formItem['falseLabel'] ?? 'No'),
                    $this->module->l($formItem['trueLabel'] ?? 'Yes'),
                ];
            }

            $newForm[] = $formItem;

            return $newForm;
        }, []);
    }

    private function getFormLayoutContext(): array
    {
        $form = [
            [
                'label'    => 'Api Settings',
                'name'     => 'api-settings',
                'children' => [
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'  => Constant::API_KEY_CONFIGURATION_NAME,
                        'label' => 'Your API key',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::API_LOGGING_CONFIGURATION_NAME,
                        'label'      => 'Api logging',
                        'falseLabel' => 'Disabled',
                        'trueLabel'  => 'Enabled',
                    ],
                    [
                        'type'   => ModuleSettings::FIELD_TYPE_SUBMIT,
                        'label'  => 'Clear cache',
                        'action' => [ControllerService::MODULE_SETTINGS, ModuleSettings::BUTTON_CLEAR_CACHE],
                    ],
                    [
                        'type'   => ModuleSettings::FIELD_TYPE_SUBMIT,
                        'label'  => 'Reset Webhook',
                        'action' => [ControllerService::MODULE_SETTINGS, ModuleSettings::BUTTON_RESET_HOOK],
                    ],
                    [
                        'type'   => ModuleSettings::FIELD_TYPE_SUBMIT,
                        'label'  => 'Delete Webhook',
                        'action' => [ControllerService::MODULE_SETTINGS, ModuleSettings::BUTTON_DELETE_HOOK],
                    ],
                ],
            ],
            [
                'label'    => 'General Settings',
                'name'     => 'general-settings',
                'children' => [
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
                        'label'      => 'Share customer email with MyParcel',
                        'trueLabel'  => 'Enabled',
                        'falseLabel' => 'Disabled',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME,
                        'label'      => 'Share customer phone with MyParcel',
                        'trueLabel'  => 'Enabled',
                        'falseLabel' => 'Disabled',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME,
                        'label'      => 'Use second address field in checkout as street number',
                        'trueLabel'  => 'Enabled',
                        'falseLabel' => 'Disabled',
                    ],
                    [
                        'type'    => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'    => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME,
                        'label'   => 'Show prices as',
                        'options' => [
                            [
                                'value' => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_TOTAL_PRICE,
                                'label' => 'Total price',
                            ],
                            [
                                'value' => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE,
                                'label' => 'Surcharge',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'label'    => 'Label Settings',
                'name'     => 'label-settings',
                'children' => [
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME,
                        'label'       => 'Label description',
                        'description' => $this->module->l(
                                'You can add the following variables to the description',
                                'labelform'
                            )
                            . '<ul class="label-description-variables">'
                            . '<li>'
                            . '<code>{order.id}</code>'
                            . sprintf(
                                ' - %s',
                                $this->module->l('Order number', 'labelform')
                            )
                            . '</li>'
                            . '<li>'
                            . '<code>{order.reference}</code>'
                            . sprintf(
                                ' - %s',
                                $this->module->l('Order reference', 'labelform')
                            )
                            . '</li>'
                            . '</ul>',
                        'placeholder' => '{order.id} {order.reference}',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::LABEL_SIZE_CONFIGURATION_NAME,
                        'label'      => 'Default label size',
                        'attributes' => [
                            'options' => [
                                ['value' => 'a4', 'label' => 'A4',],
                                ['value' => 'a6', 'label' => 'A6',],
                            ],
                        ],
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::LABEL_POSITION_CONFIGURATION_NAME,
                        'label'      => 'Default label position',
                        'attributes' => [
                            'options' => [
                                ['value' => '1', 'label' => 'Top left', 'labelform'],
                                ['value' => '2', 'label' => 'Top right', 'labelform'],
                                ['value' => '3', 'label' => 'Bottom left', 'labelform'],
                                ['value' => '4', 'label' => 'Bottom right', 'labelform'],
                            ],
                        ], // TODO implement v-if: only show when label size = A4 in this case
                        'condition'  => [
                            Constant::LABEL_SIZE_CONFIGURATION_NAME => 'a4',
                        ],
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
                        'label'      => 'Open or download label',
                        'attributes' => [
                            'options' => [
                                ['value' => 1, 'label' => 'Open'],
                                ['value' => 0, 'label' => 'Download'],
                            ],
                        ],
                    ],
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'  => Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME,
                        'label' => 'Prompt for label position',
                    ],
                ],
            ],
            [
                'label'    => 'Order settings',
                'name'     => 'order-settings',
                'children' => [
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME,
                        'label'      => 'Order status when label created',
                        'attributes' => ['options' => $this->getOrderStateOptions()],
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT_MULTI,
                        'name'       => Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME,
                        'label'      => 'Ignore order statuses',
                        'attributes' => ['options' => array_slice($this->getOrderStateOptions(), 1)],
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME,
                        'label'      => 'Order status mail',
                        'trueLabel'  => 'Enabled',
                        'falseLabel' => 'Disabled',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME,
                        'label'      => 'Send notification after',
                        'attributes' => [
                            'options' => [
                                [
                                    'value' => ModuleSettings::SEND_NOTIFICATION_AFTER_FIRST_SCAN,
                                    'label' => $this->module->l('Label has passed first scan', 'orderform'),
                                ],
                                [
                                    'value' => ModuleSettings::SEND_NOTIFICATION_AFTER_PRINTED,
                                    'label' => $this->module->l('Label is printed', 'orderform'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SWITCH,
                        'name'       => Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
                        'label'      => 'Automatic set order state to ‘sent’ for digital stamp',
                        'trueLabel'  => 'Enabled',
                        'falseLabel' => 'Disabled',
                    ],
                ],
            ],
            [
                'label'    => 'Customs settings',
                'name'     => 'customs-settings',
                'children' => [
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
                        'label'      => 'Default customs form',
                        'attributes' => [
                            'options' => [
                                [
                                    'value' => Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_ADD,
                                    'label' => $this->module->l('Add this product to customs form', 'customsform'),
                                ],
                                [
                                    'value' => Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_SKIP,
                                    'label' => $this->module->l('Skip this product on customs form', 'customsform'),
                                ],
                            ],
                        ],
                    ],
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'  => Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME,
                        'label' => 'Default customs code',
                    ],
                    [
                        'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                        'name'       => Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME,
                        'label'      => 'Default customs origin',
                        'attributes' => [
                            'options' => array_map(static function ($record) {
                                return [
                                    'value' => $record['id_country'],
                                    'label' => $record['name'],
                                ];
                            }, Country::getCountries(Context::getContext()->language->id)),
                        ]                        // TODO implement default country
                    ],
                ],
            ],
            [
                'label'    => 'Checkout settings',
                'name'     => 'checkout-settings',
                'children' => [
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => ModuleSettings::CONFIGURATION_DELIVERY_TITLE,
                        'label'       => 'Delivery Title',
                        'description' => 'Title of the delivery option.',
                    ],
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => ModuleSettings::CONFIGURATION_DELIVERY_STANDARD_TITLE,
                        'label'       => 'Standard delivery title',
                        'description' => ModuleSettings::DELIVERY_TITLE_DESCRIPTION,
                    ],
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => ModuleSettings::CONFIGURATION_DELIVERY_MORNING_TITLE,
                        'label'       => 'Morning delivery title',
                        'description' => ModuleSettings::DELIVERY_TITLE_DESCRIPTION,
                    ],
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => ModuleSettings::CONFIGURATION_DELIVERY_EVENING_TITLE,
                        'label'       => 'Evening delivery title',
                        'description' => ModuleSettings::DELIVERY_TITLE_DESCRIPTION,
                    ],
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'  => ModuleSettings::CONFIGURATION_SATURDAY_DELIVERY_TITLE,
                        'label' => 'Saturday delivery title',
                    ],
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'  => ModuleSettings::CONFIGURATION_SIGNATURE_TITLE,
                        'label' => 'Signature title',
                    ],
                    [
                        'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'  => ModuleSettings::CONFIGURATION_ONLY_RECIPIENT_TITLE,
                        'label' => 'Only recipient title',
                    ],
                    [
                        'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                        'name'        => ModuleSettings::CONFIGURATION_PICKUP_TITLE,
                        'label'       => 'Pickup title',
                        'description' => 'Title of the pickup option.',
                    ],
                ],
            ],
            [
                'label'    => 'Carrier Settings',
                'name'     => 'carrier-settings',
                'children' => $this->getCarriersLayout(),
            ],
        ];

        return $this->expandFormLayout($form);
    }

    public function getCarriersLayout(): array
    {
        $psCarriers      = CarrierConfigurationProvider::getPsCarriers();
        $carrierSettings = [];

        foreach ($psCarriers as $psCarrier) {
            $carrierSettings[] = $this->getFormForPsCarrier($psCarrier);
        }

        return [
            [
                'type'   => 'submit',
                'label'  => 'Add Carrier',
                'action' => ['showModal', 'addCarrier'],
            ],
            [
                'type'     => 'accordion',
                'children' => $carrierSettings,
            ],
        ];
    }

    private function getCarrierAllowedPackageTypes(AbstractConsignment $consignment): array
    {
        $packageTypes = [];

        foreach (AbstractConsignment::PACKAGE_TYPES_NAMES as $name) {
            if ($consignment->canHavePackageType($name)) {
                $packageTypes[] = [
                    'value' => AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP[$name],
                    'label' => CarrierOptionsCalculator::PACKAGE_TYPE_NAMES_OPTIONS[$name],
                ];
            }
        }

        return $packageTypes;
    }

    private function getCarrierAllowedPackageFormats(AbstractConsignment $consignment): array
    {
        $formats = [];

        $formats[] = [
            'value' => Constant::PACKAGE_FORMAT_NORMAL,
            'label' => CarrierOptionsCalculator::PACKAGE_FORMAT_OPTIONS[Constant::PACKAGE_FORMAT_NORMAL],
        ];
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_LARGE_FORMAT)) {
            $formats[] = [
                'value' => Constant::PACKAGE_FORMAT_LARGE,
                'label' => CarrierOptionsCalculator::PACKAGE_FORMAT_OPTIONS[Constant::PACKAGE_FORMAT_LARGE],
            ];
        }
        if (count($formats) > 1) {
            $formats[] = [
                'value' => Constant::PACKAGE_FORMAT_AUTOMATIC,
                'label' => CarrierOptionsCalculator::PACKAGE_FORMAT_OPTIONS[Constant::PACKAGE_FORMAT_AUTOMATIC],
            ];
        }

        return $formats;
    }

    private function getCarrierLayoutDeliveryChildren(string $psCarrierId, AbstractConsignment $consignment): array
    {
        $children = [];

        $children[] = [
            'label'      => 'Default package type',
            'type'       => ModuleSettings::FIELD_TYPE_SELECT,
            'name'       => $this->carrierFieldName($psCarrierId, Constant::PACKAGE_TYPE_CONFIGURATION_NAME),
            'attributes' => [
                'options' => $this->getCarrierAllowedPackageTypes($consignment),
            ],
        ];
        $children[] = [
            'label'      => 'Default package format',
            'type'       => ModuleSettings::FIELD_TYPE_SELECT,
            'name'       => $this->carrierFieldName($psCarrierId, Constant::PACKAGE_FORMAT_CONFIGURATION_NAME),
            'attributes' => [
                'options' => $this->getCarrierAllowedPackageFormats($consignment),
            ],
        ];
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_ONLY_RECIPIENT)) {
            $children[] = [
                'label' => 'Delivery only to recipient',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, Constant::ONLY_RECIPIENT_CONFIGURATION_NAME),
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_AGE_CHECK)) {
            $children[] = [
                'label' => 'Age check',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, Constant::AGE_CHECK_CONFIGURATION_NAME),
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_RETURN)) {
            $children[] = [
                'label' => 'Return package when recipient is not home',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, Constant::RETURN_PACKAGE_CONFIGURATION_NAME),
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_SIGNATURE)) {
            $children[] = [
                'label' => 'Recipient need to sign',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME),
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_INSURANCE)) {
            $children[] = [
                'label'       => 'Always insure package',
                'type'        => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'        => $this->carrierFieldName($psCarrierId, Constant::INSURANCE_CONFIGURATION_NAME),
                'description' => 'Package will be insured according to below settings when Always insure package is on, or any product in the order has insurance set to on.',
            ];
            $children[] = [
                'label'      => 'Insure from price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, Constant::INSURANCE_CONFIGURATION_FROM_PRICE),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
            $children[] = [
                'label'      => 'Max insured amount',
                'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                'name'       => $this->carrierFieldName($psCarrierId, Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT),
                'attributes' => [
                    'options' => $this->getCarrierInsurancePossibilities($consignment),
                ],
            ];
        }

        return $children;
    }

    private function getCarrierInsurancePossibilities(AbstractConsignment $consignment): array
    {
        try {
            $insurancePossibilities = array_merge(
                Constant::DEFAULT_INSURANCE_POSSIBILITIES,
                $consignment->getInsurancePossibilities()
            );
        } catch (\Throwable $e) {
            $insurancePossibilities = Constant::DEFAULT_INSURANCE_POSSIBILITIES;
        }
        return array_map(
            static function ($value) {
                return [
                    'value' => (string) $value,
                    'label' => '€ ' . $value,
                ];
            },
            $insurancePossibilities
        );
    }

    private function getCarrierLayoutCheckoutChildren(string $psCarrierId, AbstractConsignment $consignment): array
    {
        $children = [];

        $children[] = [
            'label'      => 'Drop off days', // todo add cutoff time...
            'type'       => ModuleSettings::FIELD_TYPE_SELECT_MULTI,
            'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::DROP_OFF_DAYS),
            'attributes' => [
                'options' => [
                    ['value' => 1, 'label' => 'Monday'],
                    ['value' => 2, 'label' => 'Tuesday'],
                    ['value' => 3, 'label' => 'Wednesday'],
                    ['value' => 4, 'label' => 'Thursday'],
                    ['value' => 5, 'label' => 'Friday'],
                    ['value' => 6, 'label' => 'Saturday'],
                    ['value' => 7, 'label' => 'Sunday'],
                ],
            ],
        ]; // TODO exception schedule

        if ($consignment->canHaveExtraOption(AbstractConsignment::EXTRA_OPTION_DELIVERY_DATE)) {
            $children[] = [
                'label'       => 'Delivery days window',
                'type'        => ModuleSettings::FIELD_TYPE_SELECT,
                'name'        => $this->carrierFieldName($psCarrierId, ModuleSettings::DELIVERY_DAYS_WINDOW),
                'attributes'  => ['options' => $this->getOptionsWithDays(14, 'Hide days'),],
                'description' => 'This option allows the Merchant to set the number of days into the future for which he wants to show his consumers delivery options. For example; If set to 3 (days) in his checkout, a consumer ordering on Monday will see possible delivery options for Tuesday, Wednesday and Thursday (provided there is no drop-off delay, it\'s before the cut-off time and he goes to PostNL on Mondays). Min. is 1 and max. is 14.',
            ];
        }
        $children[] = [
            'label'       => 'Drop off delay',
            'type'        => ModuleSettings::FIELD_TYPE_SELECT,
            'name'        => $this->carrierFieldName($psCarrierId, ModuleSettings::DROP_OFF_DELAY),
            'attributes'  => ['options' => $this->getOptionsWithDays(14, 'No delay'),],
            'description' => 'This option allows the Merchant to set the number of days it takes him to pick, pack and hand in his parcel at PostNL when ordered before the cutoff time. By default this is 0 and max. is 14.',
        ];
        if ($consignment->canHaveExtraOption(AbstractConsignment::EXTRA_OPTION_DELIVERY_MONDAY)) {
            $children[] = [
                'label'       => 'Allow Monday delivery',
                'type'        => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'        => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_MONDAY_DELIVERY),
                'description' => 'Monday delivery is only possible when the package is delivered before 15.00 on Saturday at the designated PostNL locations. Note: To activate Monday delivery value 6 must be given with dropOffDays and value 1 must be given by monday_delivery. On Saturday the cutoffTime must be before 15:00 (14:30 recommended) so that Monday will be shown.',
            ];
            $children[] = [
                'label'      => 'Delivery Monday price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_MONDAY_DELIVERY),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
        }
        if ($consignment->canHaveDeliveryType(AbstractConsignment::DELIVERY_TYPE_MORNING_NAME)) {
            $children[] = [
                'label' => 'Allow morning delivery',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_MORNING_DELIVERY),
            ];
            $children[] = [
                'label'      => 'Delivery morning price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_MORNING_DELIVERY),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
        }
        if ($consignment->canHaveDeliveryType(AbstractConsignment::DELIVERY_TYPE_EVENING_NAME)) {
            $children[] = [
                'label' => 'Allow evening delivery',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_EVENING_DELIVERY),
            ];
            $children[] = [
                'label'      => 'Delivery evening price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_EVENING_DELIVERY),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_SIGNATURE)) {
            $children[] = [
                'label' => 'Allow signature',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_SIGNATURE),
            ];
            $children[] = [
                'label'      => 'Signature price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_SIGNATURE),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
        }
        if ($consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_ONLY_RECIPIENT)) {
            $children[] = [
                'label' => 'Allow only recipient',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_ONLY_RECIPIENT),
            ];
            $children[] = [
                'label'      => 'Only recipient price',
                'type'       => ModuleSettings::FIELD_TYPE_TEXT,
                'name'       => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_ONLY_RECIPIENT),
                'attributes' => ['currencySymbol' => $this->currencySymbol],
            ];
        }
        if ($consignment->canHaveDeliveryType(AbstractConsignment::DELIVERY_TYPE_PICKUP_NAME)) {
            $children[] = [
                'label' => 'Allow pickup points',
                'type'  => ModuleSettings::FIELD_TYPE_SWITCH,
                'name'  => $this->carrierFieldName($psCarrierId, ModuleSettings::ALLOW_PICKUP_POINTS),
            ];
            $children[] = [
                'label'       => 'Pickup price',
                'type'        => ModuleSettings::FIELD_TYPE_TEXT,
                'name'        => $this->carrierFieldName($psCarrierId, ModuleSettings::PRICE_PICKUP),
                'description' => 'It\'s possible to fill in a positive or negative amount. Would you like to give a discount for the use of this feature or would you like to calculate extra costs? If the amount is negative the price will appear green in the checkout.',
                'attributes'  => ['currencySymbol' => $this->currencySymbol],
            ];
        }

        return $children;
    }

    private function carrierFieldName(int $carrierId, string $fieldName): string
    {
        return implode(ModuleSettings::CARRIER_FIELD_SEPARATOR, [$carrierId, $fieldName]);
    }

    private function getOptionsWithDays(int $maxDays, string $labelForZero): array
    {
        $options = [['value' => '0', 'label' => $labelForZero]];
        for ($i = 1; $i < $maxDays + 1; ++$i) {
            $options[] = [
                'value' => (string) $i,
                'label' => sprintf(
                    "%d %s",
                    $i,
                    (1 === $i)
                        ? 'day'
                        : 'days'
                ),
            ];
        }

        return $options;
    }

    private function fillAllValues(array $values): array
    {
        $carrierSettings = CarrierConfigurationProvider::all();

        foreach ($values as $key => $type) {
            if (strpos($key, ModuleSettings::CARRIER_FIELD_SEPARATOR)) {
                $carrierKey = explode(ModuleSettings::CARRIER_FIELD_SEPARATOR, $key);
                [$psCarrierId, $fieldName] = $carrierKey;
                $value = $carrierSettings->where(
                        CarrierConfigurationProvider::COLUMN_ID_CARRIER,
                        '=',
                        $psCarrierId
                    )
                             ->firstWhere(CarrierConfigurationProvider::COLUMN_NAME, '=', $fieldName)['value'] ?? false;
            } else {
                $value = Configuration::get($key);
            }

            $value = false === $value
                ? null
                : $value;

            switch ($type) {
                case ModuleSettings::FIELD_TYPE_SWITCH:
                    $values[$key] = '1' === $value;
                    break;
                case ModuleSettings::FIELD_TYPE_SELECT_MULTI:
                    $values[$key] = explode(',', $value);
                    break;
                default:
                    $values[$key] = $value;
                    break;
            }
        }

        return $values;
    }

    private function getFormValuesContext(): array
    {
        $values = ModuleSettings::CONFIGURATION_FIELDS;

        foreach (CarrierConfigurationProvider::getPsCarriers() as $psCarrier) {
            $psCarrierId = $psCarrier['id_carrier'];

            foreach (ModuleSettings::CARRIER_CONFIGURATION_FIELDS as $name => $type) {
                $fieldName          = implode(ModuleSettings::CARRIER_FIELD_SEPARATOR, [$psCarrierId, $name]);
                $values[$fieldName] = $type;
            }
        }

        return $this->fillAllValues($values);
    }

    /**
     * @return array[]
     */
    private function getOrderStateOptions(): array
    {
        if (isset($this->orderStateOptions)) {
            return $this->orderStateOptions;
        }

        $realOrderStates = OrderState::getOrderStates(Context::getContext()->language->id);
        $orderStates     = array_merge([
            [
                'id_order_state' => 0,
                'name'           => 'Off',
            ],
        ], $realOrderStates);

        $this->orderStateOptions = array_map(static function ($record) {
            return [
                'value' => $record['id_order_state'],
                'label' => $record['name'],
            ];
        }, $orderStates);

        return $this->orderStateOptions;
    }

    /**
     * @param string $field
     *
     * @return bool
     */
    protected function showForPluginVariant(string $field): bool
    {
        if (! $this->module->isNL() && in_array($field, Constant::EXCLUSIVE_FIELDS_NL)) {
            return false;
        }

        if (! $this->module->isBE() && in_array($field, Constant::EXCLUSIVE_FIELDS_BE)) {
            return false;
        }

        return true;
    }

    private function getAddCarrierFormLayoutContext(): array
    {
        return [
            'label'    => 'Add carrier',
            'name'     => 'add-carrier',
            'children' => [
                [
                    'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                    'name'       => Constant::ADD_CARRIER_PS_CARRIERS,
                    'label'      => 'Select PS Carriers',
                    'attributes' => [
                        'options' => array_map(
                            static function ($carrier) {
                                return [
                                    'value' => $carrier['id_carrier'],
                                    'label' => $carrier['name'],
                                ];
                            },
                            CarrierConfigurationProvider::getPsCarriers()
                        ),
                    ],
                ],
                [
                    'type'  => ModuleSettings::FIELD_TYPE_TEXT,
                    'name'  => Constant::ADD_CARRIER_NAME,
                    'label' => 'Carrier Name',
                ],
                [
                    'type'       => ModuleSettings::FIELD_TYPE_SELECT,
                    'name'       => Constant::ADD_CARRIER_TYPE,
                    'label'      => 'Carrier Option',
                    'attributes' => [
                        'options' => array_map(
                            static function ($carrier) {
                                return [
                                    'value' => $carrier->getName(),
                                    'label' => $carrier->getHuman(),
                                ];
                            },
                            $this->getCarrierType()
                                ->toArray()
                        ),
                    ],
                ],
            ],
        ];
    }

    private function getCarrierType(): Collection
    {
        return AccountSettings::getInstance()
            ->getEnabledCarriers();
    }

    /**
     * @param       $psCarrier
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    public function getFormForPsCarrier($psCarrier): array
    {
        $psCarrierId = $psCarrier['id_carrier'] ?? 0;
        $carrierType = CarrierConfigurationProvider::get((int) $psCarrierId, 'carrierType');
        $consignment = ConsignmentFactory::createByCarrierName($carrierType)
            ->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);

        $children = [];

        $children[] = [
            'label'    => 'Checkout delivery form',
            'type'     => 'tab',
            'children' => $this->getCarrierLayoutCheckoutChildren($psCarrierId, $consignment),
        ];
        $children[] = [
            'label'    => 'Delivery',
            'type'     => 'tab',
            'children' => $this->getCarrierLayoutDeliveryChildren($psCarrierId, $consignment),
        ];

        if ($consignment->canHaveExtraOption(AbstractConsignment::SHIPMENT_OPTION_RETURN)) {
            $children[] = [
                'label'    => 'Return',
                'type'     => 'tab',
                'children' => $this->getCarrierLayoutDeliveryChildren($psCarrierId, $consignment),
            ];
        }

        return [
            'label'    => $psCarrier['name'] ?? '',
            'type'     => 'tab',
            'name'     => $psCarrier['id_carrier'] + mt_rand(),
            'children' => $children,
        ];
    }
}

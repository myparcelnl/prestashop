<?php

namespace Gett\MyparcelBE\Module\Configuration\Form;

use AdminController;
use Configuration;
use Context;
use Currency;
use Db;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Database\Table;
use Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Group;
use HelperForm;
use HelperList;
use Language;
use Link;
use MyParcelBE;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use PrestaShop\PrestaShop\Adapter\Entity\Carrier;
use PrestaShopDatabaseException;
use PrestaShopException;
use PrestaShopLogger;
use RangePrice;
use RangeWeight;
use Validate;
use Zone;

class CarriersForm extends AbstractForm
{
    /**
     * @var \Context
     */
    private $context;

    public function __construct(MyParcelBE $module)
    {
        parent::__construct($module);
        $this->context = Context::getContext();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function render(): string
    {
        if (Tools::isSubmit('submitMyparcelCarrierSettings')
            || Tools::isSubmit('submitMyparcelCarrierSettingsAndStay')) {
            $carrierId = Tools::getValue('id_carrier');
            $this->updateConfigurationFields($carrierId);
        }

        if (! empty($this->context->controller->errors)
            || Tools::isSubmit('updatecarrier')
            || Tools::isSubmit('submitMyparcelCarrierSettingsAndStay')) {
            return $this->getForm();
        }

        if (Tools::isSubmit('addNewMyparcelCarrierSettings')
            || Tools::isSubmit('submitAddMyparcelCarrierSettingsAndStay')) {
            $this->createNewCarrier();
            $this->context->cookie->{'myparcelbe.message'} = $this->module->l(
                'A new carrier has been added to myparcel'
            );
            $this->redirectToCarrierList();
        }

        if (! empty($this->context->controller->errors)
            || Tools::isSubmit('addcarrier')) {
            return $this->getForm(true);
        }

        return $this->getMessage() . $this->getList();
    }

    protected function redirectToCarrierList(): void
    {
        // Redirect back to list
        Tools::redirectAdmin((new Link())->getAdminLink('AdminModules', true, [], [
            'configure' => 'myparcelbe',
            'tab_module' => 'shipping_logistics',
            'module_name' => 'myparcelbe',
            'menu' => 5,
        ]));
    }

    protected function getMessage(): string
    {
        if (!isset($this->context->cookie)) {
            return '';
        }

        $message = $this->context->cookie->{'myparcelbe.message'};

        if ($message) {
            $message = $this->module->displayConfirmation($message);
        }

        unset($this->context->cookie->{'myparcelbe.message'});

        return $message ?? '';
    }

    protected function getLegend(): string
    {
        return '';
    }

    protected function getFields(): array
    {
        return [];
    }

    protected function getNamespace(): string
    {
        return 'carriersform';
    }

    protected function addCarrier($configuration)
    {
        $carrier = new Carrier();

        $carrier->name = $configuration['name'];
        $carrier->is_module = true;
        $carrier->active = 1;
        $carrier->range_behavior = 1;
        $carrier->need_range = 1;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->external_module_name = $this->module->name;
        $carrier->shipping_method = 2;

        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = 'Super fast delivery';
        }

        try {
            if ($carrier->add()) {
                @copy(
                    _PS_MODULE_DIR_ . 'myparcel/views/images/' . $configuration['image'],
                    _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg'
                );

                return $carrier;
            }
        } catch (PrestaShopDatabaseException $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[MYPARCEL] PrestaShopDatabaseException carrier "%s" install: %s',
                    ($configuration['name'] ?? 'empty'),
                    $e->getMessage()
                ),
                1,
                null,
                'Cart',
                $carrier->id ?? null,
                true
            );
        } catch (PrestaShopException $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[MYPARCEL] PrestaShopException carrier "%s" install: %s',
                    ($configuration['name'] ?? 'empty'),
                    $e->getMessage()
                ),
                1,
                null,
                'Cart',
                $carrier->id ?? null,
                true
            );
        }

        return false;
    }

    protected function addGroups($carrier): void
    {
        $groups_ids = [];
        $groups = Group::getGroups(Context::getContext()->language->id);
        foreach ($groups as $group) {
            $groups_ids[] = $group['id_group'];
        }

        $carrier->setGroups($groups_ids);
    }

    protected function addRanges($carrier): void
    {
        $range_price             = new RangePrice();
        $range_price->id_carrier = $carrier->id;
        $range_price->delimiter1 = '0';
        $range_price->delimiter2 = '10000';
        $range_price->add();

        $range_weight             = new RangeWeight();
        $range_weight->id_carrier = $carrier->id;
        $range_weight->delimiter1 = '0';
        $range_weight->delimiter2 = '10000';
        $range_weight->add();
    }

    protected function addZones($carrier): void
    {
        $zones = Zone::getZones();

        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }
    }

    /**
     * @param  int  $carrierId
     * @param  bool $isInsert
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function updateConfigurationFields(int $carrierId, bool $isInsert = false): void
    {
        $dropOff = [];
        $postFields = Tools::getAllValues();

        $carrierType = CarrierConfigurationProvider::get($carrierId, 'carrierType');

        // if carrier is not found and not insert, should be insert
        if (is_null($carrierType) && !$isInsert) {
            $isInsert = true;
        }

        foreach ($postFields as $key => $value) {
            if (stripos($key, 'dropOffDays') !== false) {
                $temp = explode('_', $key);
                $dropOff[] = end($temp);
            }
        }
        $postFields['dropOffDays'] = '';
        if (!empty($dropOff)) {
            $postFields['dropOffDays'] = implode(',', $dropOff);
        }

        $insert = [];

        foreach (Constant::CARRIER_CONFIGURATION_FIELDS as $field) {
            if (!isset($postFields[$field]) && $field === 'carrierType') {
                continue;
            }

            $updatedValue = $postFields[$field] ?? '';

            if (stripos($field, 'price') === 0) {
                $price = $updatedValue = Tools::normalizeFloat($updatedValue);
                if (! empty($price) && ! Validate::isFloat($price)) {
                    switch ($field) {
                        case 'priceMondayDelivery':
                            $label = $this->module->l('Delivery Monday price', 'carriers');

                            break;

                        case 'priceMorningDelivery':
                            $label = $this->module->l('Delivery morning price', 'carriers');

                            break;

                        case 'priceEveningDelivery':
                            $label = $this->module->l('Delivery evening price', 'carriers');

                            break;

                        case 'priceSaturdayDelivery':
                            $label = $this->module->l('Delivery Saturday price', 'carriers');

                            break;

                        case 'priceSignature':
                            $label = $this->module->l('Signature price', 'carriers');

                            break;

                        case 'priceOnlyRecipient':
                            $label = $this->module->l('Only recipient price', 'carriers');

                            break;

                        case 'pricePickup':
                            $label = $this->module->l('Pickup price', 'carriers');

                            break;

                        default:
                            $label = $this->module->l('Price field', 'carriers');

                            break;
                    }
                    $this->context->controller->errors[] = sprintf(
                        $this->module->l('Wrong price format for %s', 'carriers'),
                        $label
                    );

                    continue;
                }
            }

            if ($isInsert) {
                $insert[] = [
                    'id_carrier' => $carrierId,
                    'name'       => pSQL($field),
                    'value'      => pSQL($updatedValue),
                ];
            } else {
                CarrierConfigurationProvider::updateValue($carrierId, $field, $updatedValue);
            }
        }

        if ($isInsert) {
            Db::getInstance()->insert(Table::TABLE_CARRIER_CONFIGURATION, $insert);
        }

        $carrier = new Carrier($carrierId);

        if ($carrier->external_module_name !== $this->module->name) {
            $carrier->external_module_name = 'myparcelbe';
            $carrier->is_module            = true;
            $carrier->active               = 1;
            $carrier->need_range           = 1;
            $carrier->shipping_external    = true;
            $carrier->range_behavior       = 0;
            $carrier->shipping_method      = 2;
            $carrier->update();
        }
    }

    /**
     * @param  bool $isNew
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private function getForm(bool $isNew = false): string
    {
        $carrierName = $isNew
            ? $this->module->l('Add new Carrier', 'carriers')
            : $this->module->l(
                'Carriers',
                'carriers'
            );

        $carrierId = (int) Tools::getValue('id_carrier');
        $carrier   = new Carrier($carrierId, $this->context->language->id);

        if (! empty($carrier->name)) {
            $carrierName = $carrier->name;
        }

        $carrierType = $this->exclusiveField->getCarrierType($carrier);
        $countryIso  = $this->module->getModuleCountry();
        $tabs        = [];

        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'ALLOW_STANDARD_FORM')) {
            $tabs['form'] = $this->module->l('Checkout delivery form', 'carriers');
        }

        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'ALLOW_DELIVERY_FORM') && ! $isNew) {
            $tabs['delivery'] = $this->module->l('Delivery', 'carriers');
        }

        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'ALLOW_RETURN_FORM') && ! $isNew) {
            $tabs['return'] = $this->module->l('Return', 'carriers');
        }

        $fields = [
            'form' => [
                'legend' => [
                    'title' => $carrierName,
                    'icon'  => 'icon-truck',
                ],
                'tabs'   => $tabs,
                'input'  => $this->getFormInputs($carrier, $isNew),
                'submit' => [
                    'title' => $this->module->l('Save', 'carriers'),
                ],
            ],
        ];

        // Add save and stay if not new
        if (! $isNew) {
            $fields['form']['buttons'] = [
                'save-and-stay' => [
                    'title' => $this->module->l('Save and stay', 'carriers'),
                    'name'  => 'submitMyparcelCarrierSettingsAndStay',
                    'type'  => 'submit',
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-save',
                ],
            ];
        }

        $helper = new HelperForm();

        $helper->show_toolbar             = false;
        $helper->module                   = $this->module;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->submit_action = $isNew ? 'addNewMyparcelCarrierSettings' : 'submitMyparcelCarrierSettings';

        $helper->currentIndex = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&id_carrier=' . (int) $carrier->id
            . '&menu=' . Tools::getValue('menu', 0)
            . '&' . ($isNew ? 'addcarrier' : 'updatecarrier');

        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $table          = Table::withPrefix(Table::TABLE_CARRIER_CONFIGURATION);
        $carrierConfigs = Db::getInstance()
            ->executeS(
                <<<SQL
SELECT *
FROM `$table`
WHERE `id_carrier` = $carrierId
SQL

            );

        $vars = [];

        if ($isNew) {
            $carrierConfigs = [];
            $configFields   = Constant::CARRIER_CONFIGURATION_FIELDS;

            $configFields[] = 'carrierName';
            $configFields[] = 'psCarriers';

            foreach ($configFields as $field) {
                $carrierConfigs[] = [
                    'id_configuration' => 0,
                    'id_carrier'       => 0,
                    'name'             => $field,
                    'value'            => '',
                ];
            }
        }

        foreach ($carrierConfigs as $row) {
            if ($row['name'] == 'dropOffDays') {
                $temp = explode(',', $row['value']);
                foreach ($temp as $value) {
                    $vars['dropOffDays_' . $value] = 1;
                }

                continue;
            }

            if ($row['name'] == Constant::CUTOFF_EXCEPTIONS) {
                if (empty($row['value'])) {
                    $row['value'] = '{}';
                } else {
                    //TODO remove this once the pc_myparcelbe_carrier_configuration can hold all data for 'value'
                    // (issue #15)
                    json_decode($row['value'], true);
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        $row['value'] = '{}';
                    }
                }
            }

            $vars[$row['name']] = $row['value'];
        }

        $vars['id_carrier'] = $carrierId;

        $this->setExclusiveFieldsValues($carrier, $vars);

        $helper->tpl_vars = [
            'fields_value' => $vars,
        ];

        return $helper->generateForm([$fields]);
    }

    /**
     * @return false|string
     * @throws \PrestaShopDatabaseException
     */
    private function getList()
    {
        $fieldsList = [
            'id_carrier' => [
                'title' => $this->module->l('ID', 'carriers'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'search' => false,
            ],
            'name' => [
                'title' => $this->module->l('Name', 'carriers'),
                'search' => false,
            ],
        ];

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->actions = ['edit'];
        $helper->show_toolbar = true;
        $helper->toolbar_btn = [
            'new' => [
                'desc' => 'Add new carrier',
                'imgclass' => 'new',
                'href' => AdminController::$currentIndex
                . '&configure=' . $this->module->name
                . '&menu=' . Tools::getValue('menu', 0)
                . '&addcarrier='
                . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
        ];
        $helper->module = $this;
        $helper->has_bulk_actions = false;
        $helper->identifier = 'id_carrier';
        $helper->title = $this->module->l('Delivery options', 'carriers');
        $helper->table = 'carrier';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name . '&menu=' . Tools::getValue(
            'menu',
            0
        );
        $helper->colorOnBackground = true;
        $helper->no_link = true;

        $table = Table::withPrefix('carrier');
        $list = Db::getInstance()
            ->executeS(
                <<<SQL
SELECT *
FROM $table
WHERE external_module_name = '{$this->module->name}'
         AND deleted = 0 
         ORDER BY position
         LIMIT 0, 50
SQL
            );

        return $helper->generateList($list, $fieldsList);
    }

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\Carrier $carrier
     * @param  bool                                          $isNew
     *
     * @return array
     * @throws \Exception
     */
    private function getFormInputs(Carrier $carrier, bool $isNew = false): array
    {
        $currency = Currency::getDefaultCurrency();
        $fields   = [];

        $showCarrierTypeOption = false;

        if ($isNew) {
            // Get ps carrier config
            $carriers = [];

            $psCarriers = Carrier::getCarriers($this->context->language->id, true, false, false);

            foreach ($psCarriers as $pscarrier) {
                $carriers[] = [
                    'id_carrier' => $pscarrier['id_carrier'],
                    'name'       => $pscarrier['name'],
                ];
            }

            array_unshift($carriers, [
                'id_carrier' => 0,
                'name'       => $carriers ? 'Select from ps carriers' : '--',
            ]);

            $fields[] = [
                'tab'     => 'form',
                'type'    => 'select',
                'label'   => $this->module->l('Select PS Carriers', 'carriers'),
                'name'    => 'psCarriers',
                'options' => [
                    'query' => $carriers,
                    'id'    => 'id_carrier',
                    'name'  => 'name',
                ],
            ];

            $fields[] = [
                'tab'   => 'form',
                'type'  => 'text',
                'label' => $this->module->l('Carrier Name', 'carriers'),
                'desc'  => $this->module->l('Create new carrier', 'carriers'),
                'name'  => 'carrierName',
            ];

            $showCarrierTypeOption = true;
        }

        // Only if ps carrier
        if ($idCarrier = Tools::getValue('id_carrier')) {
            $psCarriersConfig = (array) json_decode(Configuration::get('MYPARCEL_PSCARRIERS'));
            $carriers         = array_keys($psCarriersConfig);

            if (in_array($idCarrier, $carriers)) {
                $showCarrierTypeOption = true;
            }
        }

        if ($showCarrierTypeOption) {
            $fields[] = [
                'tab'     => 'form',
                'type'    => 'select',
                'label'   => $this->module->l('Carrier Option', 'carriers'),
                'name'    => 'carrierType',
                'options' => [
                    'query' => $this->getCarrierType(),
                    'id'    => 'configuration_name',
                    'name'  => 'name',
                ],
            ];
        }

        $formTabFields     = $this->getFormTabFields($carrier, $currency);
        $deliveryTabFields = [];
        $returnTabFields   = [];

        if (! $isNew) {
            $deliveryTabFields = $this->getExtraTabFields($carrier);
            $returnTabFields   = $this->getExtraTabFields($carrier, 'return');
        }

        return array_merge($fields, $formTabFields, $deliveryTabFields, $returnTabFields);
    }

    /**
     * @return array[]
     */
    private function getCarrierType(): array
    {
        if ($this->module->isBE()) {
            return [
                ['name' => CarrierBpost::HUMAN, 'configuration_name' => CarrierBpost::NAME],
                ['name' => CarrierDPD::HUMAN, 'configuration_name' => CarrierDPD::NAME],
                ['name' => CarrierPostNL::HUMAN, 'configuration_name' => CarrierPostNL::NAME],
            ];
        }

        return [
            ['name' => CarrierPostNL::HUMAN, 'configuration_name' => CarrierPostNL::NAME],
        ];
    }

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\Carrier $carrier
     * @param  \Currency                                     $currency
     *
     * @return array
     */
    private function getFormTabFields(Carrier $carrier, Currency $currency): array
    {
        $fields      = [];
        $carrierType = $this->exclusiveField->getCarrierType($carrier);
        $countryIso  = $this->module->getModuleCountry();

        if (! $this->exclusiveField->isAvailable($countryIso, $carrierType, 'ALLOW_STANDARD_FORM')) {
            return $fields;
        }

        $deliveryDaysOptions = [
            [
                'id'   => -1,
                'name' => $this->module->l('Hide days', 'carriers'),
            ],
        ];

        for ($i = 1; $i < 15; ++$i) {
            $deliveryDaysOptions[] = [
                'id'   => $i,
                'name' => sprintf($this->module->l('%d days', 'carriers'), $i),
            ];
        }

        $dropOffDelayOptions = [
            [
                'id'   => 0,
                'name' => $this->module->l('No delay', 'carriers'),
            ],
            [
                'id'   => 1,
                'name' => $this->module->l('1 day', 'carriers'),
            ],
        ];

        for ($i = 2; $i < 15; ++$i) {
            $dropOffDelayOptions[] = [
                'id'   => $i,
                'name' => sprintf($this->module->l('%d days', 'carriers'), $i),
            ];
        }

        $cutoffTimeValues = [];
        foreach (Constant::WEEK_DAYS as $index => $day) {
            $cutoffTimeValues[$index] = [
                'name'   => $day . 'CutoffTime',
                'class'  => 'cutoff-time-pseudo',
                'prefix' => $this->module->l('Cutoff Time', 'carriers'),
            ];
        }

        $fields[] = [
            'tab'              => 'form',
            'type'             => 'checkbox',
            'multiple'         => true,
            'label'            => $this->module->l('Drop off days', 'carriers'),
            'name'             => 'dropOffDays',
            'form_group_class' => 'with-cutoff-time',
            'values'           => [
                'query' => [
                    ['day_number' => 1, 'name' => $this->module->l('Monday', 'carriers')],
                    ['day_number' => 2, 'name' => $this->module->l('Tuesday', 'carriers')],
                    ['day_number' => 3, 'name' => $this->module->l('Wednesday', 'carriers')],
                    ['day_number' => 4, 'name' => $this->module->l('Thursday', 'carriers')],
                    ['day_number' => 5, 'name' => $this->module->l('Friday', 'carriers')],
                    ['day_number' => 6, 'name' => $this->module->l('Saturday', 'carriers')],
                    ['day_number' => 7, 'name' => $this->module->l('Sunday', 'carriers')],
                ],
                'id'    => 'day_number',
                'name'  => 'name',
            ],
            'cutoff_time'      => $cutoffTimeValues,
            'desc'             => [
                sprintf(
                    $this->module->l(
                        'This option allows the Merchant to set the days he normally goes to %s to hand in the
                        parcels. Monday is 1 and Saturday is 6.',
                        'carriers'
                    ),
                    $carrier->name
                ),
                sprintf(
                    $this->module->l(
                        'The Cutoff Time option allows the Merchant to indicate the latest cut-off time before an order will
                        still be picked, packed and dispatched on the same/first set dropoff day, taking into account
                        the dropoff-delay. Industry standard default time is 17:00. For example, if cutoff time is
                        17:00, Monday is a delivery day and there\'s no delivery delay; all orders placed Monday
                        before 17:00 will be dropped of at %s on that same Monday in time for the Monday collection
                        and delivery on Tuesday.',
                        'carriers'
                    ),
                    $carrier->name
                ),
            ],
        ];

        foreach (Constant::WEEK_DAYS as $day) {
            $fields[] = [
                'tab'  => 'form',
                'type' => 'hidden',
                'name' => $day . 'CutoffTime',
            ];
        }

        $fields[] = [
            'tab'   => 'form',
            'type'  => 'cutoffexceptions',
            'label' => $this->module->l('Exception schedule', 'carriers'),
            'name'  => Constant::CUTOFF_EXCEPTIONS,
        ];

        $fields[] = [
            'tab'     => 'form',
            'type'    => 'select',
            'label'   => $this->module->l('Delivery days window', 'carriers'),
            'name'    => 'deliveryDaysWindow',
            'options' => [
                'query' => $deliveryDaysOptions,
                'id'    => 'id',
                'name'  => 'name',
            ],
            'desc'    => sprintf(
                $this->module->l(
                    'This option allows the Merchant to set the number of days into the future for which he wants to
                show his consumers delivery options. For example; If set to 3 (days) in his checkout, a consumer
                ordering on Monday will see possible delivery options for Tuesday, Wednesday and Thursday (provided
                there is no drop-off delay, it\'s before the cut-off time and he goes to %s on Mondays). Min. is
                1 and max. is 14.',
                    'carriers'
                ),
                $carrier->name
            ),
        ];

        $fields[] = [
            'type'    => 'select',
            'label'   => $this->module->l('Drop off delay', 'carriers'),
            'name'    => 'dropOffDelay',
            'tab'     => 'form',
            'options' => [
                'query' => $dropOffDelayOptions,
                'id'    => 'id',
                'name'  => 'name',
            ],
            'desc'    => sprintf(
                $this->module->l(
                    'This option allows the Merchant to set the number of days it takes him to pick, pack and hand in
                his parcel at %s when ordered before the cutoff time. By default this is 0 and max. is 14.',
                    'carriers'
                ),
                $carrier->name
            ),
        ];

        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowMondayDelivery')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'label'            => $this->module->l('Allow Monday delivery', 'carriers'),
                'name'             => 'allowMondayDelivery',
                'desc'             => sprintf(
                    $this->module->l(
                        'Monday delivery is only possible when the package is delivered before 15.00 on Saturday at
                    the designated %s locations. Note: To activate Monday delivery value 6 must be given with
                    dropOffDays and value 1 must be given by monday_delivery. On Saturday the cutoffTime must be before
                    15:00 (14:30 recommended) so that Monday will be shown.',
                        'carriers'
                    ),
                    $carrier->name
                ),
                'values'           => [
                    [
                        'id'    => 'allowMondayDelivery_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowMondayDelivery_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Delivery Monday price', 'carriers'),
                'name'             => 'priceMondayDelivery',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowMondayDelivery',
            ];
        }

        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowMorningDelivery')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'values'           => [
                    [
                        'id'    => 'allowMorningDelivery_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowMorningDelivery_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label'            => $this->module->l('Allow morning delivery', 'carriers'),
                'name'             => 'allowMorningDelivery',
                'desc'             => sprintf(
                    $this->module->l(
                        'Monday delivery is only possible when the package is delivered before 15.00 on Saturday at the
                    designated %s locations. Note: To activate Monday delivery value 6 must be given with
                    dropOffDays and value 1 must be given by monday_delivery. On Saturday the cutoffTime must be before
                    15:00 (14:30 recommended) so that Monday will be shown.',
                        'carriers'
                    ),
                    $carrier->name
                ),
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Delivery morning price', 'carriers'),
                'name'             => 'priceMorningDelivery',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowMorningDelivery',
            ];
        }
        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowEveningDelivery')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'label'            => $this->module->l('Allow evening delivery', 'carriers'),
                'name'             => 'allowEveningDelivery',
                'values'           => [
                    [
                        'id'    => 'allowEveningDelivery_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowEveningDelivery_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Delivery evening price', 'carriers'),
                'name'             => 'priceEveningDelivery',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowEveningDelivery',
            ];
        }
        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowSaturdayDelivery')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'values'           => [
                    [
                        'id'    => 'allowSaturdayDelivery_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowSaturdayDelivery_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label'            => $this->module->l('Allow Saturday delivery', 'carriers'),
                'name'             => 'allowSaturdayDelivery',
                'desc'             => sprintf(
                    $this->module->l(
                        'Saturday delivery is only possible when the package is delivered before 15:00 on Friday
                    at the designated %s locations. Note: To allow Saturday delivery, Friday must be enabled in
                    Drop-off days.',
                        'carriers'
                    ),
                    $carrier->name
                ),
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Delivery Saturday price', 'carriers'),
                'name'             => 'priceSaturdayDelivery',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowSaturdayDelivery',
            ];
        }
        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowSignature')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'label'            => $this->module->l('Allow signature', 'carriers'),
                'name'             => 'allowSignature',
                'values'           => [
                    [
                        'id'    => 'allowSignature_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowSignature_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Signature price', 'carriers'),
                'name'             => 'priceSignature',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowSignature',
            ];
        }
        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowOnlyRecipient')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'label'            => $this->module->l('Allow only recipient', 'carriers'),
                'name'             => 'allowOnlyRecipient',
                'values'           => [
                    [
                        'id'    => 'allowOnlyRecipient_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowOnlyRecipient_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Only recipient price', 'carriers'),
                'name'             => 'priceOnlyRecipient',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'form_group_class' => 'toggle-child-field allowOnlyRecipient',
            ];
        }
        if ($this->exclusiveField->isAvailable($countryIso, $carrierType, 'allowPickupPoints')) {
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'switch',
                'is_bool'          => true,
                'label'            => $this->module->l('Allow pickup points', 'carriers'),
                'name'             => 'allowPickupPoints',
                'values'           => [
                    [
                        'id'    => 'allowPickupPoints_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id'    => 'allowPickupPoints_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'form_group_class' => 'toggle-parent-field',
            ];
            // Disable price automatically when the option is not available
            $fields[] = [
                'tab'              => 'form',
                'type'             => 'text',
                'label'            => $this->module->l('Pickup price', 'carriers'),
                'name'             => 'pricePickup',
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
                'desc'             => $this->module->l(
                    'It\'s possible to fill in a positive or negative amount. Would you like to give a discount
                    for the use of this feature or would you like to calculate extra costs? If the amount is negative
                    the price will appear green in the checkout.',
                    'carriers'
                ),
                'form_group_class' => 'toggle-child-field allowPickupPoints',
            ];
        }

        return $fields;
    }

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\Carrier $carrier
     * @param  string                                        $prefix
     *
     * @return array
     * @throws \Exception
     */
    private function getExtraTabFields(Carrier $carrier, string $prefix = ''): array
    {
        $fields      = [];
        $countryIso  = $this->module->getModuleCountry();
        $tabName     = 'ALLOW_DELIVERY_FORM';
        $tabId       = 'delivery';

        if ('return' === $prefix) {
            $tabName = 'ALLOW_RETURN_FORM';
            $tabId   = $prefix;
            $prefix  .= '_';
        }

        $myParcelCarrier = CarrierService::getMyParcelCarrier($carrier->id);
        $carrierType     = $myParcelCarrier->getName();

        if (! $this->exclusiveField->isAvailable($countryIso, $carrierType, $tabName)) {
            return $fields;
        }

        $carrierOptionsCalculator = (new CarrierOptionsCalculator($myParcelCarrier));

        $fields[] = [
            'tab'     => $tabId,
            'type'    => 'select',
            'label'   => $this->module->l('Default package type', 'carriers'),
            'name'    => $prefix . Constant::PACKAGE_TYPE_CONFIGURATION_NAME,
            'options' => [
                'query' => $carrierOptionsCalculator->getAvailablePackageTypes($prefix),
                'id'    => 'value',
                'name'  => 'label',
            ],
        ];

        $fields[] = [
            'tab'     => $tabId,
            'type'    => 'select',
            'label'   => $this->module->l('Default package format', 'carriers'),
            'name'    => $prefix . Constant::PACKAGE_FORMAT_CONFIGURATION_NAME,
            'options' => [
                'query' => $carrierOptionsCalculator->getAvailablePackageFormats($prefix),
                'id'    => 'value',
                'name'  => 'label',
            ],
        ];

        if ($this->exclusiveField->isAvailable(
            $countryIso,
            $carrierType,
            $prefix . Constant::ONLY_RECIPIENT_CONFIGURATION_NAME
        )) {
            $fields[] = [
                'tab' => $tabId,
                'type' => 'switch',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $prefix . Constant::ONLY_RECIPIENT_CONFIGURATION_NAME . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id' => $prefix . Constant::ONLY_RECIPIENT_CONFIGURATION_NAME . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label' => $this->module->l('Deliver only to recipient', 'carriers'),
                'name' => $prefix . Constant::ONLY_RECIPIENT_CONFIGURATION_NAME,
            ];
        }
        if ($this->exclusiveField->isAvailable(
            $countryIso,
            $carrierType,
            $prefix . Constant::AGE_CHECK_CONFIGURATION_NAME
        )) {
            $fields[] = [
                'tab' => $tabId,
                'type' => 'switch',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $prefix . Constant::AGE_CHECK_CONFIGURATION_NAME . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id' => $prefix . Constant::AGE_CHECK_CONFIGURATION_NAME . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label' => $this->module->l('Age check', 'carriers'),
                'name' => $prefix . Constant::AGE_CHECK_CONFIGURATION_NAME,
            ];
        }
        if ($this->exclusiveField->isAvailable(
            $countryIso,
            $carrierType,
            $prefix . Constant::RETURN_PACKAGE_CONFIGURATION_NAME
        )) {
            $fields[] = [
                'tab' => $tabId,
                'type' => 'switch',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $prefix . Constant::RETURN_PACKAGE_CONFIGURATION_NAME . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id' => $prefix . Constant::RETURN_PACKAGE_CONFIGURATION_NAME . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label' => $this->module->l('Return package when recipient is not home', 'carriers'),
                'name' => $prefix . Constant::RETURN_PACKAGE_CONFIGURATION_NAME,
            ];
        }
        if ($this->exclusiveField->isAvailable(
            $countryIso,
            $carrierType,
            $prefix . Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME
        )) {
            $fields[] = [
                'tab' => $tabId,
                'type' => 'switch',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $prefix . Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id' => $prefix . Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label' => $this->module->l('Recipient need to sign', 'carriers'),
                'name' => $prefix . Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
            ];
        }
        if ($this->exclusiveField->isAvailable(
            $countryIso,
            $carrierType,
            $prefix . Constant::INSURANCE_CONFIGURATION_NAME
        )) {
            $fields[] = [
                'tab' => $tabId,
                'type' => 'switch',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => $prefix . Constant::INSURANCE_CONFIGURATION_NAME . '_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'carriers'),
                    ],
                    [
                        'id' => $prefix . Constant::INSURANCE_CONFIGURATION_NAME . '_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'carriers'),
                    ],
                ],
                'label' => $this->module->l('Always insure package', 'carriers'),
                'name'  => $prefix . Constant::INSURANCE_CONFIGURATION_NAME,
                'desc'  => $this->module->l('Package will be insured according to below settings when Always insure package is on, or any product in the order has insurance set to on.', 'carriers'),
            ];
            try {
                $c = ConsignmentFactory::createByCarrierId($myParcelCarrier->getId());
                $c->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
                $insurancePossibilities = array_merge([0], $c->getInsurancePossibilities());
            } catch (\Throwable $e) {
                $insurancePossibilities = [0];
            }
            $fields[] = [
                'tab'              => $tabId,
                'type'             => 'text',
                'label'            => $this->module->l('Insure from price', 'carriers'),
                'name'             => $prefix . Constant::INSURANCE_CONFIGURATION_FROM_PRICE,
                'suffix'           => $currency->getSign(),
                'class'            => 'col-lg-2',
            ];
            $fields[] = [
                'tab'              => $tabId,
                'type'             => 'select',
                'label'            => $this->module->l('Max insured amount', 'carriers'),
                'name'             => $prefix . Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT,
                'options'          => [
                    'query' => array_map(
                        static function ($value) use ($currency) {
                            return [
                                'value' => $value,
                                'label' => $currency->getSign() . ' ' . $value,
                            ];
                        },
                        $insurancePossibilities
                    ),
                    'id'    => 'value',
                    'name'  => 'label',
                ],
                'class'            => 'col-lg-2',
            ];
        }

        return $fields;
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createNewCarrier(): void
    {
        $carrierType = Tools::getValue('carrierType');
        $carrierName = Tools::getValue('carrierName');

        $image = 'postnl.jpg';

        if ($this->module->isBE()) {
            $image = 'dpd.jpg';
            if ($carrierType == CarrierBpost::NAME) {
                $image = 'bpost.jpg';
            }
        }

        if (Tools::getValue('psCarriers')) {
            $carrier                       = new Carrier(Tools::getValue('psCarriers'));
            $carrier->external_module_name = 'myparcelbe';
            $carrier->is_module            = true;
            $carrier->need_range           = 1;
            $carrier->shipping_external    = true;
            $carrier->update();
        } else {
            $carrier = $this->addCarrier(
                ['name' => $carrierName, 'image' => $image]
            );

            $this->addZones($carrier);
            $this->addGroups($carrier);
            $this->addRanges($carrier);
        }

        $psCarriersConfig               = (array) json_decode(Configuration::get('MYPARCEL_PSCARRIERS'));
        $psCarriersConfig[$carrier->id] = $carrierType;
        Configuration::updateValue('MYPARCEL_PSCARRIERS', json_encode($psCarriersConfig));

        $configurationPsCarriers = CarrierConfigurationProvider::get($carrier->id, 'carrierType');
        if (is_null($configurationPsCarriers)) {
            $this->updateConfigurationFields($carrier->id, true);
        } else {
            $this->updateConfigurationFields($carrier->id);
        }
    }
}

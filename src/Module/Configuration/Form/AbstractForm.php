<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Configuration\Form;

use AdminController;
use Configuration;
use Context;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Module\Carrier\ExclusiveField;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use HelperForm;
use Module;
use MyParcelNL;
use MyParcelNL\Pdk\Base\Service\CountryService;
use PrestaShop\PrestaShop\Adapter\Entity\Carrier;
use Tools;
use Validate;

abstract class AbstractForm
{
    protected const FIELD_TYPE_CHECKBOX = 'checkbox';
    protected const FIELD_TYPE_SELECT   = 'select';
    protected const FIELD_TYPE_SWITCH   = 'switch';
    protected const FIELD_TYPE_TEXT     = 'text';

    /**
     * @var string
     */
    public $name;

    /**
     * @var \Context
     */
    protected $context;

    /**
     * @var \MyParcelNL\Pdk\Base\Service\CountryService
     */
    protected $countryService;

    /**
     * @var Module
     */
    protected $exclusiveField;

    /**
     * @var string
     */
    protected $icon = 'cog';

    /**
     * @var string
     */
    protected $legend;

    /**
     * @var \MyParcelNL
     */
    protected $module;

    /**
     * @param  \MyParcelNL                                 $module
     * @param  \Context                                    $context
     * @param  \MyParcelNL\Pdk\Base\Service\CountryService $countryService
     */
    public function __construct(MyParcelNL $module, Context $context, CountryService $countryService)
    {
        $this->module         = $module;
        $this->context        = $context;
        $this->countryService = $countryService;
        $this->name           = str_replace(' ', '', $module->displayName) . self::class;
        $this->exclusiveField = new ExclusiveField();
    }

    abstract protected function getFields(): array;

    abstract protected function getLegend(): string;

    /**
     * @return string
     */
    abstract protected function getNamespace(): string;

    /**
     * @return string
     */
    public function render(): string
    {
        $helper = new HelperForm();

        // Process prev form
        $resultProcess = $this->process();

        // Module, token and current index
        $helper->module          = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->module->name . '&menu=' . Tools::getValue(
                'menu',
                0
            );

        // Language
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;

        // Field values
        $helper->fields_value = $this->getValues();

        // Title and toolbar
        $helper->title          = $this->module->displayName;
        $helper->show_toolbar   = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action  = $this->name . 'Submit';

        return $resultProcess . $helper->generateForm($this->form());
    }

    /**
     * @param  string $fieldType
     * @param  string $field
     *
     * @return string
     */
    protected function getExclusiveNlFieldType(string $fieldType, string $field): string
    {
        if (! ModuleService::isNl() && in_array($field, Constant::EXCLUSIVE_FIELDS_NL)) {
            $fieldType = 'hidden';
        }

        return $fieldType;
    }

    /**
     * @return array
     */
    protected function getValues(): array
    {
        $values = [];

        foreach ($this->getFieldsNormalized() as $name => $field) {
            $values[$name] = Configuration::get(
                $name,
                null,
                null,
                null,
                $field['default'] ?? null
            );

            if ($name === Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME) {
                $temp = explode(',', $values[$name]);

                foreach ($temp as $value) {
                    $values[Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME . '_' . $value] = 1;
                }
            }
        }

        return $values;
    }

    /**
     * @param  \PrestaShop\PrestaShop\Adapter\Entity\Carrier $carrier
     * @param  array                                         $vars
     *
     * @return void
     */
    protected function setExclusiveFieldsValues(Carrier $carrier, array &$vars): void
    {
        $carrierType = $this->exclusiveField->getCarrierType($carrier);

        foreach (Constant::CARRIER_CONFIGURATION_FIELDS as $field) {
            if (! $this->exclusiveField->isAvailable(ModuleService::getModuleCountry(), $carrierType, $field, 1)) {
                $vars[$field] = 0;
            }
        }
    }

    protected function update(): string
    {
        $result = true;

        foreach (array_keys($this->getFieldsNormalized()) as $name) {
            if ($name == Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME) {
                $ignored = [];
                foreach (Tools::getAllValues() as $key => $value) {
                    if (stripos($key, Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME) !== false) {
                        $temp      = explode('_', $key);
                        $ignored[] = end($temp);
                    }
                }
                Configuration::updateValue($name, implode(',', $ignored));
            }
            $value  = Tools::getValue($name, Configuration::get($name));
            $result = $result && Configuration::updateValue($name, trim($value));
        }

        if (! $result) {
            return $this->module->displayError(
                $this->module->l('Could not update configuration!', 'abstractform')
            );
        }

        return $this->module->displayConfirmation(
            $this->module->l('Configuration was successfully updated!', 'abstractform')
        );
    }

    /**
     * @return bool
     */
    protected function validate(): bool
    {
        $isValid = true;

        foreach ($this->getFieldsNormalized() as $name => $field) {
            $value = Tools::getValue($name, Configuration::get($name));

            if (! $field['required'] && empty($value)) {
                continue;
            }

            $isValid = $isValid
                && null !== $value
                && '' !== $value
                && call_user_func([Validate::class, $field['validate']], $value);
        }

        return $isValid;
    }

    private function form(): array
    {
        $form = [
            'form' => [
                'id_form' => strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->name)),
                'legend'  => [
                    'title' => $this->getLegend(),
                    'icon'  => 'icon-' . $this->icon,
                ],
                'input'   => [],
                'submit'  => [
                    'title' => $this->module->l('Save', 'abstractform'),
                ],
                'buttons' => [],
            ],
        ];

        foreach ($this->getFieldsNormalized() as $name => $field) {
            $field['name'] = $name;
            $field['desc'] = isset($field['desc']) ? implode(' ', (array) $field['desc']) : null;

            $form['form']['input'][] = $field;
        }

        if (method_exists($this, 'getButtons')) {
            foreach ($this->getButtons() as $button) {
                $form['form']['buttons'][] = $button;
            }
        }

        return ['form' => $form];
    }

    /**
     * @param  array $field
     *
     * @return array
     */
    private function getFieldDefaults(array $field): array
    {
        $defaults = [
            'required' => false,
            'is_bool'  => false,
        ];

        switch ($field['type']) {
            case 'switch':
                $defaults['values']  = [
                    [
                        'id'    => 'active_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', $this->getNamespace()),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', $this->getNamespace()),
                    ],
                ];
                $defaults['is_bool'] = true;
                break;
            case 'select':
                $defaults['options']       = [
                    'query' => [],
                    'id'    => 'id',
                    'name'  => 'name',
                ];
                $defaults['default_value'] = $field['options']['query'][0]['id'] ?? null;
                break;
        }

        return $defaults;
    }

    /**
     * @return array
     */
    private function getFieldsNormalized(): array
    {
        $fields   = $this->getFields();
        $defaults = array_map(function (array $field) {
            return $this->getFieldDefaults($field);
        }, $fields);

        return array_replace_recursive($defaults, $fields);
    }

    private function process(): string
    {
        if (! Tools::isSubmit($this->name . 'Submit')) {
            return '';
        }

        return $this->update();
    }
}

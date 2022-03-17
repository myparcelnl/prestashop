<?php

namespace Gett\MyparcelBE\Module\Configuration\Form;

use Context;
use Country;
use Gett\MyparcelBE\Constant;

class CustomsForm extends AbstractForm
{
    protected function getLegend(): string
    {
        return $this->module->l('Customs Settings', 'customsform');
    }

    /**
     * @return array[]
     */
    protected function getFields(): array
    {
        return [
            Constant::CUSTOMS_FORM_CONFIGURATION_NAME           => [
                'type'    => self::FIELD_TYPE_SELECT,
                'label'   => $this->module->l('Default customs form', 'customsform'),
                'name'    => Constant::CUSTOMS_FORM_CONFIGURATION_NAME,
                'options' => [
                    'query' => [
                        [
                            'id'   => Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_ADD,
                            'name' => $this->module->l('Add this product to customs form', 'customsform'),
                        ],
                        [
                            'id'   => Constant::CUSTOMS_FORM_CONFIGURATION_OPTION_SKIP,
                            'name' => $this->module->l('Skip this product on customs form', 'customsform'),
                        ],
                    ],
                    'id'    => 'id',
                    'name'  => 'name',
                ],
            ],
            Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME   => [
                'type'  => self::FIELD_TYPE_TEXT,
                'label' => $this->module->l('Default customs code', 'customsform'),
                'name'  => Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME,
            ],
            Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME => [
                'type'          => self::FIELD_TYPE_SELECT,
                'label'         => $this->module->l('Default customs origin', 'customsform'),
                'name'          => Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME,
                'options'       => [
                    'query' => Country::getCountries(Context::getContext()->language->id),
                    'id'    => 'iso_code',
                    'name'  => 'name',
                ],
                'default_value' => $this->module->getModuleCountry(),
            ],
        ];
    }

    protected function getNamespace(): string
    {
        return 'customsform';
    }
}

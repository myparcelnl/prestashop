<?php

namespace Gett\MyparcelBE\Module\Configuration\Form;

use Gett\MyparcelBE\Constant;

class LabelForm extends AbstractForm
{
    protected function getLegend(): string
    {
        return $this->module->l('Label Settings', 'labelform');
    }

    protected function getFields(): array
    {
        $translate = $this->module->l('The maximum length is %d characters. You can add the following variables to the description');
        $labelDescription = sprintf($translate, Constant::ORDER_DESCRIPTION_MAX_LENGTH);

        return [
            Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME => [
                'type' => self::FIELD_TYPE_TEXT,
                'label' => $this->module->l('Label description', 'labelform'),
                'name' => Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME,
                'placeholder' => '{order.id} {order.reference}',
                'desc' => [
                    $this->module->l($labelDescription, 'labelform'),
                    '<ul class="label-description-variables">'
                        . '<li>'
                            . '<code>{order.id}</code>'
                            . sprintf(' - %s', $this->module->l('Order number', 'labelform'))
                        . '</li>'
                        . '<li>'
                            . '<code>{order.reference}</code>'
                            . sprintf(' - %s', $this->module->l('Order reference', 'labelform'))
                        . '</li>'
                    . '</ul>',
                ],
            ],
            Constant::LABEL_SIZE_CONFIGURATION_NAME => [
                'type' => self::FIELD_TYPE_SELECT,
                'label' => $this->module->l('Default label size', 'labelform'),
                'name' => Constant::LABEL_SIZE_CONFIGURATION_NAME,
                'options' => [
                    'query' => [
                        ['id' => 'a4', 'name' => 'A4'],
                        ['id' => 'a6', 'name' => 'A6'],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            Constant::LABEL_POSITION_CONFIGURATION_NAME => [
                'type' => self::FIELD_TYPE_SELECT,
                'label' => $this->module->l('Default label position', 'labelform'),
                'name' => Constant::LABEL_POSITION_CONFIGURATION_NAME,
                'form_group_class' => 'label_position',
                'options' => [
                    'query' => [
                        ['id' => '1', 'name' => $this->module->l('Top left', 'labelform')],
                        ['id' => '2', 'name' => $this->module->l('Top right', 'labelform')],
                        ['id' => '3', 'name' => $this->module->l('Bottom left', 'labelform')],
                        ['id' => '4', 'name' => $this->module->l('Bottom right', 'labelform')],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME => [
                'type' => self::FIELD_TYPE_SELECT,
                'label' => $this->module->l('Open or download label', 'labelform'),
                'name' => Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
                'options' => [
                    'query' => [
                        ['id' => 'true', 'name' => $this->module->l('Open', 'labelform')],
                        ['id' => 'false', 'name' => $this->module->l('Download', 'labelform')],
                    ],
                    'id' => 'id',
                    'name' => 'name',
                ],
            ],
            Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME => [
                'type' => self::FIELD_TYPE_SWITCH,
                'label' => $this->module->l('Prompt for label position', 'labelform'),
                'name' => Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME,
                'required' => false,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->module->l('Yes', 'labelform'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->module->l('No', 'labelform'),
                    ],
                ],
            ],
        ];
    }

    protected function getNamespace(): string
    {
        return 'labelform';
    }
}

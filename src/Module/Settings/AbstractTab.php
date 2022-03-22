<?php

namespace Gett\MyparcelBE\Module\Settings;

use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Module\Carrier\ExclusiveField;
use Gett\MyparcelBE\Module\Tools\Tools;
use MyParcelBE;

abstract class AbstractTab
{
    protected const FIELD_TYPE_CHECKBOX     = 'checkbox';
    protected const FIELD_TYPE_SELECT       = 'select';
    protected const FIELD_TYPE_SELECT_MULTI = 'multi';
    protected const FIELD_TYPE_SUBMIT       = 'submit';
    protected const FIELD_TYPE_SWITCH       = 'switch';
    protected const FIELD_TYPE_TEXT         = 'text';
    protected const FIELD_TYPE_HIDDEN       = 'hidden';

    /**
     * @var \Gett\MyparcelBE\Module\Carrier\ExclusiveField
     */
    protected $exclusiveField;
    /**
     * @var \MyParcelBE
     */
    protected $module;
    /**
     * @var string
     */
    protected $name;

    /**
     * @param \MyParcelBE $module
     */
    public function __construct(MyParcelBE $module)
    {
        $this->module         = $module;
        $this->name           = str_replace(' ', '', $module->displayName) . self::class;
        $this->exclusiveField = new ExclusiveField();
    }

    /**
     * @param string      $name
     * @param string      $label
     * @param bool        $required
     * @param string|null $trueLabel
     * @param string|null $falseLabel
     *
     * @return array[]
     */
    protected function getArrayForSwitch(
        string $name,
        string $label,
        bool   $required = false,
        string $trueLabel = null,
        string $falseLabel = null
    ): array {
        return [
            $name => [
                'type'     => $this->hiddenWhenExclusive(self::FIELD_TYPE_SWITCH, $name),
                'label'    => $this->module->l($label),
                'name'     => $name,
                'options'  => [
                    $this->module->l($falseLabel ?? 'No'),
                    $this->module->l($trueLabel ?? 'Yes'),
                ],
                'required' => $required,
                'value'    => '1' === Configuration::get($name),
            ],
        ];
    }

    protected function getArrayForSelect(
        string $name,
        string $label,
        array  $options,
        bool   $required = false,
        bool   $multi = false
    ): array {
        $fieldType = $multi
            ? self::FIELD_TYPE_SELECT_MULTI
            : self::FIELD_TYPE_SELECT;

        $options = array_map(function ($item) {
            return [
                'label' => isset($item['label'])
                    ? $this->module->l($item['label'])
                    : '',
                'value' => (string) ($item['value'] ?? $item['label'] ?? ''),
            ];
        }, $options);

        return [
            $name => [
                'type'     => $this->hiddenWhenExclusive($fieldType, $name),
                'label'    => $label,
                'name'     => $name,
                'options'  => $options,
                'required' => $required,
                'value'    => Configuration::get($name),
            ],
        ];
    }

    protected function getArrayForSelectMulti(
        string $name,
        string $label,
        array  $options,
        bool   $required = false
    ): array {

        Tools::clearAllCache();

        $array                 = $this->getArrayForSelect($name, $label, $options, $required, true);
        $array[$name]['value'] = explode(',', Configuration::get($name));
        return $array;
    }

    protected function getArrayForText(
        string $name,
        string $label,
        array  $properties = [],
        bool   $required = false
    ): array {
        return [
            $name => [
                'type'        => $this->hiddenWhenExclusive(self::FIELD_TYPE_TEXT, $name),
                'label'       => $this->module->l($label),
                'name'        => $name,
                'description' => implode($properties),
                'required'    => $required,
                'value'       => Configuration::get($name),
            ],
        ];
    }

    /**
     * @param string                $name
     * @param string                $label
     * @param array{string, string} $action
     *
     * @return array[]
     */
    protected function getArrayForButton(string $name, string $label, array $action): array
    {
        return [
            $name => [
                'type'  => $this->hiddenWhenExclusive(self::FIELD_TYPE_SUBMIT, $name),
                'label' => $this->module->l($label),
                'name'  => $name,
                'value' => $action,
            ],
        ];
    }

    /**
     * @param string $fieldType
     * @param string $field
     *
     * @return string
     */
    protected function hiddenWhenExclusive(string $fieldType, string $field): string
    {
        if (! $this->module->isNL() && in_array($field, Constant::EXCLUSIVE_FIELDS_NL)) {
            $fieldType = self::FIELD_TYPE_HIDDEN;
        }

        return $fieldType;
    }

    abstract public function toArray(): array;
}

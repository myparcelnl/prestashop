<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Configuration\Form;

use Gett\MyparcelBE\Constant;

class GeneralForm extends AbstractForm
{
    /**
     * @return array[]
     */
    protected function getFields(): array
    {
        return [
            Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME          => [
                'type' => $this->getExclusiveNlFieldType(
                    self::FIELD_TYPE_SWITCH,
                    Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME
                ),
                'label'    => $this->module->l('Share customer email with MyParcel', $this->getNamespace()),
                'name'     => Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
            ],
            Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME          => [
                'type'     => self::FIELD_TYPE_SWITCH,
                'label'    => $this->module->l('Share customer phone with MyParcel', $this->getNamespace()),
                'name'     => Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME,
            ],
            Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME => [
                'type'     => self::FIELD_TYPE_SWITCH,
                'label'    => $this->module->l('Use second address field in checkout as street number', $this->getNamespace()),
                'name'     => Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME,
            ],
            Constant::CONCEPT_FIRST => [
                'type'     => self::FIELD_TYPE_SWITCH,
                'label'    => $this->module->l('Turn on concept shipment before exporting', $this->getNamespace()),
                'name'     => Constant::CONCEPT_FIRST,
                'desc'     => $this->module->l('Creates the label as concept instead of registered', $this->getNamespace()),
            ],
            Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME => [
                'type'    => self::FIELD_TYPE_SELECT,
                'label'   => $this->module->l('Show prices as', $this->getNamespace()),
                'name'    => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME,
                'options' => [
                    'query' => [
                        [
                            'id'   => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_TOTAL_PRICE,
                            'name' => $this->module->l('Total price', $this->getNamespace()),
                        ],
                        [
                            'id'   => Constant::DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE,
                            'name' => $this->module->l('Surcharge', $this->getNamespace()),
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getLegend(): string
    {
        return $this->module->l('General Settings', $this->getNamespace());
    }

    protected function getNamespace(): string
    {
        return 'generalform';
    }
}

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Configuration\Form;

class CheckoutForm extends AbstractForm
{
    public const  CONFIGURATION_ADDRESS_NOT_FOUND        = 'MYPARCELBE_ADDRESS_NOT_FOUND_TITLE';
    public const  CONFIGURATION_CC                       = 'MYPARCELBE_CC';
    public const  CONFIGURATION_CITY                     = 'MYPARCELBE_CITY_TITLE';
    public const  CONFIGURATION_CLOSED                   = 'MYPARCELBE_CLOSED_TITLE';
    public const  CONFIGURATION_DELIVERY_CHOICE          = 'MYPARCELBE_DELIVERY_CHOICE_TITLE';
    public const  CONFIGURATION_DELIVERY_EVENING_TITLE   = 'MYPARCELBE_DELIVERY_EVENING_TITLE';
    public const  CONFIGURATION_DELIVERY_MORNING_TITLE   = 'MYPARCELBE_DELIVERY_MORNING_TITLE';
    public const  CONFIGURATION_DELIVERY_STANDARD_TITLE  = 'MYPARCELBE_DELIVERY_STANDARD_TITLE';
    public const  CONFIGURATION_DELIVERY_TITLE           = 'MYPARCELBE_DELIVERY_TITLE';
    public const  CONFIGURATION_DISCOUNT                 = 'MYPARCELBE_DISCOUNT_TITLE';
    public const  CONFIGURATION_FREE                     = 'MYPARCELBE_FREE_TITLE';
    public const  CONFIGURATION_FROM                     = 'MYPARCELBE_FROM_TITLE';
    public const  CONFIGURATION_HOUSE_NUMBER             = 'MYPARCELBE_HOUSE_NUMBER_TITLE';
    public const  CONFIGURATION_LOAD_MORE                = 'MYPARCELBE_LOAD_MORE_TITLE';
    public const  CONFIGURATION_ONLY_RECIPIENT_TITLE     = 'MYPARCELBE_ONLY_RECIPIENT_TITLE';
    public const  CONFIGURATION_OPENING_HOURS            = 'MYPARCELBE_OPENING_HOURS_TITLE';
    public const  CONFIGURATION_PICKUP_LIST_TITLE        = 'MYPARCELBE_PICKUP_LIST_TITLE';
    public const  CONFIGURATION_PICKUP_MAP_TITLE         = 'MYPARCELBE_PICKUP_MAP_TITLE';
    public const  CONFIGURATION_PICKUP_TITLE             = 'MYPARCELBE_PICKUP_TITLE';
    public const  CONFIGURATION_PICK_UP_FROM             = 'MYPARCELBE_PICK_UP_FROM_TITLE';
    public const  CONFIGURATION_POSTCODE                 = 'MYPARCELBE_POSTCODE_TITLE';
    public const  CONFIGURATION_RETRY                    = 'MYPARCELBE_RETRY_TITLE';
    public const  CONFIGURATION_SATURDAY_DELIVERY_TITLE  = 'MYPARCELBE_SATURDAY_DELIVERY_TITLE';
    public const  CONFIGURATION_SIGNATURE_TITLE          = 'MYPARCELBE_SIGNATURE_TITLE';
    public const  CONFIGURATION_WRONG_NUMBER_POSTAL_CODE = 'MYPARCELBE_WRONG_NUMBER_POSTAL_CODE';
    public const  CONFIGURATION_WRONG_POSTAL_CODE_CITY   = 'MYPARCELBE_WRONG_POSTAL_CODE_CITY_TITLE';


    /**
     * Default data to merge with each config item.
     */
    private const DEFAULTS                   = [
        'tab'  => 'form',
        'type' => 'text',
    ];
    private const DELIVERY_TITLE_DESCRIPTION = 'When there is no title, the delivery time will automatically be visible.';
    private const TRANSLATION_KEY            = 'myparcelbe_checkout';

    /**
     * @return array[]
     */
    public function getConfig(): array
    {
        return [
            [
                'label' => 'Delivery Title',
                'name'  => self::CONFIGURATION_DELIVERY_TITLE,
                'desc'  => 'Title of the delivery option.',
            ],
            [
                'label' => 'Standard delivery title',
                'name'  => self::CONFIGURATION_DELIVERY_STANDARD_TITLE,
                'desc'  => self::DELIVERY_TITLE_DESCRIPTION,
            ],
            [
                'label' => 'Morning delivery title',
                'name'  => self::CONFIGURATION_DELIVERY_MORNING_TITLE,
                'desc'  => self::DELIVERY_TITLE_DESCRIPTION,
            ],
            [
                'label' => 'Evening delivery title',
                'name'  => self::CONFIGURATION_DELIVERY_EVENING_TITLE,
                'desc'  => self::DELIVERY_TITLE_DESCRIPTION,
            ],
            [
                'label' => 'Saturday delivery title',
                'name'  => self::CONFIGURATION_SATURDAY_DELIVERY_TITLE,
            ],
            [
                'label' => 'Signature title',
                'name'  => self::CONFIGURATION_SIGNATURE_TITLE,
            ],
            [
                'label' => 'Only recipient title',
                'name'  => self::CONFIGURATION_ONLY_RECIPIENT_TITLE,
            ],
            [
                'label' => 'Pickup title',
                'name'  => self::CONFIGURATION_PICKUP_TITLE,
                'desc'  => 'Title of the pickup option.',
            ],
            [
                'label' => 'House number text',
                'name'  => self::CONFIGURATION_HOUSE_NUMBER,
            ],
            [
                'label' => 'City text',
                'name'  => self::CONFIGURATION_CITY,
            ],
            [
                'label' => 'Postal code text',
                'name'  => self::CONFIGURATION_POSTCODE,
            ],
            [
                'label' => 'Country text',
                'name'  => self::CONFIGURATION_CC,
            ],
            [
                'label' => 'Opening hours text',
                'name'  => self::CONFIGURATION_OPENING_HOURS,
            ],
            [
                'label' => 'Load more title',
                'name'  => self::CONFIGURATION_LOAD_MORE,
            ],
            [
                'label' => 'Pickup map button title',
                'name'  => self::CONFIGURATION_PICKUP_MAP_TITLE,
            ],
            [
                'label' => 'Pickup list button text',
                'name'  => self::CONFIGURATION_PICKUP_LIST_TITLE,
            ],
            [
                'label' => 'Retry title',
                'name'  => self::CONFIGURATION_RETRY,
            ],
            [
                'label' => 'Address not found title',
                'name'  => self::CONFIGURATION_ADDRESS_NOT_FOUND,
            ],
            [
                'label' => 'Wrong postal code/city combination title',
                'name'  => self::CONFIGURATION_WRONG_POSTAL_CODE_CITY,
            ],
            [
                'label' => 'Wrong number/postal code title',
                'name'  => self::CONFIGURATION_WRONG_NUMBER_POSTAL_CODE,
            ],
            [
                'label' => 'From title',
                'name'  => self::CONFIGURATION_FROM,
            ],
            [
                'label' => 'Free title',
                'name'  => self::CONFIGURATION_FREE,
            ],
            [
                'label' => 'Discount title',
                'name'  => self::CONFIGURATION_DISCOUNT,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getFields(): array
    {
        $fields = [];

        foreach ($this->getConfig() as $item) {
            $item['label'] = $this->module->l($item['label'], self::TRANSLATION_KEY);
            $item['desc']  = isset($item['desc']) ? $this->module->l($item['desc'], 'carriers') : null;

            $fields[$item['name']] = array_merge(self::DEFAULTS, $item);
        }

        return $fields;
    }

    /**
     * @return string
     */
    protected function getLegend(): string
    {
        return $this->module->l('Checkout options', self::TRANSLATION_KEY);
    }

    protected function getNamespace(): string
    {
        return 'checkoutform';
    }
}

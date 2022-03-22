<?php

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Gett\MyparcelBE\Constant;

class ModuleSettings
{
    public const FIELD_TYPE_CHECKBOX     = 'checkbox';
    public const FIELD_TYPE_SELECT       = 'select';
    public const FIELD_TYPE_SELECT_MULTI = 'multi';
    public const FIELD_TYPE_SUBMIT       = 'submit';
    public const FIELD_TYPE_SWITCH       = 'switch';
    public const FIELD_TYPE_TEXT         = 'text';

    public const BUTTON_CLEAR_CACHE = 'clearCache';
    public const BUTTON_RESET_HOOK  = 'resetHook';
    public const BUTTON_DELETE_HOOK = 'deleteHook';

    public const ALLOW_EVENING_DELIVERY  = 'allowEveningDelivery';
    public const ALLOW_MONDAY_DELIVERY   = 'allowMondayDelivery';
    public const ALLOW_MORNING_DELIVERY  = 'allowMorningDelivery';
    public const ALLOW_ONLY_RECIPIENT    = 'allowOnlyRecipient';
    public const ALLOW_PICKUP_POINTS     = 'allowPickupPoints';
    public const ALLOW_SATURDAY_DELIVERY = 'allowSaturdayDelivery';
    public const ALLOW_SIGNATURE         = 'allowSignature';
    public const DELIVERY_DAYS_WINDOW    = 'deliveryDaysWindow';
    public const DROP_OFF_DAYS           = 'dropOffDays';
    public const DROP_OFF_DELAY          = 'dropOffDelay';

    public const FRIDAY_CUTOFF_TIME      = 'fridayCutoffTime';
    public const MONDAY_CUTOFF_TIME      = 'mondayCutoffTime';
    public const PRICE_EVENING_DELIVERY  = 'priceEveningDelivery';
    public const PRICE_MONDAY_DELIVERY   = 'priceMondayDelivery';
    public const PRICE_MORNING_DELIVERY  = 'priceMorningDelivery';
    public const PRICE_ONLY_RECIPIENT    = 'priceOnlyRecipient';
    public const PRICE_PICKUP            = 'pricePickup';
    public const PRICE_SATURDAY_DELIVERY = 'priceSaturdayDelivery';
    public const PRICE_SIGNATURE         = 'priceSignature';
    public const SATURDAY_CUTOFF_TIME    = 'saturdayCutoffTime';
    public const SUNDAY_CUTOFF_TIME      = 'sundayCutoffTime';
    public const THURSDAY_CUTOFF_TIME    = 'thursdayCutoffTime';
    public const TUESDAY_CUTOFF_TIME     = 'tuesdayCutoffTime';
    public const WEDNESDAY_CUTOFF_TIME   = 'wednesdayCutoffTime';

    public const SEND_NOTIFICATION_AFTER_FIRST_SCAN = 'first_scan';
    public const SEND_NOTIFICATION_AFTER_PRINTED    = 'printed';

    public const CONFIGURATION_ADDRESS_NOT_FOUND       = 'MYPARCELBE_ADDRESS_NOT_FOUND_TITLE';
    public const CONFIGURATION_CITY                    = 'MYPARCELBE_CITY_TITLE';
    public const CONFIGURATION_CLOSED                  = 'MYPARCELBE_CLOSED_TITLE';
    public const CONFIGURATION_DELIVERY_EVENING_TITLE  = 'MYPARCELBE_DELIVERY_EVENING_TITLE';
    public const CONFIGURATION_DELIVERY_MORNING_TITLE  = 'MYPARCELBE_DELIVERY_MORNING_TITLE';
    public const CONFIGURATION_DELIVERY_STANDARD_TITLE = 'MYPARCELBE_DELIVERY_STANDARD_TITLE';
    public const CONFIGURATION_DELIVERY_TITLE          = 'MYPARCELBE_DELIVERY_TITLE';
    public const CONFIGURATION_DISCOUNT                = 'MYPARCELBE_DISCOUNT_TITLE';
    public const CONFIGURATION_FREE                    = 'MYPARCELBE_FREE_TITLE';
    public const CONFIGURATION_FROM                    = 'MYPARCELBE_FROM_TITLE';
    public const CONFIGURATION_HOUSE_NUMBER            = 'MYPARCELBE_HOUSE_NUMBER_TITLE';
    public const CONFIGURATION_LOAD_MORE               = 'MYPARCELBE_LOAD_MORE_TITLE';
    public const CONFIGURATION_ONLY_RECIPIENT_TITLE    = 'MYPARCELBE_ONLY_RECIPIENT_TITLE';
    public const CONFIGURATION_OPENING_HOURS           = 'MYPARCELBE_OPENING_HOURS_TITLE';
    public const CONFIGURATION_PICKUP_TITLE            = 'MYPARCELBE_PICKUP_TITLE';
    public const CONFIGURATION_PICK_UP_FROM            = 'MYPARCELBE_PICK_UP_FROM_TITLE';
    public const CONFIGURATION_POSTCODE                = 'MYPARCELBE_POSTCODE_TITLE';
    public const CONFIGURATION_RETRY                   = 'MYPARCELBE_RETRY_TITLE';
    public const CONFIGURATION_SATURDAY_DELIVERY_TITLE = 'MYPARCELBE_SATURDAY_DELIVERY_TITLE';
    public const CONFIGURATION_SIGNATURE_TITLE         = 'MYPARCELBE_SIGNATURE_TITLE';
    public const CONFIGURATION_WRONG_POSTAL_CODE_CITY  = 'MYPARCELBE_WRONG_POSTAL_CODE_CITY_TITLE';

    public const DELIVERY_TITLE_DESCRIPTION = 'When there is no title, the delivery time will automatically be visible.';

    public const CONFIGURATION_FIELDS = [
        Constant::API_KEY_CONFIGURATION_NAME                             => null,
        Constant::API_LOGGING_CONFIGURATION_NAME                         => self::FIELD_TYPE_SWITCH,
        self::BUTTON_CLEAR_CACHE                                         => null,
        self::BUTTON_RESET_HOOK                                          => null,
        self::BUTTON_DELETE_HOOK                                         => null,
        Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME                => self::FIELD_TYPE_SWITCH,
        Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME                => self::FIELD_TYPE_SWITCH,
        Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME       => self::FIELD_TYPE_SWITCH,
        Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME       => null,
        Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME                   => null,
        Constant::LABEL_SIZE_CONFIGURATION_NAME                          => null,
        Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME                 => null,
        Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME               => self::FIELD_TYPE_SWITCH,
        Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME          => null,
        Constant::LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME          => null,
        Constant::DELIVERED_ORDER_STATUS_CONFIGURATION_NAME              => null,
        Constant::IGNORE_ORDER_STATUS_CONFIGURATION_NAME                 => self::FIELD_TYPE_SELECT_MULTI,
        Constant::STATUS_CHANGE_MAIL_CONFIGURATION_NAME                  => self::FIELD_TYPE_SWITCH,
        Constant::ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME            => null,
        Constant::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME => self::FIELD_TYPE_SWITCH,
        Constant::CUSTOMS_FORM_CONFIGURATION_NAME                        => null,
        Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME                => null,
        Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME              => null,
        self::CONFIGURATION_DELIVERY_TITLE                               => null,
        self::CONFIGURATION_DELIVERY_STANDARD_TITLE                      => null,
        self::CONFIGURATION_DELIVERY_MORNING_TITLE                       => null,
        self::CONFIGURATION_DELIVERY_EVENING_TITLE                       => null,
        self::CONFIGURATION_SATURDAY_DELIVERY_TITLE                      => null,
        self::CONFIGURATION_SIGNATURE_TITLE                              => null,
        self::CONFIGURATION_ONLY_RECIPIENT_TITLE                         => null,
        self::CONFIGURATION_PICKUP_TITLE                                 => null,
    ];

    public const CARRIER_FIELD_SEPARATOR = '|';

    public const CARRIER_CONFIGURATION_FIELDS = [
        Constant::CARRIER_CONFIGURATION_FIELD_CARRIER_TYPE          => null,
        self::DROP_OFF_DAYS                                         => self::FIELD_TYPE_SELECT_MULTI,
        Constant::CUTOFF_EXCEPTIONS                                 => null,
        self::MONDAY_CUTOFF_TIME                                    => null,
        self::TUESDAY_CUTOFF_TIME                                   => null,
        self::WEDNESDAY_CUTOFF_TIME                                 => null,
        self::THURSDAY_CUTOFF_TIME                                  => null,
        self::FRIDAY_CUTOFF_TIME                                    => null,
        self::SATURDAY_CUTOFF_TIME                                  => null,
        self::SUNDAY_CUTOFF_TIME                                    => null,
        self::DELIVERY_DAYS_WINDOW                                  => null,
        self::DROP_OFF_DELAY                                        => null,
        self::ALLOW_MONDAY_DELIVERY                                 => self::FIELD_TYPE_SWITCH,
        self::PRICE_MONDAY_DELIVERY                                 => null,
        self::ALLOW_MORNING_DELIVERY                                => self::FIELD_TYPE_SWITCH,
        self::PRICE_MORNING_DELIVERY                                => null,
        self::ALLOW_EVENING_DELIVERY                                => self::FIELD_TYPE_SWITCH,
        self::PRICE_EVENING_DELIVERY                                => null,
        self::ALLOW_SATURDAY_DELIVERY                               => self::FIELD_TYPE_SWITCH,
        self::PRICE_SATURDAY_DELIVERY                               => null,
        self::ALLOW_SIGNATURE                                       => self::FIELD_TYPE_SWITCH,
        self::PRICE_SIGNATURE                                       => null,
        self::ALLOW_ONLY_RECIPIENT                                  => self::FIELD_TYPE_SWITCH,
        self::PRICE_ONLY_RECIPIENT                                  => null,
        self::ALLOW_PICKUP_POINTS                                   => self::FIELD_TYPE_SWITCH,
        self::PRICE_PICKUP                                          => null,
        Constant::PACKAGE_TYPE_CONFIGURATION_NAME                   => null,
        Constant::PACKAGE_FORMAT_CONFIGURATION_NAME                 => null,
        Constant::ONLY_RECIPIENT_CONFIGURATION_NAME                 => self::FIELD_TYPE_SWITCH,
        Constant::AGE_CHECK_CONFIGURATION_NAME                      => self::FIELD_TYPE_SWITCH,
        Constant::RETURN_PACKAGE_CONFIGURATION_NAME                 => self::FIELD_TYPE_SWITCH,
        Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME             => self::FIELD_TYPE_SWITCH,
        Constant::INSURANCE_CONFIGURATION_NAME                      => self::FIELD_TYPE_SWITCH,
        Constant::INSURANCE_CONFIGURATION_FROM_PRICE                => null,
        Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT                => null,
        'return_' . Constant::PACKAGE_TYPE_CONFIGURATION_NAME       => null,
        'return_' . Constant::PACKAGE_FORMAT_CONFIGURATION_NAME     => null,
        'return_' . Constant::ONLY_RECIPIENT_CONFIGURATION_NAME     => self::FIELD_TYPE_SWITCH,
        'return_' . Constant::AGE_CHECK_CONFIGURATION_NAME          => self::FIELD_TYPE_SWITCH,
        'return_' . Constant::RETURN_PACKAGE_CONFIGURATION_NAME     => self::FIELD_TYPE_SWITCH,
        'return_' . Constant::SIGNATURE_REQUIRED_CONFIGURATION_NAME => self::FIELD_TYPE_SWITCH,
        'return_' . Constant::INSURANCE_CONFIGURATION_NAME          => self::FIELD_TYPE_SWITCH,
        'return_' . Constant::INSURANCE_CONFIGURATION_FROM_PRICE    => null,
        'return_' . Constant::INSURANCE_CONFIGURATION_MAX_AMOUNT    => null,
        'return_label_description'                                  => null,
    ];
}

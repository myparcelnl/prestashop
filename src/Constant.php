<?php

namespace Gett\MyparcelBE;

use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

class Constant
{
    /**
     * Maximum characters length of item description.
     */
    public const ITEM_DESCRIPTION_MAX_LENGTH  = 50;
    public const ORDER_DESCRIPTION_MAX_LENGTH = 45;

    public const API_KEY_CONFIGURATION_NAME                = 'MYPARCELBE_API_KEY';
    public const API_LOGGING_CONFIGURATION_NAME            = 'MYPARCELBE_API_LOGGING';
    public const PACKAGE_TYPE_CONFIGURATION_NAME           = 'MYPARCELBE_PACKAGE_TYPE';
    public const ONLY_RECIPIENT_CONFIGURATION_NAME         = 'MYPARCELBE_RECIPIENT_ONLY';
    public const AGE_CHECK_CONFIGURATION_NAME              = 'MYPARCELBE_AGE_CHECK';
    public const PACKAGE_FORMAT_CONFIGURATION_NAME         = 'MYPARCELBE_PACKAGE_FORMAT';
    public const RETURN_PACKAGE_CONFIGURATION_NAME         = 'MYPARCELBE_RETURN_PACKAGE';
    public const SIGNATURE_REQUIRED_CONFIGURATION_NAME     = 'MYPARCELBE_SIGNATURE_REQUIRED';
    public const INSURANCE_CONFIGURATION_NAME              = 'MYPARCELBE_INSURANCE';
    public const INSURANCE_CONFIGURATION_FROM_PRICE        = 'MYPARCELBE_INSURANCE_FROM_PRICE';
    public const INSURANCE_CONFIGURATION_MAX_AMOUNT        = 'MYPARCELBE_INSURANCE_MAX_AMOUNT';
    public const INSURANCE_CONFIGURATION_BELGIUM           = 'MYPARCELBE_INSURANCE_BELGIUM';
    public const CUSTOMS_FORM_CONFIGURATION_NAME           = 'MYPARCELBE_CUSTOMS_FORM';
    public const CUSTOMS_FORM_CONFIGURATION_OPTION_ADD     = 'Add';
    public const CUSTOMS_FORM_CONFIGURATION_OPTION_SKIP    = 'Skip';
    public const CUSTOMS_CODE_CONFIGURATION_NAME           = 'MYPARCELBE_CUSTOMS_CODE';
    public const DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME   = 'MYPARCELBE_DEFAULT_CUSTOMS_CODE';
    public const CUSTOMS_ORIGIN_CONFIGURATION_NAME         = 'MYPARCELBE_CUSTOMS_ORIGIN';
    public const DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME = 'MYPARCELBE_DEFAULT_CUSTOMS_ORIGIN';
    public const INSURANCE_CONFIGURATION_NONE              = 0;
    public const DEFAULT_INSURANCE_POSSIBILITIES           = [self::INSURANCE_CONFIGURATION_NONE];
    public const INSURANCE_CONFIGURATION_BELGIUM_AMOUNT    = 500;

    public const SINGLE_LABEL_CREATION_OPTIONS                          = [
        'packageType'       => self::PACKAGE_TYPE_CONFIGURATION_NAME,
        'packageFormat'     => self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        'onlyRecipient'     => self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'ageCheck'          => self::AGE_CHECK_CONFIGURATION_NAME,
        'returnUndelivered' => self::RETURN_PACKAGE_CONFIGURATION_NAME,
        'signatureRequired' => self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        'insurance'         => self::INSURANCE_CONFIGURATION_NAME,
    ];
    public const SINGLE_LABEL_RETURN_OPTIONS                            = [
        'packageType'       => 'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME,
        'packageFormat'     => 'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        'onlyRecipient'     => 'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'ageCheck'          => 'return_' . self::AGE_CHECK_CONFIGURATION_NAME,
        'returnUndelivered' => 'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME,
        'signatureRequired' => 'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        'insurance'         => 'return_' . self::INSURANCE_CONFIGURATION_NAME,
        'labelDescription'  => 'return_label_description',
    ];
    public const CUTOFF_EXCEPTIONS                                      = 'cutoff_exceptions';

    public const PACKAGE_TYPE_PACKAGE                                   = 1;
    public const PACKAGE_TYPE_MAILBOX                                   = 2;
    public const PACKAGE_TYPE_LETTER                                    = 3;
    public const PACKAGE_TYPE_DIGITAL_STAMP                             = 4;
    public const PACKAGE_TYPES                                          = [
        self::PACKAGE_TYPE_PACKAGE       => 'package',
        self::PACKAGE_TYPE_MAILBOX       => 'mailbox package',
        self::PACKAGE_TYPE_LETTER        => 'letter',
        self::PACKAGE_TYPE_DIGITAL_STAMP => 'digital stamp',
    ];
    public const PACKAGE_TYPES_LEGACY_NAMES_IDS_MAP = [
        'mailbox package'                                    => AbstractConsignment::PACKAGE_TYPE_MAILBOX,
        'digital stamp'                                      => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP,
        AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME       => AbstractConsignment::PACKAGE_TYPE_PACKAGE,
        AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME       => AbstractConsignment::PACKAGE_TYPE_MAILBOX,
        AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP_NAME => AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP,
        AbstractConsignment::PACKAGE_TYPE_LETTER_NAME        => AbstractConsignment::PACKAGE_TYPE_LETTER,
    ];

    public const PACKAGE_TYPE_WEIGHT_LIMIT                              = 2; // Kg

    public const PACKAGE_FORMAT_NORMAL    = 1;
    public const PACKAGE_FORMAT_LARGE     = 2;
    public const PACKAGE_FORMAT_AUTOMATIC = 3;

    public const PACKAGE_FORMAT_LARGE_INDEX                             = 2;
    public const SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME                = 'MYPARCELBE_SHARE_CUSTOMER_EMAIL';
    public const SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME                = 'MYPARCELBE_SHARE_CUSTOMER_PHONE';
    public const USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME       = 'MYPARCELBE_USE_ADDRESS2_AS_STREET_NUMBER';
    public const CONCEPT_FIRST                                          = 'MYPARCELBE_CONCEPT_FIRST';

    public const DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME = 'MYPARCELBE_DELIVERY_OPTIONS_PRICE_FORMAT';
    public const DELIVERY_OPTIONS_PRICE_FORMAT_TOTAL_PRICE        = 'total_price';
    public const DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE          = 'surcharge';

    public const LABEL_DESCRIPTION_CONFIGURATION_NAME          = 'MYPARCELBE_LABEL_DESCRIPTION';
    public const LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME        = 'MYPARCELBE_LABEL_OPEN_DOWNLOAD';
    public const LABEL_SIZE_CONFIGURATION_NAME                 = 'MYPARCELBE_LABEL_SIZE';
    public const LABEL_POSITION_CONFIGURATION_NAME             = 'MYPARCELBE_LABEL_POSITION';
    public const LABEL_PROMPT_POSITION_CONFIGURATION_NAME      = 'MYPARCELBE_LABEL_PROMPT_POSITION';
    public const LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME = 'MYPARCELBE_LABEL_CREATED_ORDER_STATUS';

    public const CONSIGNMENT_INIT_PARAMS_FOR_CHECKING_ONLY = ['extraOptions' => ['labelAmount' => 100]];

    // Field used in <platform>_carrier_configuration to link myparcel carrier to prestashop carrier.
    public const CARRIER_CONFIGURATION_FIELD_CARRIER_TYPE = 'carrierType';

    public const CARRIER_CONFIGURATION_FIELDS                           = [
        self::CARRIER_CONFIGURATION_FIELD_CARRIER_TYPE,
        'dropOffDays',
        self::CUTOFF_EXCEPTIONS,
        'mondayCutoffTime',
        'tuesdayCutoffTime',
        'wednesdayCutoffTime',
        'thursdayCutoffTime',
        'fridayCutoffTime',
        'saturdayCutoffTime',
        'sundayCutoffTime',
        'deliveryDaysWindow',
        'dropOffDelay',
        'allowMondayDelivery',
        'priceMondayDelivery',
        'saturdayCutoffTime',
        'allowMorningDelivery',
        'priceMorningDelivery',
        'allowEveningDelivery',
        'priceEveningDelivery',
        'allowSaturdayDelivery',
        'priceSaturdayDelivery',
        'allowSignature',
        'priceSignature',
        'allowOnlyRecipient',
        'priceOnlyRecipient',
        'allowPickupPoints',
        'pricePickup',
        self::PACKAGE_TYPE_CONFIGURATION_NAME,
        self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        self::AGE_CHECK_CONFIGURATION_NAME,
        self::RETURN_PACKAGE_CONFIGURATION_NAME,
        self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        self::INSURANCE_CONFIGURATION_NAME,
        self::INSURANCE_CONFIGURATION_FROM_PRICE,
        self::INSURANCE_CONFIGURATION_MAX_AMOUNT,
        self::INSURANCE_CONFIGURATION_BELGIUM,
        self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME,
        'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME,
        'return_' . self::AGE_CHECK_CONFIGURATION_NAME,
        'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME,
        'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME,
        'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME,
        'return_' . self::INSURANCE_CONFIGURATION_NAME,
        'return_' . self::INSURANCE_CONFIGURATION_FROM_PRICE,
        'return_' . self::INSURANCE_CONFIGURATION_MAX_AMOUNT,
        'return_label_description',
    ];
    public const WEEK_DAYS                                              = [
        1 => 'monday',
        2 => 'tuesday',
        3 => 'wednesday',
        4 => 'thursday',
        5 => 'friday',
        6 => 'saturday',
        7 => 'sunday',
    ];

    public const DEFAULT_CUTOFF_TIME                                    = '17:00';

    public const STATUS_CHANGE_MAIL_CONFIGURATION_NAME                  = 'MYPARCELBE_STATUS_CHANGE_MAIL';
    public const SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME = 'MYPARCELBE_SENT_ORDER_STATE_FOR_DIGITAL_STAMPS';
    public const LABEL_SCANNED_ORDER_STATUS_CONFIGURATION_NAME          = 'MYPARCELBE_LABEL_SCANNED_ORDER_STATUS';
    public const DELIVERED_ORDER_STATUS_CONFIGURATION_NAME              = 'MYPARCELBE_DELIVERED_ORDER_STATUS';
    public const ORDER_NOTIFICATION_AFTER_CONFIGURATION_NAME            = 'MYPARCELBE_ORDER_NOTIFICATION_AFTER';
    public const IGNORE_ORDER_STATUS_CONFIGURATION_NAME                 = 'MYPARCELBE_IGNORE_ORDER_STATUS';
    public const WEBHOOK_ID_CONFIGURATION_NAME                          = 'MYPARCELBE_WEBHOOK_ID';
    public const WEBHOOK_HASH_CONFIGURATION_NAME                        = 'MYPARCELBE_WEBHOOK_HASH';
    public const POSTNL_CONFIGURATION_NAME                              = 'MYPARCELBE_POSTNL';
    public const BPOST_CONFIGURATION_NAME                               = 'MYPARCELBE_BPOST';
    public const DPD_CONFIGURATION_NAME                                 = 'MYPARCELBE_DPD';

    public const CARRIER_CONFIGURATION_MAP = [
        CarrierPostNL::class => Constant::POSTNL_CONFIGURATION_NAME,
        CarrierBpost::class  => Constant::BPOST_CONFIGURATION_NAME,
        CarrierDPD::class    => Constant::DPD_CONFIGURATION_NAME,
    ];

    /** @deprecated use CarrierPostNL::NAME */
    public const POSTNL_CARRIER_NAME = CarrierPostNL::NAME;
    /** @deprecated use CarrierBpost::NAME */
    public const BPOST_CARRIER_NAME = CarrierBpost::NAME;
    /** @deprecated use CarrierDPD::NAME */
    public const DPD_CARRIER_NAME = CarrierDPD::NAME;

    public const EXCLUSIVE_FIELDS_NL                                    = [
        self::SENT_ORDER_STATE_FOR_DIGITAL_STAMPS_CONFIGURATION_NAME,
        self::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME,
    ];

    public const CARRIER_EXCLUSIVE              = [
        'POSTNL' => [
            'ALLOW_STANDARD_FORM'                                   => ['BE' => true, 'NL' => true],
            'dropOffDays'                                           => ['BE' => true, 'NL' => true],
            'cutoffTime'                                            => ['BE' => true, 'NL' => true],
            'deliveryDaysWindow'                                    => ['BE' => true, 'NL' => true],
            'dropOffDelay'                                          => ['BE' => true, 'NL' => true],
            'allowMondayDelivery'                                   => ['BE' => false, 'NL' => true],
            'allowMorningDelivery'                                  => ['BE' => false, 'NL' => true],
            'allowEveningDelivery'                                  => ['BE' => false, 'NL' => true],
            'allowSaturdayDelivery'                                 => ['BE' => false, 'NL' => false],
            'priceSaturdayDelivery'                                 => ['BE' => false, 'NL' => false],
            'allowSignature'                                        => ['BE' => true, 'NL' => true],
            'priceSignature'                                        => ['BE' => true, 'NL' => true],
            'allowOnlyRecipient'                                    => ['BE' => true, 'NL' => true],
            'priceOnlyRecipient'                                    => ['BE' => true, 'NL' => true],
            'allowPickupPoints'                                     => ['BE' => true, 'NL' => true],
            // Delivery form
            'ALLOW_DELIVERY_FORM'                                   => ['BE' => true, 'NL' => true],
            self::PACKAGE_TYPE_CONFIGURATION_NAME                   => [
                'BE' => [1 => true],
                'NL' => [1 => true, 2 => true, 3 => true, 4 => true],
            ],
            self::ONLY_RECIPIENT_CONFIGURATION_NAME                 => ['BE' => true, 'NL' => true],
            self::PACKAGE_FORMAT_CONFIGURATION_NAME => [
                'BE' => [
                    self::PACKAGE_FORMAT_NORMAL    => true,
                    self::PACKAGE_FORMAT_LARGE     => true,
                    self::PACKAGE_FORMAT_AUTOMATIC => true,
                ],
                'NL' => [
                    self::PACKAGE_FORMAT_NORMAL    => true,
                    self::PACKAGE_FORMAT_LARGE     => true,
                    self::PACKAGE_FORMAT_AUTOMATIC => true,
                ],
            ],
            self::SIGNATURE_REQUIRED_CONFIGURATION_NAME             => ['BE' => true, 'NL' => true],
            self::INSURANCE_CONFIGURATION_NAME                      => ['BE' => true, 'NL' => true],
            self::AGE_CHECK_CONFIGURATION_NAME                      => ['BE' => false, 'NL' => true],
            self::RETURN_PACKAGE_CONFIGURATION_NAME                 => ['BE' => false, 'NL' => true],
            // Return form
            'ALLOW_RETURN_FORM'                                     => ['BE' => false, 'NL' => true],
            'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME       => [
                'BE' => false,
                'NL' => [1 => true, 2 => true, 3 => true, 4 => true],
            ],
            'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME     => ['BE' => false, 'NL' => true],
            'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME     => ['BE' => false, 'NL' => [1 => true, 2 => true]],
            'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME => ['BE' => false, 'NL' => true],
            'return_' . self::INSURANCE_CONFIGURATION_NAME          => ['BE' => false, 'NL' => true],
            'return_' . self::AGE_CHECK_CONFIGURATION_NAME          => ['BE' => false, 'NL' => true],
            'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME     => ['BE' => false, 'NL' => true],
        ],
        'BPOST'  => [
            'ALLOW_STANDARD_FORM'                                   => ['BE' => true, 'NL' => true],
            'dropOffDays'                                           => ['BE' => true, 'NL' => false],
            'cutoffTime'                                            => ['BE' => true, 'NL' => false],
            'deliveryDaysWindow'                                    => ['BE' => true, 'NL' => false],
            'dropOffDelay'                                          => ['BE' => true, 'NL' => false],
            'allowMondayDelivery'                                   => ['BE' => false, 'NL' => false],
            'allowMorningDelivery'                                  => ['BE' => false, 'NL' => false],
            'allowEveningDelivery'                                  => ['BE' => false, 'NL' => false],
            'allowSaturdayDelivery'                                 => ['BE' => true, 'NL' => false],
            'priceSaturdayDelivery'                                 => ['BE' => true, 'NL' => false],
            'allowSignature'                                        => ['BE' => true, 'NL' => false],
            'priceSignature'                                        => ['BE' => true, 'NL' => false],
            'allowOnlyRecipient'                                    => ['BE' => false, 'NL' => false],
            'priceOnlyRecipient'                                    => ['BE' => false, 'NL' => false],
            'allowPickupPoints'                                     => ['BE' => true, 'NL' => false],
            // Delivery form
            'ALLOW_DELIVERY_FORM'                                   => ['BE' => true, 'NL' => true],
            self::PACKAGE_TYPE_CONFIGURATION_NAME                   => [
                'BE' => [1 => true],
                'NL' => false,
            ],
            self::ONLY_RECIPIENT_CONFIGURATION_NAME                 => ['BE' => false, 'NL' => false],
            self::PACKAGE_FORMAT_CONFIGURATION_NAME                 => ['BE' => [1 => true], 'NL' => false],
            self::SIGNATURE_REQUIRED_CONFIGURATION_NAME             => ['BE' => true, 'NL' => false],
            self::INSURANCE_CONFIGURATION_NAME                      => ['BE' => true, 'NL' => false],
            self::AGE_CHECK_CONFIGURATION_NAME                      => ['BE' => false, 'NL' => false],
            self::RETURN_PACKAGE_CONFIGURATION_NAME                 => ['BE' => false, 'NL' => false],
            // Return form
            'ALLOW_RETURN_FORM'                                     => ['BE' => true, 'NL' => false],
            'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME       => [
                'BE' => [1 => true],
                'NL' => false,
            ],
            'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME     => ['BE' => false, 'NL' => false],
            'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME     => ['BE' => [1 => true], 'NL' => false],
            'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME => ['BE' => true, 'NL' => false],
            'return_' . self::INSURANCE_CONFIGURATION_NAME          => ['BE' => true, 'NL' => false],
            'return_' . self::AGE_CHECK_CONFIGURATION_NAME          => ['BE' => false, 'NL' => false],
            'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME     => ['BE' => false, 'NL' => false],
        ],
        'DPD'    => [
            'ALLOW_STANDARD_FORM'                                   => ['BE' => true, 'NL' => true],
            'dropOffDays'                                           => ['BE' => true, 'NL' => false],
            'cutoffTime'                                            => ['BE' => true, 'NL' => false],
            'deliveryDaysWindow'                                    => ['BE' => true, 'NL' => false],
            'dropOffDelay'                                          => ['BE' => true, 'NL' => false],
            'allowMondayDelivery'                                   => ['BE' => false, 'NL' => false],
            'allowMorningDelivery'                                  => ['BE' => false, 'NL' => false],
            'allowEveningDelivery'                                  => ['BE' => false, 'NL' => false],
            'allowSaturdayDelivery'                                 => ['BE' => false, 'NL' => false],
            'priceSaturdayDelivery'                                 => ['BE' => false, 'NL' => false],
            'allowSignature'                                        => ['BE' => false, 'NL' => false],
            'priceSignature'                                        => ['BE' => false, 'NL' => false],
            'allowOnlyRecipient'                                    => ['BE' => false, 'NL' => false],
            'priceOnlyRecipient'                                    => ['BE' => false, 'NL' => false],
            'allowPickupPoints'                                     => ['BE' => true, 'NL' => false],
            // Delivery form
            'ALLOW_DELIVERY_FORM'                                   => ['BE' => true, 'NL' => true],
            self::PACKAGE_TYPE_CONFIGURATION_NAME                   => [
                'BE' => [1 => true],
                'NL' => false,
            ],
            self::ONLY_RECIPIENT_CONFIGURATION_NAME                 => ['BE' => false, 'NL' => false],
            self::PACKAGE_FORMAT_CONFIGURATION_NAME                 => ['BE' => [1 => true], 'NL' => false],
            self::SIGNATURE_REQUIRED_CONFIGURATION_NAME             => ['BE' => false, 'NL' => false],
            self::INSURANCE_CONFIGURATION_NAME                      => ['BE' => false, 'NL' => false],
            self::AGE_CHECK_CONFIGURATION_NAME                      => ['BE' => false, 'NL' => false],
            self::RETURN_PACKAGE_CONFIGURATION_NAME                 => ['BE' => false, 'NL' => false],
            // Return form
            'ALLOW_RETURN_FORM'                                     => ['BE' => false, 'NL' => false],
            'return_' . self::PACKAGE_TYPE_CONFIGURATION_NAME       => [
                'BE' => false,
                'NL' => false,
            ],
            'return_' . self::ONLY_RECIPIENT_CONFIGURATION_NAME     => ['BE' => false, 'NL' => false],
            'return_' . self::PACKAGE_FORMAT_CONFIGURATION_NAME     => ['BE' => false, 'NL' => false],
            'return_' . self::SIGNATURE_REQUIRED_CONFIGURATION_NAME => ['BE' => false, 'NL' => false],
            'return_' . self::INSURANCE_CONFIGURATION_NAME          => ['BE' => false, 'NL' => false],
            'return_' . self::AGE_CHECK_CONFIGURATION_NAME          => ['BE' => false, 'NL' => false],
            'return_' . self::RETURN_PACKAGE_CONFIGURATION_NAME     => ['BE' => false, 'NL' => false],
        ],
    ];
    public const NL_MONTHS = [
        1  => 'januari',
        2  => 'februari',
        3  => 'maart',
        4  => 'april',
        5  => 'mei',
        6  => 'juni',
        7  => 'juli',
        8  => 'augustus',
        9  => 'september',
        10 => 'oktober',
        11 => 'november',
        12 => 'december',
    ];
    public const NL_DAYS = [
        1 => 'maandag',
        2 => 'dinsdag',
        3 => 'woensdag',
        4 => 'donderdag',
        5 => 'vrijdag',
        6 => 'zaterdag',
        0 => 'zondag',
    ];
    public const MAIL_FALLBACK_LANGUAGE = 'en';
}

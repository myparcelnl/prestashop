<?php
/**
 * 2017-2018 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelCarrierDeliverySetting
 */
class MyParcelCarrierDeliverySetting extends MyParcelObjectModel
{
    const ENUM_NONE = 0;
    const ENUM_DELIVERY = 2;
    const ENUM_DELIVERY_SELF_DELAY = 3;

    const MONDAY_ENABLED = 'monday_enabled';
    const MONDAY_CUTOFF = 'monday_cutoff';
    const TUESDAY_ENABLED = 'tuesday_enabled';
    const TUESDAY_CUTOFF = 'tuesday_cutoff';
    const WEDNESDAY_ENABLED = 'wednesday_enabled';
    const WEDNESDAY_CUTOFF = 'wednesday_cutoff';
    const THURSDAY_ENABLED = 'thursday_enabled';
    const THURSDAY_CUTOFF = 'thursday_cutoff';
    const FRIDAY_ENABLED = 'friday_enabled';
    const FRIDAY_CUTOFF = 'friday_cutoff';
    const SATURDAY_ENABLED = 'saturday_enabled';
    const SATURDAY_CUTOFF = 'saturday_cutoff';
    const SUNDAY_ENABLED = 'sunday_enabled';
    const SUNDAY_CUTOFF = 'sunday_cutoff';
    const DAYTIME = 'daytime';
    const MORNING = 'morning';
    const MORNING_PICKUP = 'morning_pickup';
    const MORNING_FEE = 'morning_fee_tax_incl';
    const MORNING_PICKUP_FEE = 'morning_pickup_fee_tax_incl';
    const EVENING = 'evening';
    const EVENING_FEE = 'evening_fee_tax_incl';
    const SIGNED = 'signed';
    const SIGNED_FEE = 'signed_fee_tax_incl';
    const RECIPIENT_ONLY = 'recipient_only';
    const RECIPIENT_ONLY_FEE = 'recipient_only_fee_tax_incl';
    const SIGNED_RECIPIENT_ONLY = 'signed_recipient_only';
    const SIGNED_RECIPIENT_ONLY_FEE = 'signed_recipient_only_fee_tax_incl';
    const CUTOFF_EXCEPTIONS = 'cutoff_exceptions';
    const CUTOFF_EXCEPTIONS_SAMEDAY = 'cutoff_sameday_exceptions';
    const DELIVERYDAYS_WINDOW = 'timeframe_days';
    const DROPOFF_DELAY = 'dropoff_delay';

    const DELIVERY = 'delivery';
    const PICKUP = 'pickup';
    const MAILBOX_PACKAGE = 'mailbox_package';

    const DEFAULT_CUTOFF = '15:30';

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'myparcel_carrier_delivery_setting',
        'primary' => 'id_myparcel_carrier_delivery_setting',
        'fields' => array(
            'id_reference'                       => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'delivery'                           => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'pickup'                             => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'mailbox_package'                    => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'monday_enabled'                     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'tuesday_enabled'                    => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'wednesday_enabled'                  => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'thursday_enabled'                   => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'friday_enabled'                     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'saturday_enabled'                   => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default' => '0',
                'db_type' => 'TINYINT(1) UNSIGNED',
            ),
            'sunday_enabled'                     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'cutoff_exceptions'                  => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'TEXT',
            ),
            'monday_cutoff'                      => array(

                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'tuesday_cutoff'                     => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'wednesday_cutoff'                   => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'thursday_cutoff'                    => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'friday_cutoff'                      => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'saturday_cutoff'                    => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'sunday_cutoff'                      => array(
                'type'     => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'db_type'  => 'VARCHAR(5)',
            ),
            'daytime'                            => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'morning'                            => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'morning_pickup'                     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'evening'                            => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'signed'                             => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'recipient_only'                     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'signed_recipient_only'              => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'TINYINT(1) UNSIGNED',
            ),
            'timeframe_days'                     => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
                'default'  => '1',
                'db_type'  => 'INT(2) UNSIGNED',
            ),
            'dropoff_delay'                      => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'INT(2) UNSIGNED',
            ),
            'id_shop'                            => array(
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'INT(11) UNSIGNED',
            ),
            'morning_fee_tax_incl'               => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'morning_pickup_fee_tax_incl'        => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'default_fee_tax_incl'               => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'evening_fee_tax_incl'               => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'signed_fee_tax_incl'                => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'recipient_only_fee_tax_incl'        => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
            'signed_recipient_only_fee_tax_incl' => array(
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default'  => '0',
                'db_type'  => 'DECIMAL(15, 6)',
            ),
        ),
    );
    /** @var int $id_reference */
    public $id_reference;
    /** @var bool $delivery */
    public $delivery;
    /** @var bool $pickup */
    public $pickup;
    /** @var bool $mailbox_package */
    public $mailbox_package;
    /** @var bool $monday_enabled */
    public $monday_enabled;
    /** @var string $monday_cutoff */
    public $monday_cutoff;
    /** @var bool $tuesday_enabled */
    public $tuesday_enabled;
    /** @var string $tuesday_cutoff */
    public $tuesday_cutoff;
    /** @var bool $wednesday_enabled */
    public $wednesday_enabled;
    /** @var string $wednesday_cutoff */
    public $wednesday_cutoff;
    /** @var bool $thursday_enabled */
    public $thursday_enabled;
    /** @var string $thursday_cutoff */
    public $thursday_cutoff;
    /** @var bool $friday_enabled */
    public $friday_enabled;
    /** @var string $friday_cutoff */
    public $friday_cutoff;
    /** @var bool $saturday_enabled */
    public $saturday_enabled;
    /** @var string $saturday_cutoff */
    public $saturday_cutoff;
    /** @var bool $sunday_enabled */
    public $sunday_enabled;
    /** @var string $sunday_cutoff */
    public $sunday_cutoff;
    /** @var string $cutoff_exceptions */
    public $cutoff_exceptions;
    /** @var bool $daytime */
    public $daytime;
    /** @var bool $morning */
    public $morning;
    /** @var bool $morning_pickup */
    public $morning_pickup;
    /** @var bool $evening */
    public $evening;
    /** @var bool $signed */
    public $signed;
    /** @var bool $recipient_only */
    public $recipient_only;
    /** @var bool $signed_recipient_only */
    public $signed_recipient_only;
    /** @var int $timeframe_days */
    public $timeframe_days;
    /** @var int $dropoff_delay */
    public $dropoff_delay;
    /** @var float $morning_fee_tax_incl */
    public $morning_fee_tax_incl;
    /** @var float $morning_pickup_fee_tax_incl */
    public $morning_pickup_fee_tax_incl;
    /** @var float $default_fee_tax_incl */
    public $default_fee_tax_incl;
    /** @var float $evening_fee_tax_incl */
    public $evening_fee_tax_incl;
    /** @var float $signed_fee_tax_incl */
    public $signed_fee_tax_incl;
    /** @var float $recipient_only_fee_tax_incl */
    public $recipient_only_fee_tax_incl;
    /** @var float $signed_recipient_only_fee_tax_incl */
    public $signed_recipient_only_fee_tax_incl;
    /** @var int $id_shop Shop ID */
    public $id_shop;
    // @codingStandardsIgnoreEnd

    /**
     * MyParcelDeliveryOption constructor.
     *
     * @param int $id     MyParcel Delivery Option ID
     * @param int $idLang Language ID
     * @param int $idShop Shop ID
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public function __construct($id = null, $idLang = null, $idShop = null)
    {
        parent::__construct($id, $idLang, $idShop);

        // Check if cutoff exceptions at least have their defaults
        if (empty($this->cutoff_exceptions)) {
            $this->cutoff_exceptions = '{}';
        }

        $cutoffs = array(
            'monday_cutoff',
            'tuesday_cutoff',
            'wednesday_cutoff',
            'thursday_cutoff',
            'friday_cutoff',
            'saturday_cutoff',
            'sunday_cutoff',
        );

        foreach ($cutoffs as $cutoff) {
            if (empty($this->{$cutoff})) {
                $this->{$cutoff} = '15:30';
            }
        }
    }

    /**
     * @param int      $idReference
     * @param int|null $idShop
     *
     * @return MyParcelCarrierDeliverySetting
     *
     * @throws Adapter_Exception
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function createDefault($idReference, $idShop = null)
    {
        if (!$idShop) {
            $idShop = (int) Context::getContext()->shop->id;
        }

        $mcds = new static();
        foreach (static::$definition['fields'] as $key => $field) {
            if ($field['required'] && $field['default']) {
                $mcds->{$key} = $field['default'];
            }
        }
        $mcds->id_reference = $idReference;
        $mcds->id_shop = $idShop;

        return $mcds;
    }

    /**
     * Get delivery option by Carrier Reference
     *
     * @param int $idReference Carrier reference
     *
     * @return bool|MyParcelCarrierDeliverySetting
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function getByCarrierReference($idReference)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(MyParcelCarrierDeliverySetting::$definition['table']), 'pcds');
        $sql->where('pcds.`id_reference` = '.(int) $idReference);

        try {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return false;
        }
        if (empty($result)) {
            return false;
        }

        $deliverySetting = new MyParcelCarrierDeliverySetting();
        $deliverySetting->hydrate($result);
        if (!Validate::isLoadedObject($deliverySetting)) {
            return false;
        }

        return $deliverySetting;
    }

    /**
     * Get Carrier reference by MyParcelCarrierDeliverySetting ID
     *
     * @param int $idMyParcelCarrierDeliverySetting MyParcelCarrierDeliverySetting ID
     *
     * @return bool|MyParcelCarrierDeliverySetting
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function getCarrierReferenceById($idMyParcelCarrierDeliverySetting)
    {
        $sql = new DbQuery();
        $sql->select('pcds.`id_reference`');
        $sql->from(bqSQL(static::$definition['table']), 'pcds');
        $sql->where('pcds.`'.bqSQL(static::$definition['primary']).'` = '.(int) $idMyParcelCarrierDeliverySetting);

        try {
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return 0;
        }
    }

    /**
     * Toggle delivery status
     *
     * @param int $idMyParcelCarrierDeliverySetting
     *
     * @return bool Indicates whether the delivery status has been successfully toggled
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function toggleDelivery($idMyParcelCarrierDeliverySetting)
    {
        $mpcds = new static($idMyParcelCarrierDeliverySetting);
        $mpcds->delivery = !$mpcds->delivery;

        if ($mpcds->delivery) {
            $mpcds->mailbox_package = false;
        }

        MyParcel::processCarrierDeliverySettingsRestrictions($mpcds);

        return $mpcds->save();
    }

    /**
     * Save
     *
     * @param bool $nullValues
     * @param bool $autoDate
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public function save($nullValues = false, $autoDate = true)
    {
        try {
            // Need to be associated with an active carrier
            $carrier = Carrier::getCarrierByReference($this->id_reference);
            if (!Validate::isLoadedObject($carrier)) {
                return false;
            }
            // Cannot associate with this carrier if it is already managage by another module
            if ($carrier->external_module_name && $carrier->external_module_name !== 'myparcel') {
                return false;
            }
            // No delivery options for this carrier => release carrier
            if (!$this->pickup && !$this->delivery && !$this->mailbox_package) {
                static::associateCarrierToModule($this->id_reference, false);
            } else {
                static::associateCarrierToModule($this->id_reference);
                if ($this->mailbox_package) {
                    Db::getInstance()->update(
                        'carrier',
                        array(
                            'max_width'  => 0,
                            'max_height' => 0,
                            'max_depth'  => 0,
                            'max_weight' => 0,
                        ),
                        '`id_reference` = '.(int) $this->id_reference.' AND `deleted` = 0'
                    );
                }
            }

            return parent::save($nullValues, $autoDate);
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Associate a carrier with a module
     *
     * @param int  $idReference Carrier reference ID
     * @param bool $associate   Associate/disassociate the module
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function associateCarrierToModule($idReference, $associate = true)
    {
        try {
            if ($associate) {
                return Db::getInstance()->update(
                    'carrier',
                    array(
                        'external_module_name' => 'myparcel',
                        'shipping_external'    => 1,
                        'is_module'            => 1,
                        'need_range'           => 1,
                    ),
                    '`id_reference` = '.(int) $idReference.' AND `deleted` = 0'
                );
            } else {
                return Db::getInstance()->update(
                    'carrier',
                    array(
                        'external_module_name' => '',
                        'shipping_external'    => 0,
                        'is_module'            => 0,
                    ),
                    '`id_reference` = '.(int) $idReference.' AND `deleted` = 0'
                );
            }
        } catch (PrestaShopException $e) {
            Logger::addLog("MyParcel module error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Toggle pickup status
     *
     * @param int $idMyParcelCarrierDeliverySetting
     *
     * @return bool Indicates whether the pickup status has been successfully toggled
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function togglePickup($idMyParcelCarrierDeliverySetting)
    {
        $mpcds = new self($idMyParcelCarrierDeliverySetting);
        $mpcds->pickup = !$mpcds->pickup;

        if ($mpcds->pickup) {
            $mpcds->mailbox_package = false;
        }

        MyParcel::processCarrierDeliverySettingsRestrictions($mpcds);

        return $mpcds->save();
    }

    /**
     * Toggle mailbox pacckage status
     *
     * @param int $idMyParcelCarrierDeliverySetting
     *
     * @return bool Indicates whether the mailbox package status has been successfully toggled
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @since 2.0.0
     */
    public static function toggleMailboxPackage($idMyParcelCarrierDeliverySetting)
    {
        $mpcds = new self($idMyParcelCarrierDeliverySetting);
        $mpcds->mailbox_package = !$mpcds->mailbox_package;

        MyParcel::processCarrierDeliverySettingsRestrictions($mpcds);

        return $mpcds->save();
    }

    /**
     * Get cut off times
     *
     * @param string $dateFrom From date
     * @param int    $method   Method
     *
     * @return array Array with cut off times
     *
     * @since 2.0.0
     */
    public function getCutOffTimes($dateFrom, $method)
    {
        $cutoffTimes = array();
        if ($method === static::ENUM_DELIVERY) {
            $date = new DateTime($dateFrom);
            $cutoffExceptions = @json_decode($this->cutoff_exceptions, true);
            if (!is_array($cutoffExceptions)) {
                $cutoffExceptions = array();
            }
            for ($i = 0; $i < 7; $i++) {
                if (array_key_exists($date->format('d-m-Y'), $cutoffExceptions)) {
                    $exceptionInfo = $cutoffExceptions[$date->format('d-m-Y')];
                    if ((array_key_exists('nodispatch', $exceptionInfo) && $exceptionInfo['nodispatch'])
                        && (array_key_exists('cutoff', $exceptionInfo))
                    ) {
                        $nodispatch = false;
                    } else {
                        $nodispatch = true;
                    }

                    $cutoffTimes[$i] = array(
                        'name'       => Translate::getModuleTranslation(
                            'mpmyparceldeliveryopts',
                            $date->format('D'),
                            'dates'
                        ),
                        'time'       => (array_key_exists('cutoff', $exceptionInfo)
                            ? $exceptionInfo['cutoff']
                            : ''),
                        'exception'  => true,
                        'nodispatch' => $nodispatch,
                    );
                } elseif ((bool) $this->{Tools::strtolower($date->format('l')).'_enabled'}) {
                    $cutoffTimes[$i] = array(
                        'name'       => Translate::getModuleTranslation(
                            'mpmyparceldeliveryopts',
                            $date->format('D'),
                            'dates'
                        ),
                        'time'       => $this->{Tools::strtolower($date->format('l')).'_cutoff'},
                        'exception'  => false,
                        'nodispatch' => false,
                    );
                } else {
                    $cutoffTimes[$i] = array(
                        'name'       => Translate::getModuleTranslation(
                            'mpmyparceldeliveryopts',
                            $date->format('D'),
                            'dates'
                        ),
                        'time'       => '',
                        'exception'  => false,
                        'nodispatch' => true,
                    );
                }
                $date->modify('+1 day');
            }
        }

        return $cutoffTimes;
    }

    /**
     * Get cut off times
     *
     * @param string $dateFrom From date
     * @param int    $days     For how many days?
     *
     * @return array Array with cut off times
     *
     * @since 2.0.0
     * @since 2.2.0 Returned array key is now `Y-m-d` date
     */
    public function getDropoffDays($dateFrom, $days = 7)
    {
        $cutoffTimes = array();

        $date = new DateTime($dateFrom);
        $cutoffExceptions = @json_decode($this->cutoff_exceptions, true);
        if (!is_array($cutoffExceptions)) {
            $cutoffExceptions = array();
        }
        for ($i = 0; $i < $days; $i++) {
            if (array_key_exists($date->format('d-m-Y'), $cutoffExceptions)) {
                $exceptionInfo = $cutoffExceptions[$date->format('d-m-Y')];
                if ((array_key_exists('nodispatch', $exceptionInfo) && $exceptionInfo['nodispatch'])
                    && (array_key_exists('cutoff', $exceptionInfo))
                ) {
                    $nodispatch = false;
                } else {
                    $nodispatch = true;
                }

                if (!$nodispatch) {
                    $cutoffTimes[$date->format('Y-m-d')] = $date->format('N');
                }
            } elseif ((bool) $this->{Tools::strtolower($date->format('l')).'_enabled'}) {
                $cutoffTimes[$date->format('Y-m-d')] = $date->format('N');
            }

            $date->modify('+1 day');
        }

        return $cutoffTimes;
    }

    /**
     * Get delivery options array
     *
     * @return array Array
     *
     * @since 2.0.0
     */
    public function getOptions()
    {
        return array(
            static::MORNING => $this->{static::MORNING},
            static::EVENING => $this->{static::EVENING},
            static::DAYTIME => $this->{static::DAYTIME},
        );
    }

    /**
     * Get the cutoff exceptions hash
     * Useful to invalidate caching
     *
     * @return string
     *
     * @since 2.1.0
     */
    public function getCutoffExceptionsHash()
    {
        return md5($this->cutoff_exceptions);
    }
}

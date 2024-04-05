<?php
/**
 * 2017-2019 DM Productions B.V.
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
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * @param MyParcel $module
 *
 * @return bool
 * @throws PrestaShopDatabaseException
 * @throws PrestaShopException
 */
function upgrade_module_2_2_0($module)
{
    /** @var MyParcel $module */
    Configuration::deleteByName('MYPARCEL_SUPPORTED');
    Configuration::deleteByName('MYPARCEL_EU');
    Configuration::deleteByName('MYPARCEL_UPDATE_OS');
    Configuration::updateValue('MYPARCEL_TOUR_STEP', 99, false, 0, 0);
    Configuration::updateValue('MYPARCEL_CHECKOUT_FG_COLOR3', '#000000');
    Configuration::updateValue('MYPARCEL_CHECKOUT_IA_COLOR', '#848484');

    $columnsToDrop = array(
        'country_iso',
        'company',
        'name',
        'postcode',
        'house_number',
        'house_number_add',
        'street1',
        'street2',
        'email',
        'phone',
    );

    foreach ($columnsToDrop as $columnToDrop) {
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
            AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_delivery_option\'
            AND COLUMN_NAME = \''.pSQL($columnToDrop).'\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_delivery_option` DROP `'.bqSQL($columnToDrop).'`';
        }
    }

    Configuration::updateValue('MYPARCEL_ASK_PAPER_SELECT', true);
    Configuration::updateValue('MYPARCEL_ASK_RETURN_SELECT', true);
    Configuration::updateValue('MYPARCEL_MON_DEL', true);
    $module->registerHook('actionAdminLogsListingFieldsModifier');
    $module->registerHook('registerGDPRConsent');
    $module->registerHook('actionDeleteGDPRCustomer');
    $module->registerHook('actionExportGDPRData');

    return true;
}

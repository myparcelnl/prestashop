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

if (!defined('_PS_VERSION_') && !defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param MyParcel $module
 *
 * @return bool
 * @throws PrestaShopException
 */
function upgrade_module_2_0_0($module)
{
    /** @var MyParcel $module */

    $sql = array();

    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel` RENAME TO `'._DB_PREFIX_.'myparcel_order`';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'id_myparcel_order\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `myparcel_id`'
                .' `id_myparcel_order` INT(11) NOT NULL AUTO_INCREMENT';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'id_order\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `order_id` `id_order` INT(11) NOT NULL';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'postnl_status\'')) {
            $sql[] =
                'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_status` `postnl_status` VARCHAR(255) NOT NULL';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'date_upd\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_updated_on` `date_upd` DATETIME NOT NULL';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'postnl_final\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_final` `postnl_final`'
                .' TINYINT(1) NOT NULL DEFAULT \'0\'';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'id_shipment\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `consignment_id`'
                .' `id_shipment` BIGINT(20) NOT NULL DEFAULT \'0\'';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }

    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `postnl_status` `postnl_status`'
        .' VARCHAR(255) DEFAULT \'1\'';
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tracktrace` `tracktrace` VARCHAR(32)';
    if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'shipment\'')) {
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` ADD `shipment` TEXT';
    }
    if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_order\'
                AND COLUMN_NAME = \'type\'')) {
        $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` ADD `type` TINYINT(1) NOT NULL DEFAULT \'1\'';
    }
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `retour` `retour` TINYINT(1) NOT NULL DEFAULT \'0\'';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'myparcel_order_history` (
  `id_myparcel_order_history` INT(11) UNSIGNED           NOT NULL AUTO_INCREMENT,
  `id_shipment`               BIGINT(20)                 NOT NULL,
  `postnl_status`             VARCHAR(255) DEFAULT \'1\' NOT NULL,
  `date_upd`                  DATETIME                   NOT NULL,
  PRIMARY KEY (`id_myparcel_order_history`)
)';
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'myparcel_carrier_delivery_setting` (
  `id_myparcel_carrier_delivery_setting` INT(11) UNSIGNED                  NOT NULL AUTO_INCREMENT,
  `id_reference`                           INT DEFAULT \'0\'                 NOT NULL,
  `delivery`                               TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `pickup`                                 TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `mailbox_package`                        TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `monday_enabled`                         TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `tuesday_enabled`                        TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `wednesday_enabled`                      TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `thursday_enabled`                       TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `friday_enabled`                         TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `saturday_enabled`                       TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `sunday_enabled`                         TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `cutoff_exceptions`                      TEXT                              NOT NULL,
  `monday_cutoff`                          VARCHAR(5)                        NOT NULL,
  `tuesday_cutoff`                         VARCHAR(5)                        NOT NULL,
  `wednesday_cutoff`                       VARCHAR(5)                        NOT NULL,
  `thursday_cutoff`                        VARCHAR(5)                        NOT NULL,
  `friday_cutoff`                          VARCHAR(5)                        NOT NULL,
  `saturday_cutoff`                        VARCHAR(5)                        NOT NULL,
  `sunday_cutoff`                          VARCHAR(5)                        NOT NULL,
  `daytime`                                TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `morning`                                TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `morning_pickup`                         TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `evening`                                TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `signed`                                 TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `recipient_only`                         TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `signed_recipient_only`                  TINYINT(1) DEFAULT \'0\'          NOT NULL,
  `timeframe_days`                         INT(2) DEFAULT \'1\'              NOT NULL,
  `dropoff_delay`                          INT(2) DEFAULT \'0\'              NOT NULL,
  `id_shop`                                INT(11) UNSIGNED DEFAULT \'0\'    NOT NULL,
  `morning_fee_tax_incl`                   DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `morning_pickup_fee_tax_incl`            DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `default_fee_tax_incl`                   DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `evening_fee_tax_incl`                   DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `signed_fee_tax_incl`                    DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `recipient_only_fee_tax_incl`            DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
  `signed_recipient_only_fee_tax_incl`     DECIMAL(15,6) DEFAULT \'0.00000\' NOT NULL,
	PRIMARY KEY (`id_myparcel_carrier_delivery_setting`)
)';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'myparcel_delivery_option` (
  `id_myparcel_delivery_option` INT AUTO_INCREMENT,
  `id_cart`                     INT DEFAULT \'0\' NOT NULL,
  myparcel_delivery_option      TEXT NOT NULL,
  country_iso                   CHAR(2) NOT NULL,
  company                       VARCHAR(255) NOT NULL,
  name                          TEXT NOT NULL,
  postcode                      VARCHAR(16) NOT NULL,
  house_number                  VARCHAR(16) NOT NULL,
  house_number_add              VARCHAR(16) NOT NULL,
  street1                       TEXT NOT NULL,
  street2                       TEXT NOT NULL,
  email                         VARCHAR(255) NOT NULL,
  phone                         VARCHAR(255) NOT NULL,
  identifier                    VARCHAR(255) NOT NULL,
  UNIQUE (`id_cart`),
  PRIMARY KEY(`id_myparcel_delivery_option`)
)';

    $hooks = array(
        'displayCarrierList',
        'displayHeader',
        'displayBackOfficeHeader',
        'adminOrder',
        'orderDetail',
        'actionValidateOrder',
    );

    foreach ($hooks as $hook) {
        try {
            $module->registerHook($hook);
        } catch (PrestaShopException $e) {
        }
    }

    // Remove the old template files
    foreach (array(
        _PS_OVERRIDE_DIR_.'controllers/admin/templates/orders/helpers/list/list_content.tpl',
        _PS_OVERRIDE_DIR_.'controllers/admin/templates/orders/helpers/list/list_header.tpl',
    ) as $file) {
        if (file_exists($file)) {
            if (!@unlink($file)) {
                Context::getContext()->controller->warnings[] =
                    "Unable to remove file {$file} due to a permission error. Please remove this file manually!";
            }
        }
    }

    // Clear the smarty cache hereafter
    Tools::clearCache(Context::getContext()->smarty);

    foreach ($sql as $query) {
        try {
            if (!Db::getInstance()->execute($query)) {
                $errorMessage = Db::getInstance()->getMsgError();
                Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$errorMessage}";

                return false;
            }
        } catch (PrestaShopException $e) {
            Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

            return false;
        }
    }

    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_FG_COLOR1', '#FFFFFF');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_FG_COLOR2', '#000000');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_BG_COLOR1', '#FBFBFB');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_BG_COLOR2', '#01BBC5');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_BG_COLOR3', '#75D3D8');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_HL_COLOR', '#FF8C00');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_FONT', 'Exo');
    Configuration::updateGlobalValue('MYPARCEL_CHECKOUT_FSIZE', 2);
    Configuration::updateGlobalValue('MYPARCEL_LABEL_DESCRIPTION', '{order.reference}');
    Configuration::updateGlobalValue('MYPARCEL_SHIPPED_STATUS', (int) Configuration::get('PS_OS_SHIPPING'));
    Configuration::updateGlobalValue('MYPARCEL_RECEIVED_STATUS', (int) Configuration::get('PS_OS_DELIVERED'));

    return true;
}

<?php
/**
 * 2017 DM Productions B.V.
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
 * @author     DM Productions B.V. <info@dmp.nl>
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2017 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

function upgrade_module_2_0_0($module)
{
    /** @var MyParcel $module */

    $sql = array();
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel` RENAME TO `'._DB_PREFIX_.'myparcel_order`';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `myparcel_id` `id_myparcel_order` INT(11) NOT NULL AUTO_INCREMENT';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `order_id` `id_order` INT(11) NOT NULL';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_status` `postnl_status` VARCHAR(255) NOT NULL';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_updated_on` `date_upd` DATETIME NOT NULL';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tnt_final` `postnl_final` TINYINT(1) NOT NULL DEFAULT \'0\'';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `consignment_id` `id_shipment` BIGINT(20) NOT NULL DEFAULT \'0\'';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `postnl_status` `postnl_status` VARCHAR(255) DEFAULT \'1\'';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `retour` `retour` TINYINT(1) NOT NULL DEFAULT \'0\'';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` CHANGE `tracktrace` `tracktrace` VARCHAR(32)';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` ADD `shipment` TEXT';
    $sql[] = 'ALTER IGNORE TABLE `'._DB_PREFIX_.'myparcel_order` ADD `type` TINYINT(1) NOT NULL DEFAULT \'1\'';
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
  `morning_fee_tax_incl`                   DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `morning_pickup_fee_tax_incl`            DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `default_fee_tax_incl`                   DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `evening_fee_tax_incl`                   DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `signed_fee_tax_incl`                    DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `recipient_only_fee_tax_incl`            DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
  `signed_recipient_only_fee_tax_incl`     DECIMAL(15,5) DEFAULT \'0.00000\' NOT NULL,
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
        $module->registerHook($hook);
    }

    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            Logger::addLog($module->l('Could not upgrade the MyParcel module due to a MySQL error: ').Db::getInstance()->getMsgError());

            return false;
        }
    }

    return true;
}

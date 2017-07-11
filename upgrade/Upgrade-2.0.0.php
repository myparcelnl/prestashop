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
  id_myparcel_order_history UNSIGNED INT(11)           NOT NULL AUTO_INCREMENT,
  id_shipment               BIGINT(20)                 NOT NULL,
  postnl_status             VARCHAR(255) DEFAULT \'1\' NOT NULL,
  date_upd                  DATETIME                   NOT NULL,
  PRIMARY KEY (`id_myparcel_order_history`)
);';

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

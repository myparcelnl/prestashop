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
function upgrade_module_2_3_3($module)
{
    if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(*)
FROM information_schema.statistics
WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
  AND TABLE_NAME = \''._DB_PREFIX_.pSQL(MyParcelOrder::$definition['table']).'\' 
  AND INDEX_NAME = \'mpo_id_order\'')) {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.pSQL(MyParcelOrder::$definition['table']).'` ADD INDEX `mpo_id_order` (`id_order`)');
    }

    return true;
}

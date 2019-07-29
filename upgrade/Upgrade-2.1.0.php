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
 */
function upgrade_module_2_1_0($module)
{
    $sql = array();

    // Add two extra columns to the delivery option object for the renewed order grid
    /** @var MyParcel $module */
    try {
        if (!Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_delivery_option\'
                AND COLUMN_NAME = \'date_delivery\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_delivery_option` ADD `date_delivery` DATETIME NULL';
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
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_delivery_option\'
                AND COLUMN_NAME = \'pickup\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_delivery_option` ADD `pickup` VARCHAR(255) NULL';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }

    try {
        if (Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
                SELECT COUNT(*)
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = \''._DB_NAME_.'\'
                AND TABLE_NAME = \''._DB_PREFIX_.'myparcel_delivery_option\'
                AND COLUMN_NAME = \'identifier\'')) {
            $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'myparcel_delivery_option` DROP `identifier`;';
        }
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }

    // Set the new config key's name
    $sql[] = 'UPDATE `'._DB_PREFIX_.'configuration` SET `name` = \'MYPARCEL_UPDATE_OS\' WHERE `name` ='.
        ' \'MYPARCEL_WEBHOOK_ENABLED\'';

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

    // Grab the dates from old values, making them directly accessible from the delivery option table
    // and filterable/sortable on the order grid
    $sql = new DbQuery();
    $sql->select('COUNT(*)');
    $sql->from('orders', 'o');
    $sql->innerJoin('myparcel_delivery_option', 'mdo', 'o.`id_cart` = mdo.`id_cart`');
    $sql->where('mdo.`myparcel_delivery_option` IS NOT NULL');
    $sql->where('mdo.`myparcel_delivery_option` != \'\'');
    try {
        $total = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    } catch (PrestaShopException $e) {
        Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

        return false;
    }

    // Determine how many chunks we'll be processing
    $chunkSize = 100;
    $nbChunks = ceil($total / $chunkSize);

    for ($i = 0; $i < $nbChunks; $i++) {
        $sql = new DbQuery();
        $sql->select('o.`date_add`, mdo.`id_myparcel_delivery_option`');
        $sql->select('mdo.`myparcel_delivery_option` as `option`');
        $sql->from('orders', 'o');
        $sql->innerJoin('myparcel_delivery_option', 'mdo', 'o.`id_cart` = mdo.`id_cart`');
        $sql->where('mdo.`myparcel_delivery_option` IS NOT NULL');
        $sql->where('mdo.`myparcel_delivery_option` != \'\'');
        $sql->limit($chunkSize, $i * $chunkSize);
        try {
            $orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopException $e) {
            Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

            return false;
        }

        $updates = '';
        foreach ($orders as $order) {
            if (!$order['option']) {
                continue;
            }
            $orderData = @json_decode($order['option'], true);
            if (!isset($orderData['type'])) {
                continue;
            }

            if (isset($orderData['data']['date'])) {
                $deliveryDate = $orderData['data']['date'];

                if (isset($orderData['data']['time'][0]['start'])) {
                    $deliveryDate .= " {$orderData['data']['time'][0]['start']}";
                } else {
                    $deliveryDate .= ' 15:00:00';
                }
            } else {
                // Data gone :O
                $deliveryDate = '1970-01-01 00:00:00';
            }

            $updates .= "UPDATE `"._DB_PREFIX_."myparcel_delivery_option` SET `date_delivery` = '$deliveryDate'".
                " WHERE `id_myparcel_delivery_option` = {$order['id_myparcel_delivery_option']};\n";
        }

        try {
            Db::getInstance()->execute($updates);
        } catch (PrestaShopException $e) {
            Context::getContext()->controller->errors[] = "Unable to update the MyParcel module: {$e->getMessage()}";

            return false;
        }
    }

    Configuration::updateValue('MYPARCEL_CHECKOUT_FSIZE', 2);
    Configuration::updateValue('MYPARCEL_NOTIF_MOMENT', 1);
    Configuration::updateValue('MYPARCEL_PAPER_SELECTION', mypa_json_encode(array(
        'size' => 'standard',
        'labels' => array(
            1 => true,
            2 => true,
            3 => true,
            4 => true,
        ),
    )));

    try {
        $module->registerHook('actionAdminOrdersListingFieldsModifier');
    } catch (PrestaShopException $e) {
    }

    // Clear the Smarty cache
    if (method_exists('Tools', 'clearCache')) {
        Tools::clearCache(Context::getContext()->smarty);
    }

    // Clear the OPCache
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }

    return true;
}

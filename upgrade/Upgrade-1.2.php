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

function upgrade_module_1_2($module)
{
    /** @var MyParcel $module */
    // Uninstall overrides
    try {
        $module->uninstallOverrides();
    } catch (Exception $e) {
        die(sprintf(Tools::displayError('Unable to uninstall overrides: %s'), $e->getMessage()));
    }

    // Remove overridden admin templates and unnecessary files
    $overrideAdminDir = _PS_ROOT_DIR_.'/override/controllers/admin';

    $srcDir = $overrideAdminDir.'/templates/orders/helpers/list';

    if (is_file($srcDir.'/list_content.tpl')) {
        unlink($srcDir.'/list_content.tpl');
    }

    if (is_file($srcDir.'/list_header.tpl')) {
        unlink($srcDir.'/list_header.tpl');
    }

    // Install tab to register controllers
    $module->installTab();

    // Clear cache
    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
        Tools::clearSmartyCache();
        PrestaShopAutoload::getInstance()->generateIndex();
    } else {
        Tools::clearCache();
        Autoload::getInstance()->generateIndex();
    }

    return true;
}

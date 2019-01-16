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
 * Class MyParcelDemo
 */
class MyParcelDemo
{
    /**
     * Initialize content
     *
     * @return string
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     * @throws SmartyException
     * @throws ErrorException
     */
    public static function renderDemo()
    {
        header('Content-Type: text/html;charset=utf-8');

        $smarty = Context::getContext()->smarty;
        $smarty->assign(array(
            'language_code'          => Tools::strtolower(Context::getContext()->language->language_code),
            'mypaCheckoutJs'         => Media::getJSPath(_PS_MODULE_DIR_.'myparcel/views/js/dist/front-2411272a95c2d98f.bundle.min.js'),
            'base_dir_ssl'           => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').Tools::getShopDomainSsl().__PS_BASE_URI__,
            'signedPreferred'        => (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_SIGNED),
            'recipientOnlyPreferred' => (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
            'foreground1color'       => Configuration::get(MyParcel::CHECKOUT_FG_COLOR1),
            'foreground2color'       => Configuration::get(MyParcel::CHECKOUT_FG_COLOR2),
            'foreground3color'       => Configuration::get(MyParcel::CHECKOUT_FG_COLOR3),
            'background1color'       => Configuration::get(MyParcel::CHECKOUT_BG_COLOR1),
            'background2color'       => Configuration::get(MyParcel::CHECKOUT_BG_COLOR2),
            'background3color'       => Configuration::get(MyParcel::CHECKOUT_BG_COLOR3),
            'highlightcolor'         => Configuration::get(MyParcel::CHECKOUT_HL_COLOR),
            'inactivecolor'          => Configuration::get(MyParcel::CHECKOUT_INACTIVE_COLOR),
            'fontfamily'             => Configuration::get(MyParcel::CHECKOUT_FONT),
        ));
        echo $smarty->fetch(_PS_MODULE_DIR_.'myparcel/views/templates/admin/examplecheckout/checkout.tpl');
        exit;
    }
}

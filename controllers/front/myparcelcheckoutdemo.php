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

require_once dirname(__FILE__).'/../../myparcel.php';

/**
 * Class MyParcelmyparcelcheckoutdemoModuleFrontController
 *
 * @since 2.0.0
 */
class MyParcelmyparcelcheckoutdemoModuleFrontController extends ModuleFrontController
{
    /**
     * MyParcelmyparcelcheckoutdemoModuleFrontController constructor.
     *
     * @since 2.0.0
     *
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct()
    {
        parent::__construct();

        $this->ssl = Tools::usingSecureMode();

        // Check if employee is logged in
        $cookie = new Cookie('psAdmin');
        if (!$cookie->id_employee) {
            Tools::redirectLink($this->context->link->getPageLink('index'));
        }
    }

    /**
     * Prevent displaying the maintenance page
     *
     * @return void
     */
    protected function displayMaintenancePage()
    {
        // Disable the maintenance page
    }

    /**
     * Initialize content
     *
     * @return string
     *
     * @since 2.0.0
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function initContent()
    {
        $smarty = $this->context->smarty;

        $smarty->assign(array(
            'language_code'          => Tools::strtolower(Context::getContext()->language->language_code),
            'checkoutJs'             => Media::getJSPath(
                _PS_MODULE_DIR_.'myparcel/views/js/app/dist/checkout-a47c55b383f5ef9f.bundle.min.js'
            ),
            'base_dir_ssl'           => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                .Tools::getShopDomainSsl().__PS_BASE_URI__,
            'signedPreferred'        => (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_SIGNED),
            'recipientOnlyPreferred' => (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
        ));

        echo $smarty->fetch(_PS_MODULE_DIR_.'myparcel/views/templates/admin/examplecheckout/checkout.tpl');
        exit;
    }
}

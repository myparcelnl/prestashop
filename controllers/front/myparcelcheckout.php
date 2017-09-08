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

if (!defined('_PS_VERSION_') && !defined('_TB_VERSION_')) {
    exit;
}

/**
 * Class MyParcelmyparcelcheckoutModuleFrontController
 *
 * @since 2.0.0
 */
class MyParcelmyparcelcheckoutModuleFrontController extends ModuleFrontController
{
    const BASE_URI = 'https://api.myparcel.nl/delivery_options';

    /** @var MyParcelCarrierDeliverySetting $myParcelCarrierDeliverySetting */
    protected $myParcelCarrierDeliverySetting;

    /**
     * MyParcelmyparcelcheckoutModuleFrontController constructor.
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        parent::__construct();

        $this->ssl = Tools::usingSecureMode();
    }

    /**
     * Initialize content
     *
     * @return string
     *
     * @since 2.0.0
     */
    public function initContent()
    {
        if (Tools::isSubmit('ajax')) {
            $this->getDeliveryOptions();

            return;
        }

        $context = Context::getContext();

        /** @var Cart $cart */
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            $this->hideMe();
        }

        $address = new Address((int) $cart->id_address_delivery);
        if (!preg_match('/^(.*?)\s+(\d+)(.*)$/', $address->address1.' '.$address->address2, $m)) {
            // No house number
            $this->hideMe();
        }

        $streetName = trim($m[1]);
        $houseNumber = trim($m[2]);

        // id_carrier is not defined in database before choosing a carrier, set it to a default one to match a potential cart _rule
        if (empty($cart->id_carrier)) {
            $checked = $cart->simulateCarrierSelectedOutput();
            $checked = ((int) Cart::desintifier($checked));
            $cart->id_carrier = $checked;
            $cart->update();
            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
        }

        $carrier = new Carrier($cart->id_carrier);
        if (!Validate::isLoadedObject($carrier)) {
            $this->hideMe();
        }

        $this->myParcelCarrierDeliverySetting = MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!$this->myParcelCarrierDeliverySetting) {
            $this->hideMe();
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>')
            && !($this->myParcelCarrierDeliverySetting->delivery || $this->myParcelCarrierDeliverySetting->pickup)
        ) {
            $this->hideMe();
        }

        $cutoffTimes = $this->myParcelCarrierDeliverySetting->getCutOffTimes(date('Y-m-d'), MyParcelCarrierDeliverySetting::ENUM_DELIVERY);
        if (isset($cutoffTimes[0]['time'])) {
            $cutoffTime = $cutoffTimes[0]['time'];
        } else {
            $cutoffTime = MyParcelCarrierDeliverySetting::DEFAULT_CUTOFF;
        }

        $countryIso = Tools::strtolower(Country::getIsoById($address->id_country));
        if (!in_array($countryIso, array('nl', 'be'))) {
            $this->hideMe();
        }

        $this->context->smarty->assign(
            array(
                'streetName'                    => $streetName,
                'houseNumber'                   => $houseNumber,
                'postcode'                      => $address->postcode,
                'langIso'                       => Tools::strtolower(Context::getContext()->language->iso_code),
                'countryIso'                    => $countryIso,
                'express'                       => (bool) $this->myParcelCarrierDeliverySetting->morning_pickup,
                'delivery'                      => (bool) $this->useTimeframes(),
                'pickup'                        => (bool) $this->myParcelCarrierDeliverySetting->pickup,
                'morning'                       => (bool) $this->myParcelCarrierDeliverySetting->morning,
                'morningFeeTaxIncl'             => $carrier->is_free ? 0 : (float) $this->myParcelCarrierDeliverySetting->morning_fee_tax_incl,
                'morningPickupFeeTaxIncl'       => $carrier->is_free ? 0 : (float) $this->myParcelCarrierDeliverySetting->morning_pickup_fee_tax_incl,
                'night'                         => (bool) $this->myParcelCarrierDeliverySetting->evening,
                'nightFeeTaxIncl'               => $carrier->is_free ? 0 : (float) $this->myParcelCarrierDeliverySetting->evening_fee_tax_incl,
                'signed'                        => (bool) $this->myParcelCarrierDeliverySetting->signed,
                'signedFeeTaxIncl'              => $carrier->is_free ? 0 : (float) $this->myParcelCarrierDeliverySetting->signed_fee_tax_incl,
                'recipientOnly'                 => (bool) $this->myParcelCarrierDeliverySetting->recipient_only,
                'recipientOnlyFeeTaxIncl'       => $this->myParcelCarrierDeliverySetting->recipient_only_fee_tax_incl,
                'signedRecipientOnly'           => (bool) $this->myParcelCarrierDeliverySetting->signed_recipient_only,
                'signedRecipientOnlyFeeTaxIncl' => $carrier->is_free ? 0 : (float) $this->myParcelCarrierDeliverySetting->signed_recipient_only_fee_tax_incl,
                'fontFamily'                    => Configuration::get(MyParcel::CHECKOUT_FONT),
                'checkoutJs'                    => Media::getJSPath(_PS_MODULE_DIR_.'myparcel/views/js/myparcelcheckout/dist/myparcelcheckout.js'),
                'link'                          => $context->link,
                'foreground1color'              => Configuration::get(MyParcel::CHECKOUT_FG_COLOR1),
                'foreground2color'              => Configuration::get(MyParcel::CHECKOUT_FG_COLOR2),
                'background1color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR1),
                'background2color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR2),
                'background3color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR3),
                'highlightcolor'                => Configuration::get(MyParcel::CHECKOUT_HL_COLOR),
                'fontfamily'                    => Configuration::get(MyParcel::CHECKOUT_FONT),
                'deliveryDaysWindow'            => (int) $this->myParcelCarrierDeliverySetting->timeframe_days,
                'dropoffDelay'                  => (int) $this->myParcelCarrierDeliverySetting->dropoff_delay,
                'dropoffDays'                   => implode(';', $this->myParcelCarrierDeliverySetting->getDropoffDays(date('Y-m-d H:i:s'))),
                'cutoffTime'                    => $cutoffTime,
                'myparcel_ajax_checkout_link'   => $this->context->link->getModuleLink('myparcel', 'myparcelcheckout', array('ajax' => true), true),
                'myparcel_deliveryoptions_link' => $this->context->link->getModuleLink('myparcel', 'deliveryoptions', array(), true),
            )
        );

        echo $context->smarty->fetch(_PS_MODULE_DIR_.'myparcel/views/templates/front/myparcelcheckout.tpl');
        die();
    }

    /**
     * Use timeframes
     *
     * @return bool
     *
     * @since 2.0.0
     */
    protected function useTimeframes()
    {
        if (!$this->context->cart->checkQuantities()) {
            return false;
        }

        if (!$this->myParcelCarrierDeliverySetting->delivery) {
            return false;
        }

        return true;
    }

    /**
     * Get delivery options
     * (API Proxy)
     *
     * @return void
     *
     * @since 2.0.0
     */
    protected function getDeliveryOptions()
    {
        if (!Tools::isSubmit('ajax')) {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        $input = file_get_contents('php://input');
        $request = Tools::jsonDecode($input, true);
        if (!$request) {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        $allowedParams = array(
            'cc',
            'postal_code',
            'number',
            'carrier',
            'delivery_time',
            'delivery_date',
            'cutoff_time',
            'dropoff_days',
            'dropoff_delay',
            'deliverydays_window',
            'exclude_delivery_type',
            'monday_delivery',
        );

        $query = array();
        foreach ($allowedParams as &$param) {
            if (!isset($request[$param])) {
                continue;
            }

            $query[$param] = $request[$param];
        }

        $url = self::BASE_URI.'?'.http_build_query($query);
        $attempts = MyParcel::CONNECTION_ATTEMPTS;

        do {
            $response = Tools::file_get_contents($url, false, null, 20);
            $attempts--;
        } while (!$response && $attempts > 0);

        if (!$response) {
            die(Tools::jsonEncode(array(
                'success' => false,
            )));
        }

        header('Content-Type: application/json;charset=utf-8');
        die(Tools::jsonEncode(array(
            'success'  => true,
            'response' => Tools::jsonDecode($response),
        )));
    }

    /**
     * Hide the iframe
     *
     * @return void
     */
    protected function hideMe()
    {
        echo Context::getContext()->smarty->fetch(_PS_MODULE_DIR_.'myparcel/views/templates/front/removeiframe.tpl');
        die();
    }
}

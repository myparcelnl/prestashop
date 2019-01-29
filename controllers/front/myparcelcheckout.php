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

require_once dirname(__FILE__).'/../../myparcel.php';

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
     * @throws PrestaShopException
     * @throws Adapter_Exception
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
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @since 2.0.0
     * @throws SmartyException
     * @throws ErrorException
     */
    public function initContent()
    {
        if (!Configuration::get(MyParcel::API_KEY)) {
            exit;
        }

        if (Tools::isSubmit('ajax')) {
            $this->getDeliveryOptions();

            return;
        }

        $context = Context::getContext();

        /** @var Cart $cart */
        $cart = $context->cart;
        if (!Validate::isLoadedObject($cart)) {
            $this->hideMe();
        }

        $address = new Address((int) $cart->id_address_delivery);

        if (!preg_match(MyParcel::SPLIT_STREET_REGEX, MyParcelTools::getAddressLine($address), $m)) {
            // No house number
            $this->hideMe();
        }

        $streetName = isset($m['street']) ? $m['street'] : '';
        $houseNumber = isset($m['street_suffix']) ? $m['street_suffix'] : '';

        // id_carrier is not defined in database before choosing a carrier,
        // set it to a default one to match a potential cart _rule
        if (empty($cart->id_carrier) ||
            !in_array(
                $cart->id_carrier,
                array_filter(explode(',', Cart::desintifier($cart->simulateCarrierSelectedOutput())))
            )) {
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

        $this->myParcelCarrierDeliverySetting =
            MyParcelCarrierDeliverySetting::getByCarrierReference($carrier->id_reference);
        if (!$this->myParcelCarrierDeliverySetting
            || !Validate::isLoadedObject($this->myParcelCarrierDeliverySetting)
        ) {
            $this->hideMe();
        }

        if (version_compare(_PS_VERSION_, '1.7.0.0', '>')
            && !($this->myParcelCarrierDeliverySetting->delivery || $this->myParcelCarrierDeliverySetting->pickup)
        ) {
            $this->hideMe();
        }

        $cutoffTimes = $this->myParcelCarrierDeliverySetting->getCutOffTimes(
            date('Y-m-d'),
            MyParcelCarrierDeliverySetting::ENUM_DELIVERY
        );
        if (isset($cutoffTimes[0]['time'])) {
            $cutoffTime = $cutoffTimes[0]['time'];
        } else {
            $cutoffTime = MyParcelCarrierDeliverySetting::DEFAULT_CUTOFF;
        }

        $countryIso = Tools::strtolower(Country::getIsoById($address->id_country));
        if (!in_array($countryIso, array('nl', 'be'))) {
            $this->hideMe();
        }

        // Calculate the conversion to make before displaying prices
        // It is comprised of taxes and currency conversions
        /** @var Currency $defaultCurrency */
        $defaultCurrency = Currency::getCurrencyInstance(Configuration::get('PS_CURRENCY_DEFAULT'));
        /** @var Currency $currentCurrency */
        $currentCurrency = $this->context->currency;
        $conversion = $defaultCurrency->conversion_rate * $currentCurrency->conversion_rate;
        // Extra costs are entered with 21% VAT
        $conversion /= 1.21;

        // Calculate tax rate
        $useTax = (Group::getPriceDisplayMethod($this->context->customer->id_default_group) == PS_TAX_INC)
            && Configuration::get('PS_TAX');
        if (Configuration::get('PS_ATCP_SHIPWRAP')) {
            if ($useTax) {
                $conversion *= (1 + $cart->getAverageProductsTaxRate());
            }
        } else {
            if ($useTax && $carrier->getTaxesRate($address)) {
                $conversion *= (1 + ($carrier->getTaxesRate($address) / 100));
            }
        }

        $smartyVars = array(
                'base_dir_ssl'                  => (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                    .Tools::getShopDomainSsl().__PS_BASE_URI__,
                'streetName'                    => $streetName,
                'houseNumber'                   => $houseNumber,
                'postcode'                      => $address->postcode,
                'langIso'                       => Tools::strtolower(Context::getContext()->language->iso_code),
                'language_code'                 => Context::getContext()->language->language_code,
                'currencyIso'                   => Tools::strtolower(Context::getContext()->currency->iso_code),
                'countryIso'                    => $countryIso,
                'express'                       => (bool) $this->myParcelCarrierDeliverySetting->morning_pickup,
                'delivery'                      => (bool) $this->useTimeframes(),
                'pickup'                        => (bool) $this->myParcelCarrierDeliverySetting->pickup,
                'morning'                       => (bool) $this->myParcelCarrierDeliverySetting->morning,
                'morningFeeTaxIncl'             => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->morning_fee_tax_incl * $conversion,
                'morningPickupFeeTaxIncl'       => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->morning_pickup_fee_tax_incl * $conversion,
                'night'                         => (bool) $this->myParcelCarrierDeliverySetting->evening,
                'nightFeeTaxIncl'               => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->evening_fee_tax_incl * $conversion,
                'signed'                        => (bool) $this->myParcelCarrierDeliverySetting->signed,
                'signedFeeTaxIncl'              => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->signed_fee_tax_incl * $conversion,
                'recipientOnly'                 => (bool) $this->myParcelCarrierDeliverySetting->recipient_only,
                'recipientOnlyFeeTaxIncl'       => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->recipient_only_fee_tax_incl * $conversion,
                'signedRecipientOnly'           => (bool) $this->myParcelCarrierDeliverySetting->signed_recipient_only,
                'signedRecipientOnlyFeeTaxIncl' => ($carrier->is_free)
                    ? 0
                    : (float) $this->myParcelCarrierDeliverySetting->signed_recipient_only_fee_tax_incl * $conversion,
                'fontFamily'                    => Configuration::get(MyParcel::CHECKOUT_FONT) ?: 'Exo',
                'fontSize'                      => (int) Configuration::get(MyParcel::CHECKOUT_FONT_SIZE),
                'mypaCheckoutJs'                => Media::getJSPath(_PS_MODULE_DIR_.'myparcel/views/js/dist/front-8d6122b2c2e093e8.bundle.min.js'),
                'link'                          => $context->link,
                'foreground1color'              => Configuration::get(MyParcel::CHECKOUT_FG_COLOR1),
                'foreground2color'              => Configuration::get(MyParcel::CHECKOUT_FG_COLOR2),
                'foreground3color'              => Configuration::get(MyParcel::CHECKOUT_FG_COLOR3),
                'background1color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR1),
                'background2color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR2),
                'background3color'              => Configuration::get(MyParcel::CHECKOUT_BG_COLOR3),
                'highlightcolor'                => Configuration::get(MyParcel::CHECKOUT_HL_COLOR),
                'inactivecolor'                 => Configuration::get(MyParcel::CHECKOUT_INACTIVE_COLOR),
                'fontfamily'                    => Configuration::get(MyParcel::CHECKOUT_FONT),
                'deliveryDaysWindow'            => (int) $this->getActualDeliveryDaysWindow($this->myParcelCarrierDeliverySetting->timeframe_days, $this->myParcelCarrierDeliverySetting->dropoff_delay),
                'dropoffDelay'                  => (int) $this->getActualDropOffDelay($this->myParcelCarrierDeliverySetting->dropoff_delay),
                'dropoffDays'                   => implode(
                    ';',
                    $this->getDropOffDays()
                ),
                'cutoffTime'                    => $cutoffTime,
                'signedPreferred'               =>
                    (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_SIGNED),
                'recipientOnlyPreferred'        =>
                    (bool) Configuration::get(MyParcel::DEFAULT_CONCEPT_HOME_DELIVERY_ONLY),
                'myparcel_ajax_checkout_link'   => $this->context->link->getModuleLink(
                    'myparcel',
                    'myparcelcheckout',
                    array('ajax' => true),
                    Tools::usingSecureMode()
                ),
                'myparcel_deliveryoptions_link' => $this->context->link->getModuleLink(
                    'myparcel',
                    'deliveryoptions',
                    array(),
                    Tools::usingSecureMode()
                ),
                'mpLogApi'                      => (bool) Configuration::get(MyParcel::LOG_API),
        );
        $cacheKey = md5(
            mypa_json_encode($smartyVars)
            .$this->myParcelCarrierDeliverySetting->getCutoffExceptionsHash()
            .$carrier->id
            .date('d-m-Y')
            .$context->cookie->id_guest
        );
        $context->smarty->assign(
            array_merge(
                $smartyVars,
                array('cacheKey' => $cacheKey)
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
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
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
     * @throws ErrorException
     * @throws PrestaShopException
     * @since 2.0.0
     */
    protected function getDeliveryOptions()
    {
        if (!Tools::isSubmit('ajax')) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        $input = file_get_contents('php://input');
        $request = @json_decode($input, true);
        if (!$request) {
            die(mypa_json_encode(array(
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
            if ($param === 'exclude_delivery_type') {
                if (empty($request[$param])) {
                    continue;
                }
            }

            $value = $request[$param];
            if ($param === 'number') {
                $value = (int) preg_replace('/[^\d]*(\d+).*$/', '$1', $value);
            }

            $query[$param] = $value;
        }

        $curl = \MyParcelModule\MyParcelHttpClient::getInstance();
        $url = static::BASE_URI.'?'.http_build_query($query);
        $response = $curl->get($url);
        if (!$response) {
            die(mypa_json_encode(array(
                'success' => false,
            )));
        }

        header('Content-Type: application/json;charset=utf-8');
        die(mypa_json_encode(array(
            'success'  => true,
            'response' => $response,
        )));
    }

    /**
     * Get drop off days
     *
     * @param int $dropOffDelay
     *
     * @return array
     *
     * @since 2.2.0
     */
    public function getDropOffDays($dropOffDelay = 0)
    {
        if ($dropOffDelay > 14) {
            return array();
        }

        $days = $this->myParcelCarrierDeliverySetting->getDropoffDays(date('Y-m-d H:i:s', strtotime("+{$dropOffDelay} days")), 14 - $dropOffDelay);
        if (empty($days)) {
            return array();
        }

        // Check if the first week is available
        $firstWeek = array();
        $secondWeek = array();
        foreach ($days as $fullDate => $day) {
            if ($fullDate < date('Y-m-d', strtotime('+7 days'))) {
                $firstWeek[$fullDate] = $day;
            } else {
                $secondWeek[$fullDate] = $day;
            }
        }
        if (!empty($firstWeek)) {
            return array_unique(array_values($firstWeek));
        }

        return array_unique(array_values($secondWeek));
    }

    /**
     * Get the actual drop off delay
     *
     * @param int $dropOffDelay
     *
     * @return int
     *
     * @since 2.2.0
     */
    public function getActualDropOffDelay($dropOffDelay = 0)
    {
        $days = $this->myParcelCarrierDeliverySetting->getDropoffDays(date('Y-m-d H:i:s', strtotime("+{$dropOffDelay} days")), 14 - $dropOffDelay);
        if (empty($days)) {
            return $dropOffDelay;
        }

        // Check if the first week is available
        $firstWeek = array();
        $secondWeek = array();
        foreach ($days as $fullDate => $day) {
            if ($fullDate < date('Y-m-d', strtotime('+7 days'))) {
                $firstWeek[$fullDate] = $day;
            } else {
                $secondWeek[$fullDate] = $day;
            }
        }

        if (!empty($firstWeek)) {
            return $dropOffDelay;
        }

        return $dropOffDelay + 7;
    }

    /**
     * Get actual delivery days window
     *
     * @param int $deliveryDaysWindow
     * @param int $dropOffDelay
     *
     * @return int
     */
    public function getActualDeliveryDaysWindow($deliveryDaysWindow = 0, $dropOffDelay = 0)
    {
        if ($deliveryDaysWindow === 0) {
            return 0;
        }

        $dropOffDelay = $this->getActualDropOffDelay($dropOffDelay);
        if ($dropOffDelay + $deliveryDaysWindow > 14) {
            return 10;
        }

        return $deliveryDaysWindow;
    }

    /**
     * Hide the iframe
     *
     * @return void
     * @throws SmartyException
     */
    protected function hideMe()
    {
        header('Content-Type: text/html;charset=utf-8');
        echo Context::getContext()->smarty->fetch(_PS_MODULE_DIR_.'myparcel/views/templates/front/removeiframe.tpl');
        exit;
    }
}

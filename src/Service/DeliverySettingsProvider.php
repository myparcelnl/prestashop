<?php

namespace Gett\MyparcelBE\Service;

use Address;
use Cart;
use Configuration;
use Context;
use Country;
use DateTime;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\Module\Configuration\Form\CheckoutForm;
use Module;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use Order;
use Tools;
use Validate;

class DeliverySettingsProvider
{
    /**
     * @var \MyParcelBE
     */
    private $module;

    /**
     * @var int[]
     */
    private $carriers;

    /**
     * @var int
     */
    private $idOrder;

    /**
     * @var Context
     */
    private $context;

    /**
     * @param  \Module       $module
     * @param  array         $carriers
     * @param  \Context|null $context
     */
    public function __construct(Module $module, array $carriers = [], Context $context = null)
    {
        $this->module   = $module;
        $this->carriers = $carriers;
        $this->context  = $context ?? Context::getContext();
    }

    public function setOrderId(int $idOrder): self
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function get(): array
    {
        $this->initCart();

        if (! Validate::isLoadedObject($this->context->cart)) {
            return [];
        }

        $address     = new Address($this->context->cart->id_address_delivery);
        $houseNumber = preg_replace('/[^0-9]/', '', $address->address1 . $address->address2);

        if (Configuration::get(Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME)) {
            $houseNumber = trim($address->address2);
        }

        $surchargeOption    = Configuration::get(Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME);
        $showPriceSurcharge = Constant::DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE === $surchargeOption;

        $carrierSettings = $this->generateCarrierSettings($address, $showPriceSurcharge);

        return [
            'config'            => [
                'platform'           => ($this->module->isBE() ? 'belgie' : 'myparcel'),
                'carrierSettings'    => $carrierSettings,
                'showPriceSurcharge' => $showPriceSurcharge,
            ],
            'strings'           => [
                'addressNotFound'       => Configuration::get(CheckoutForm::CONFIGURATION_ADDRESS_NOT_FOUND),
                'city'                  => Configuration::get(CheckoutForm::CONFIGURATION_CITY),
                'closed'                => Configuration::get(CheckoutForm::CONFIGURATION_CLOSED),
                'deliveryEveningTitle'  => Configuration::get(CheckoutForm::CONFIGURATION_DELIVERY_EVENING_TITLE),
                'deliveryMorningTitle'  => Configuration::get(CheckoutForm::CONFIGURATION_DELIVERY_MORNING_TITLE),
                'deliveryStandardTitle' => Configuration::get(CheckoutForm::CONFIGURATION_DELIVERY_STANDARD_TITLE),
                'deliveryTitle'         => Configuration::get(CheckoutForm::CONFIGURATION_DELIVERY_TITLE),
                'discount'              => Configuration::get(CheckoutForm::CONFIGURATION_DISCOUNT),
                'free'                  => Configuration::get(CheckoutForm::CONFIGURATION_FREE),
                'from'                  => Configuration::get(CheckoutForm::CONFIGURATION_FROM),
                'houseNumber'           => Configuration::get(CheckoutForm::CONFIGURATION_HOUSE_NUMBER),
                'loadMore'              => Configuration::get(CheckoutForm::CONFIGURATION_LOAD_MORE),
                'onlyRecipientTitle'    => Configuration::get(CheckoutForm::CONFIGURATION_ONLY_RECIPIENT_TITLE),
                'openingHours'          => Configuration::get(CheckoutForm::CONFIGURATION_OPENING_HOURS),
                'pickUpFrom'            => Configuration::get(CheckoutForm::CONFIGURATION_PICK_UP_FROM),
                'pickupTitle'           => Configuration::get(CheckoutForm::CONFIGURATION_PICKUP_TITLE),
                'postcode'              => Configuration::get(CheckoutForm::CONFIGURATION_POSTCODE),
                'retry'                 => Configuration::get(CheckoutForm::CONFIGURATION_RETRY),
                'saturdayDeliveryTitle' => Configuration::get(CheckoutForm::CONFIGURATION_SATURDAY_DELIVERY_TITLE),
                'signatureTitle'        => Configuration::get(CheckoutForm::CONFIGURATION_SIGNATURE_TITLE),
                'wrongPostalCodeCity'   => Configuration::get(CheckoutForm::CONFIGURATION_WRONG_POSTAL_CODE_CITY),
            ],
            'address'           => [
                'cc'         => strtoupper(Country::getIsoById($address->id_country)),
                'city'       => $address->city,
                'postalCode' => $address->postcode,
                'number'     => $houseNumber,
            ],
            'delivery_settings' => DeliveryOptions::queryByCart((int) $this->context->cart->id),
        ];
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $carrier
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getDropOffSettings(AbstractCarrier $carrier): array
    {
        $dropOffDelay     = (int) CarrierConfigurationProvider::get($carrier->getId(), 'dropOffDelay', 0);
        $cutoffExceptions = CarrierConfigurationProvider::get(
            $carrier->getId(),
            Constant::CUTOFF_EXCEPTIONS
        );

        $cutoffExceptions = json_decode(
            $cutoffExceptions,
            true
        );

        if (! is_array($cutoffExceptions)) {
            $cutoffExceptions = [];
        }

        $dropOffDateObj  = new DateTime('today');
        $weekDayNumber   = $dropOffDateObj->format('N');
        $dayName         = Constant::WEEK_DAYS[$weekDayNumber];
        $cutoffTimeToday = CarrierConfigurationProvider::get($carrier->getId(), $dayName . 'CutoffTime');
        $dropOffDays     = array_map(
            'intval',
            explode(',', CarrierConfigurationProvider::get($carrier->getId(), 'dropOffDays'))
        );

        $updatedCutoffTime  = $this->updateCutoffTime($cutoffTimeToday, $dropOffDateObj, $cutoffExceptions);
        $updatedDropOffDays = $this->updateDropOffDays($dropOffDays, $dropOffDateObj, $cutoffExceptions);

        // no dropoffdays left for the coming week, just schedule it for next week
        if ($updatedDropOffDays) {
            $dropOffDays = array_values($updatedDropOffDays);
        } else {
            $dropOffDelay += 7;
        }

        return [
            'dropOffDelay' => $dropOffDelay,
            'cutOffTime'   => $updatedCutoffTime,
            'dropOffDays'  => $dropOffDays,
        ];
    }

    /**
     * @param  \Address $address
     * @param  bool     $showPriceSurcharge
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private function generateCarrierSettings(Address $address, bool $showPriceSurcharge): array
    {
        $carrierSettings = [];

        foreach ($this->carriers as $carrierId) {
            if (! CarrierConfigurationProvider::get($carrierId, 'carrierType')) {
                continue;
            }

            $carrier = CarrierService::getMyParcelCarrier($carrierId);

            $shippingOptions       = $this->module->getShippingOptions($carrierId, $address);
            $basePrice             = $this->context->cart->getTotalShippingCost(null, $shippingOptions['include_tax']);
            $priceStandardDelivery = $showPriceSurcharge ? null : Tools::ps_round($basePrice, 2);

            $carrierSettings[$carrier->getName()] = array_merge(
                $this->getCarrierSettings($carrierId, $shippingOptions),
                $this->getDropOffSettings($carrier),
                [
                    'allowDeliveryOptions'  => true,
                    'priceStandardDelivery' => $priceStandardDelivery,
                ]
            );
        }

        return $carrierSettings;
    }

    /**
     * @param  int   $psCarrierId
     * @param  array $shippingOptions
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getCarrierSettings(int $psCarrierId, array $shippingOptions): array
    {
        $deliveryDaysWindow = (int) (CarrierConfigurationProvider::get($psCarrierId, 'deliveryDaysWindow') ?? 1);
        $taxRate            = $shippingOptions['tax_rate'];

        return [
            'allowEveningDelivery'  => $this->isEnabledInCarrier($psCarrierId, 'allowEveningDelivery'),
            'allowMondayDelivery'   => $this->isEnabledInCarrier($psCarrierId, 'allowMondayDelivery'),
            'allowMorningDelivery'  => $this->isEnabledInCarrier($psCarrierId, 'allowMorningDelivery'),
            'allowOnlyRecipient'    => $this->isEnabledInCarrier($psCarrierId, 'allowOnlyRecipient'),
            'allowPickupLocations'  => $this->isEnabledInCarrier($psCarrierId, 'allowPickupPoints'),
            'allowPickupPoints'     => $this->isEnabledInCarrier($psCarrierId, 'allowPickupPoints'),
            'allowSaturdayDelivery' => $this->isEnabledInCarrier($psCarrierId, 'allowSaturdayDelivery'),
            'allowShowDeliveryDate' => -1 !== $deliveryDaysWindow,
            'allowSignature'        => $this->isEnabledInCarrier($psCarrierId, 'allowSignature'),
            'deliveryDaysWindow'    => $deliveryDaysWindow,
            'priceEveningDelivery'  => $this->getPrice($psCarrierId, 'priceEveningDelivery', $taxRate),
            'priceMorningDelivery'  => $this->getPrice($psCarrierId, 'priceMorningDelivery', $taxRate),
            'priceOnlyRecipient'    => $this->getPrice($psCarrierId, 'priceOnlyRecipient', $taxRate),
            'pricePickup'           => $this->getPrice($psCarrierId, 'pricePickup', $taxRate),
            'priceSignature'        => $this->getPrice($psCarrierId, 'priceSignature', $taxRate),
        ];
    }

    /**
     * @param  int    $carrierId
     * @param  string $name
     * @param  float  $taxRate
     *
     * @return float
     * @throws \PrestaShopDatabaseException
     */
    private function getPrice(int $carrierId, string $name, float $taxRate = 1): float
    {
        $price = CarrierConfigurationProvider::get($carrierId, $name) ?: 0;
        return Tools::ps_round($price * $taxRate, 2);
    }

    /**
     * @param  int    $carrierId
     * @param  string $setting
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    private function isEnabledInCarrier(int $carrierId, string $setting): bool
    {
        return (bool) CarrierConfigurationProvider::get($carrierId, $setting);
    }

    /**
     * @param $cutoffTimeToday
     * @param $dropOffDateObj
     * @param $cutoffExceptions
     *
     * @return void
     */
    private function updateCutoffTime($cutoffTime, $dropOffDateObj, $cutoffExceptions)
    {
        if (false !== $cutoffTime && isset($cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['cutoff'])) {
            $cutoffTime = $cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['cutoff'];
        }

        if (empty($cutoffTime)) {
            $cutoffTime = Constant::DEFAULT_CUTOFF_TIME;
        }

        [$hour, $minute] = explode(':', $cutoffTime);
        $dropOffDateObj->setTime((int) $hour, (int) $minute, 0, 0);

        return $cutoffTime;
    }

    /**
     * @param array     $dropOffDays
     * @param \DateTime $dropOffDateObj
     * @param           $cutoffExceptions
     *
     * @return array
     */
    private function updateDropOffDays(array $dropOffDays, DateTime $dropOffDateObj, $cutoffExceptions): array
    {
        // remove days with nodispatch from the dropoffdays the coming week (still better than nothing)
        $aWeekFromNowDateObj = (new DateTime('today'))->modify('+7 day');

        do {
            if (
                ! isset($cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['cutoff'])
                && isset($cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['nodispatch'])
            ) {
                $key = array_search($dropOffDateObj->format('N'), $dropOffDays);
                if (false !== $key) {
                    unset($dropOffDays[$key]);
                }
            }
        } while ($dropOffDateObj->modify('+1 day') < $aWeekFromNowDateObj);

        return $dropOffDays;
    }

    private function initCart(): void
    {
        if ((!isset($this->context->cart) || !$this->context->cart->id) && $this->idOrder) {
            $order = new Order($this->idOrder);
            $cart = new Cart($order->id_cart);
            $this->context->cart = $cart;
        }
    }
}

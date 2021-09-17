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
use Module;
use Order;
use Tools;
use Validate;

class DeliverySettingsProvider
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var int
     */
    private $idCarrier;

    /**
     * @var int
     */
    private $idOrder;

    /**
     * @var Context
     */
    private $context;

    public function __construct(Module $module, int $idCarrier = null, Context $context = null)
    {
        $this->module = $module;
        $this->idCarrier = (int) $idCarrier;
        $this->context = $context ?? Context::getContext();
    }

    public function setOrderId(int $idOrder): self
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    public function get(): array
    {
        $this->initCart();
        if (!Validate::isLoadedObject($this->context->cart)) {
            return [];
        }
        $address = new Address($this->context->cart->id_address_delivery);
        $houseNumber = preg_replace('/[^0-9]/', '', $address->address1);
        if (Configuration::get(Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME)) {
            $houseNumber = trim($address->address2);
        }
        $carrierName = CarrierConfigurationProvider::get($this->idCarrier, 'carrierType');
        $deliveryDaysWindow = (int) (CarrierConfigurationProvider::get($this->idCarrier, 'deliveryDaysWindow') ?? 1);

        $carrierSettings = [
            $carrierName => ['allowDeliveryOptions' => false],
        ];
        $activeCarrierSettings = [
            'allowDeliveryOptions' => true,
            'allowEveningDelivery' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowEveningDelivery'),
            'allowMondayDelivery' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowMondayDelivery'),
            'allowMorningDelivery' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowMorningDelivery'),
            'allowSaturdayDelivery' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowSaturdayDelivery'),
            'allowOnlyRecipient' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowOnlyRecipient'),
            'allowSignature' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowSignature'),
            'allowPickupPoints' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowPickupPoints'),
            'deliveryDaysWindow' => $deliveryDaysWindow,
            'allowShowDeliveryDate' => (-1 !== $deliveryDaysWindow),
            // TODO: remove allowPickupLocations after fixing the allowPickupPoints reference
            'allowPickupLocations' => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowPickupPoints'),
        ];
        $carrierSettings[$carrierName] = array_merge($carrierSettings[$carrierName], $activeCarrierSettings);
        $dropOffDelay = (int) CarrierConfigurationProvider::get($this->idCarrier, 'dropOffDelay', 0);
        $cutoffExceptions = CarrierConfigurationProvider::get($this->idCarrier, Constant::CUTOFF_EXCEPTIONS);
        $cutoffExceptions = @json_decode(
            $cutoffExceptions,
            true
        );
        if (!is_array($cutoffExceptions)) {
            $cutoffExceptions = [];
        }
        $dropOffDateObj = new DateTime('today');
        $weekDayNumber = $dropOffDateObj->format('N');
        $dayName = Constant::WEEK_DAYS[$weekDayNumber];
        $cutoffTimeToday = CarrierConfigurationProvider::get($this->idCarrier, $dayName . 'CutoffTime');
        $dropOffDays = array_map(
            'intval',
            explode(',', CarrierConfigurationProvider::get($this->idCarrier, 'dropOffDays'))
        );

        $this->updateCutoffTime($cutoffTimeToday, $dropOffDateObj, $cutoffExceptions);

        $updatedDropOffDays = $this->updateDropOffDays($dropOffDays, $dropOffDateObj, $cutoffExceptions);

        // no dropoffdays left for the coming week, just schedule it for next week
        if (! $updatedDropOffDays) {
            $dropOffDelay += 7;
        } else {
            $dropOffDays = array_values($updatedDropOffDays);
        }

        $shippingOptions = $this->module->getShippingOptions($this->idCarrier, $address);

        $taxRate               = $shippingOptions['tax_rate'];
        $priceStandardDelivery = $this->context->cart->getTotalShippingCost(null, $shippingOptions['include_tax']);

        $surchargeOption    = Configuration::get(Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME);
        $showPriceSurcharge = Constant::DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE === $surchargeOption;

        return [
            'config' => [
                'platform'              => ($this->module->isBE() ? 'belgie' : 'myparcel'),
                'carrierSettings'       => $carrierSettings,
                'priceMorningDelivery'  => Tools::ps_round(CarrierConfigurationProvider::get($this->idCarrier, 'priceMorningDelivery') * $taxRate, 2),
                'priceStandardDelivery' => Tools::ps_round($priceStandardDelivery, 2),
                'priceEveningDelivery'  => Tools::ps_round(CarrierConfigurationProvider::get($this->idCarrier, 'priceEveningDelivery') * $taxRate, 2),
                'priceSignature'        => Tools::ps_round(CarrierConfigurationProvider::get($this->idCarrier, 'priceSignature') * $taxRate, 2),
                'priceOnlyRecipient'    => Tools::ps_round(CarrierConfigurationProvider::get($this->idCarrier, 'priceOnlyRecipient') * $taxRate, 2),
                'pricePickup'           => Tools::ps_round((CarrierConfigurationProvider::get($this->idCarrier, 'pricePickup') * $taxRate), 2),
                'allowSignature'        => (bool) CarrierConfigurationProvider::get($this->idCarrier, 'allowSignature'),
                'dropOffDays'           => $dropOffDays,
                'showPriceSurcharge'    => $showPriceSurcharge,
                'cutoffTime'            => $cutoffTimeToday,
                'deliveryDaysWindow'    => $deliveryDaysWindow,
                'dropOffDelay'          => $dropOffDelay,
            ],
            'strings' => [
                'wrongPostalCodeCity'   => CarrierConfigurationProvider::get($this->idCarrier, 'wrongPostalCodeCity'),
                'saturdayDeliveryTitle' => CarrierConfigurationProvider::get($this->idCarrier, 'saturdayDeliveryTitle'),

                'city'                  => CarrierConfigurationProvider::get($this->idCarrier, 'city'),
                'postcode'              => CarrierConfigurationProvider::get($this->idCarrier, 'postcode'),
                'houseNumber'           => CarrierConfigurationProvider::get($this->idCarrier, 'houseNumber'),
                'addressNotFound'       => CarrierConfigurationProvider::get($this->idCarrier, 'addressNotFound'),

                'deliveryEveningTitle'  => CarrierConfigurationProvider::get($this->idCarrier, 'deliveryEveningTitle'),
                'deliveryMorningTitle'  => CarrierConfigurationProvider::get($this->idCarrier, 'deliveryMorningTitle'),
                'deliveryStandardTitle' => CarrierConfigurationProvider::get($this->idCarrier, 'deliveryStandardTitle'),

                'deliveryTitle'         => CarrierConfigurationProvider::get($this->idCarrier, 'deliveryTitle'),
                'pickupTitle'           => CarrierConfigurationProvider::get($this->idCarrier, 'pickupTitle'),

                'onlyRecipientTitle'    => CarrierConfigurationProvider::get($this->idCarrier, 'onlyRecipientTitle'),
                'signatureTitle'        => CarrierConfigurationProvider::get($this->idCarrier, 'signatureTitle'),

                'pickUpFrom'            => CarrierConfigurationProvider::get($this->idCarrier, 'pickUpFrom'),
                'openingHours'          => CarrierConfigurationProvider::get($this->idCarrier, 'openingHours'),

                'closed'                => CarrierConfigurationProvider::get($this->idCarrier, 'closed'),
                'discount'              => CarrierConfigurationProvider::get($this->idCarrier, 'discount'),
                'free'                  => CarrierConfigurationProvider::get($this->idCarrier, 'free'),
                'from'                  => CarrierConfigurationProvider::get($this->idCarrier, 'from'),
                'loadMore'              => CarrierConfigurationProvider::get($this->idCarrier, 'loadMore'),
                'retry'                 => CarrierConfigurationProvider::get($this->idCarrier, 'retry'),
            ],
            'address' => [
                'cc' => strtoupper(Country::getIsoById($address->id_country)),
                'city' => $address->city,
                'postalCode' => $address->postcode,
                'number' => $houseNumber,
            ],
            'delivery_settings' => DeliveryOptions::queryByCart((int) $this->context->cart->id),
        ];
    }

    private function updateCutoffTime(&$cutoffTimeToday, $dropOffDateObj, $cutoffExceptions): void
    {
        if (isset($cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['cutoff']) && $cutoffTimeToday !== false) {
            $cutoffTimeToday = $cutoffExceptions[$dropOffDateObj->format('d-m-Y')]['cutoff'];
        }
        if (empty($cutoffTimeToday)) {
            $cutoffTimeToday = Constant::DEFAULT_CUTOFF_TIME;
        }

        [$hour, $minute] = explode(':', $cutoffTimeToday);
        $dropOffDateObj->setTime((int) $hour, (int) $minute, 0, 0);
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

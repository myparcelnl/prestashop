<?php

namespace Gett\MyparcelBE\Service;

use Address;
use Carrier;
use Cart;
use Configuration;
use Context;
use Country;
use DateTime;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Module\Configuration\Form\CheckoutForm;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use Order;
use Tools;
use Validate;

class DeliverySettingsProvider extends AbstractProvider
{
    /**
     * @var int[]
     */
    private $carriers;

    /**
     * @var int
     */
    private $idOrder;

    /**
     * @param  \Context|null $context
     * @param  array         $carriers
     *
     * @throws \Exception
     */
    public function __construct(Context $context = null, array $carriers = [])
    {
        parent::__construct($context);
        $this->carriers  = empty($carriers) ? $this->getPlatformCarrierNames() : $carriers;
    }

    /**
     * @throws \PrestaShopDatabaseException
     */
    public function get(int $addressId = null): array
    {
        $address = new Address($addressId ?? $this->getAddressFromCart());

        if (! Validate::isLoadedObject($address)) {
            return [];
        }

        $houseNumber = preg_replace('/\D/', '', $address->address1 . $address->address2);

        if (Configuration::get(Constant::USE_ADDRESS2_AS_STREET_NUMBER_CONFIGURATION_NAME)) {
            $houseNumber = trim($address->address2);
        }

        $surchargeOption    = Configuration::get(Constant::DELIVERY_OPTIONS_PRICE_FORMAT_CONFIGURATION_NAME);
        $showPriceSurcharge = Constant::DELIVERY_OPTIONS_PRICE_FORMAT_SURCHARGE === $surchargeOption;

        return [
            'config'  => [
                'platform'           => ($this->module->isBE() ? 'belgie' : 'myparcel'),
                'carrierSettings'    => $this->generateCarrierSettings($address, $showPriceSurcharge),
                'showPriceSurcharge' => $showPriceSurcharge,
            ],
            'strings' => $this->getDeliveryOptionsStrings(),
            'address' => [
                'cc'         => strtoupper(Country::getIsoById($address->id_country)),
                'street'     => $address->address1,
                'city'       => $address->city,
                'postalCode' => $address->postcode,
                'number'     => $houseNumber,
            ],
        ];
    }

    /**
     * @param  int $idOrder
     *
     * @return $this
     */
    public function setOrderId(int $idOrder): self
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    /**
     * @return null|\Address
     */
    protected function getAddressFromCart(): ?int
    {
        $this->initCart();

        if (! Validate::isLoadedObject($this->context->cart)) {
            return null;
        }

        return $this->context->cart->id_address_delivery;
    }

    /**
     * @return array
     */
    protected function getDeliveryOptionsStrings(): array
    {
        $getConfigurationString = static function (string $settings) {
            return Configuration::get($settings) ?: null;
        };

        return [
            'addressNotFound'       => $getConfigurationString(CheckoutForm::CONFIGURATION_ADDRESS_NOT_FOUND),
            'city'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_CITY),
            'closed'                => $getConfigurationString(CheckoutForm::CONFIGURATION_CLOSED),
            'deliveryEveningTitle'  => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_EVENING_TITLE),
            'deliveryMorningTitle'  => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_MORNING_TITLE),
            'deliveryStandardTitle' => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_STANDARD_TITLE),
            'deliveryTitle'         => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_TITLE),
            'discount'              => $getConfigurationString(CheckoutForm::CONFIGURATION_DISCOUNT),
            'free'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_FREE),
            'from'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_FROM),
            'houseNumber'           => $getConfigurationString(CheckoutForm::CONFIGURATION_HOUSE_NUMBER),
            'loadMore'              => $getConfigurationString(CheckoutForm::CONFIGURATION_LOAD_MORE),
            'onlyRecipientTitle'    => $getConfigurationString(CheckoutForm::CONFIGURATION_ONLY_RECIPIENT_TITLE),
            'openingHours'          => $getConfigurationString(CheckoutForm::CONFIGURATION_OPENING_HOURS),
            'pickUpFrom'            => $getConfigurationString(CheckoutForm::CONFIGURATION_PICK_UP_FROM),
            'pickupTitle'           => $getConfigurationString(CheckoutForm::CONFIGURATION_PICKUP_TITLE),
            'postcode'              => $getConfigurationString(CheckoutForm::CONFIGURATION_POSTCODE),
            'retry'                 => $getConfigurationString(CheckoutForm::CONFIGURATION_RETRY),
            'saturdayDeliveryTitle' => $getConfigurationString(CheckoutForm::CONFIGURATION_SATURDAY_DELIVERY_TITLE),
            'signatureTitle'        => $getConfigurationString(CheckoutForm::CONFIGURATION_SIGNATURE_TITLE),
            'wrongPostalCodeCity'   => $getConfigurationString(CheckoutForm::CONFIGURATION_WRONG_POSTAL_CODE_CITY),
        ];
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getPlatformCarrierNames(): array
    {
        $platformService = PlatformServiceFactory::create();

        return array_map(
            static function (string $carrierClass) {
                return $carrierClass::NAME;
            },
            $platformService->getCarriers()
        );
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

        foreach ($this->carriers as $carrierName) {
            $psCarrier             = $this->getPsCarrier($carrierName);
            $shippingOptions       = $this->module->getShippingOptions($psCarrier->id, $address);
            $basePrice             = $this->context->cart->getTotalShippingCost(null, $shippingOptions['include_tax']);
            $priceStandardDelivery = $showPriceSurcharge ? 0 : Tools::ps_round($basePrice, 2);

            $carrierSettings[$carrierName] = array_merge(
                $this->getCarrierSettings($psCarrier->id, $shippingOptions, $priceStandardDelivery),
                $this->getDropOffSettings($psCarrier->id),
                [
                    'allowDeliveryOptions'  => true,
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
    private function getCarrierSettings(int $psCarrierId, array $shippingOptions, float $basePrice): array
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
            'deliveryDaysWindow'    => abs($deliveryDaysWindow),
            'priceStandardDelivery' => $basePrice,
            'priceEveningDelivery'  => $basePrice + $this->getPrice($psCarrierId, 'priceEveningDelivery', $taxRate),
            'priceMorningDelivery'  => $basePrice + $this->getPrice($psCarrierId, 'priceMorningDelivery', $taxRate),
            'priceOnlyRecipient'    => $basePrice + $this->getPrice($psCarrierId, 'priceOnlyRecipient', $taxRate),
            'pricePickup'           => $basePrice + $this->getPrice($psCarrierId, 'pricePickup', $taxRate),
            'priceSignature'        => $basePrice + $this->getPrice($psCarrierId, 'priceSignature', $taxRate),
        ];
    }

    /**
     * @param  int $psCarrierId
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     */
    private function getDropOffSettings(int $psCarrierId): array
    {
        $dropOffDelay     = (int) CarrierConfigurationProvider::get($psCarrierId, 'dropOffDelay', 0);
        $cutoffExceptions = CarrierConfigurationProvider::get($psCarrierId, Constant::CUTOFF_EXCEPTIONS);
        $cutoffExceptions = json_decode($cutoffExceptions, true);

        if (! is_array($cutoffExceptions)) {
            $cutoffExceptions = [];
        }

        $dropOffDateObj  = new DateTime('today');
        $weekDayNumber   = $dropOffDateObj->format('N');
        $dayName         = Constant::WEEK_DAYS[$weekDayNumber];
        $cutoffTimeToday = CarrierConfigurationProvider::get($psCarrierId, $dayName . 'CutoffTime');
        $dropOffDays     = array_map(
            'intval',
            explode(',', CarrierConfigurationProvider::get($psCarrierId, 'dropOffDays'))
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
            'cutoffTime'   => $updatedCutoffTime,
            'dropOffDays'  => $dropOffDays,
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
     * @param  string $carrierName
     *
     * @return \Carrier
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private function getPsCarrier(string $carrierName): Carrier
    {
        $myParcelCarrier = CarrierFactory::createFromName($carrierName);
        return CarrierService::getPrestashopCarrier($myParcelCarrier);
    }

    private function initCart(): void
    {
        if ((! isset($this->context->cart) || ! $this->context->cart->id) && $this->idOrder) {
            $order               = new Order($this->idOrder);
            $cart                = new Cart($order->id_cart);
            $this->context->cart = $cart;
        }
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
     * @param  array     $dropOffDays
     * @param  \DateTime $dropOffDateObj
     * @param            $cutoffExceptions
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
}

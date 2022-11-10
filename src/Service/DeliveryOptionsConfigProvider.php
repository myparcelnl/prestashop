<?php

namespace MyParcelNL\PrestaShop\Service;

use Address;
use Carrier;
use Cart;
use Configuration;
use Context;
use Country;
use DateTime;
use MyParcelNL\PrestaShop\Carrier\PackageTypeCalculator;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Logger\DeprecatedFileLogger;
use MyParcelNL\PrestaShop\Module\Configuration\Form\CheckoutForm;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Pdk\Facade\OrderLogger;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use Order;
use RuntimeException;
use Tools;
use Validate;

class DeliveryOptionsConfigProvider extends AbstractProvider
{
    /**
     * @var int
     */
    private $idOrder;

    /**
     * @var string
     */
    private $psCarrierId;

    /**
     * @param  \Context|null $context
     * @param  string|null   $psCarrierId
     *
     * @throws \Exception
     */
    public function __construct(Context $context = null, string $psCarrierId = null)
    {
        parent::__construct($context);
        $this->psCarrierId = $psCarrierId;
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
                'platform'           => (ModuleService::isBE() ? 'belgie' : 'myparcel'),
                'packageType'        => $this->calculatePackageType(),
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
            'addressNotFound'           => $getConfigurationString(CheckoutForm::CONFIGURATION_ADDRESS_NOT_FOUND),
            'cc'                        => $getConfigurationString(CheckoutForm::CONFIGURATION_CC),
            'city'                      => $getConfigurationString(CheckoutForm::CONFIGURATION_CITY),
            'closed'                    => $getConfigurationString(CheckoutForm::CONFIGURATION_CLOSED),
            'deliveryEveningTitle'      => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_EVENING_TITLE),
            'deliveryMorningTitle'      => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_MORNING_TITLE),
            'deliveryStandardTitle'     => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_STANDARD_TITLE),
            'deliveryTitle'             => $getConfigurationString(CheckoutForm::CONFIGURATION_DELIVERY_TITLE),
            'discount'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_DISCOUNT),
            'free'                      => $getConfigurationString(CheckoutForm::CONFIGURATION_FREE),
            'from'                      => $getConfigurationString(CheckoutForm::CONFIGURATION_FROM),
            'houseNumber'               => $getConfigurationString(CheckoutForm::CONFIGURATION_HOUSE_NUMBER),
            'loadMore'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_LOAD_MORE),
            'onlyRecipientTitle'        => $getConfigurationString(CheckoutForm::CONFIGURATION_ONLY_RECIPIENT_TITLE),
            'openingHours'              => $getConfigurationString(CheckoutForm::CONFIGURATION_OPENING_HOURS),
            'pickUpFrom'                => $getConfigurationString(CheckoutForm::CONFIGURATION_PICK_UP_FROM),
            'pickupLocationsListButton' => $getConfigurationString(CheckoutForm::CONFIGURATION_PICKUP_LIST_TITLE),
            'pickupLocationsMapButton'  => $getConfigurationString(CheckoutForm::CONFIGURATION_PICKUP_MAP_TITLE),
            'pickupTitle'               => $getConfigurationString(CheckoutForm::CONFIGURATION_PICKUP_TITLE),
            'postcode'                  => $getConfigurationString(CheckoutForm::CONFIGURATION_POSTCODE),
            'retry'                     => $getConfigurationString(CheckoutForm::CONFIGURATION_RETRY),
            'saturdayDeliveryTitle'     => $getConfigurationString(CheckoutForm::CONFIGURATION_SATURDAY_DELIVERY_TITLE),
            'signatureTitle'            => $getConfigurationString(CheckoutForm::CONFIGURATION_SIGNATURE_TITLE),
            'wrongNumberPostalCode'     => $getConfigurationString(
                CheckoutForm::CONFIGURATION_WRONG_NUMBER_POSTAL_CODE
            ),
            'wrongPostalCodeCity'       => $getConfigurationString(CheckoutForm::CONFIGURATION_WRONG_POSTAL_CODE_CITY),
        ];
    }

    /**
     * @return string
     * @throws \PrestaShopDatabaseException
     */
    private function calculatePackageType(): string
    {
        $packageTypes = (new PackageTypeCalculator())->getProductsPackageTypes($this->context->cart);
        $packageType  = AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME;

        if (1 === count($packageTypes) && $packageTypes[0] !== AbstractConsignment::PACKAGE_TYPE_LETTER) {
            $packageType = array_flip(AbstractConsignment::PACKAGE_TYPES_NAMES_IDS_MAP)[$packageTypes[0]];
        }

        return $packageType;
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
        $psCarrier       = new Carrier($this->psCarrierId);

        if (! $psCarrier->id) {
            OrderLogger::error('PsCarrier not found.', ['psCarrierId' => $this->psCarrierId]);
            throw new RuntimeException("PsCarrier $this->psCarrierId not found.");
        }

        $carrierName           = CarrierService::getMyParcelCarrier((int) $psCarrier->id)
            ->getName();
        $shippingOptions       = $this->module->getShippingOptions($psCarrier->id, $address);
        $basePrice             = $this->context->cart->getTotalShippingCost(null, $shippingOptions['include_tax']);
        $priceStandardDelivery = $showPriceSurcharge ? 0 : Tools::ps_round($basePrice, 2);

        $carrierSettings[$carrierName] = array_merge(
            $this->getCarrierSettings($psCarrier->id, $shippingOptions), $this->getDropOffSettings($psCarrier->id),
            [
                'allowDeliveryOptions'  => true,
                'priceStandardDelivery' => $priceStandardDelivery,
            ]
        );

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
            'deliveryDaysWindow'    => abs($deliveryDaysWindow),
            'priceEveningDelivery'  => $this->getPrice($psCarrierId, 'priceEveningDelivery', $taxRate),
            'priceMorningDelivery'  => $this->getPrice($psCarrierId, 'priceMorningDelivery', $taxRate),
            'priceOnlyRecipient'    => $this->getPrice($psCarrierId, 'priceOnlyRecipient', $taxRate),
            'pricePickup'           => $this->getPrice($psCarrierId, 'pricePickup', $taxRate),
            'priceSignature'        => $this->getPrice($psCarrierId, 'priceSignature', $taxRate),
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

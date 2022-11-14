<?php

namespace Gett\MyparcelBE\Factory\Consignment;

use BadMethodCallException;
use Configuration;
use DateTime;
use Exception;
use Gett\MyparcelBE\Adapter\DeliveryOptionsFromOrderAdapter;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliverySettings\ExtraOptions;
use Gett\MyparcelBE\Logger\OrderLogger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\Consignment\ConsignmentNormalizer;
use Gett\MyparcelBE\Service\CountryService;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use Gett\MyparcelBE\Service\WeightService;
use MyParcelBE;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractShipmentOptionsAdapter;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory as SdkConsignmentFactory;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use MyParcelNL\Pdk\Shipment\Service\DeliveryDateService;
use OrderLabel;
use Tools;

class ConsignmentFactory
{
    private const MAX_COLLO_WEIGHT_GRAMS = 30000;
    private const FORMAT_TIMESTAMP       = 'Y-m-d H:i:s';

    /**
     * @var array|array[]
     */
    private $carrierSettings;

    /**
     * @var AbstractDeliveryOptionsAdapter
     */
    private $deliveryOptions;

    /**
     * @var \Gett\MyparcelBE\Model\Core\Order
     */
    private $orderObject;

    /**
     * @var array
     */
    private $request;

    /**
     * @var \MyParcelBE
     */
    private $module;

    /**
     * @var AbstractConsignment
     */
    private $consignment;

    /**
     * @var array
     */
    private $orderData;

    /**
     * @param  array $request
     *
     * @throws \Exception
     */
    public function __construct(array $request)
    {
        $this->request = $request;
        $this->module  = MyParcelBE::getModule();
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function fromOrder(
        Order                          $order,
        AbstractDeliveryOptionsAdapter $deliveryOptions = null
    ): ConsignmentCollection {
        $this->setOrderData($order, $deliveryOptions);
        $this->createConsignment();

        $collection   = new ConsignmentCollection();
        $extraOptions = new ExtraOptions();
        $consignment  = $this->initConsignment();
        $labelAmount  = $this->request['extraOptions']['labelAmount'] ?? 1;
        $extraOptions->setLabelAmount($labelAmount);
        $distributedWeight = $consignment->getTotalWeight() / $labelAmount;
        $consignment->setTotalWeight($distributedWeight);

        if (isset($this->request[Constant::LABEL_CHECK_ONLY]) && $this->request[Constant::LABEL_CHECK_ONLY]) {
            $consignment->setTotalWeight(1);
        }

        if ($extraOptions->getLabelAmount() > 1) {
            $countryService = new CountryService();
            $orderIso       = $countryService->getShippingCountryIso2($order);
            $carrierName    = CarrierService::getMyParcelCarrier($order->getIdCarrier())->getName();

            if (
                CarrierPostNL::NAME === $carrierName
                && AbstractConsignment::CC_NL === $orderIso
                && MyParcelBE::getModule()->isNL()
                && AbstractConsignment::PACKAGE_TYPE_PACKAGE === $consignment->getPackageType()
            ) {
                $collection->addMultiCollo($consignment, $labelAmount);
            } else {
                for ($i = 0; $i < $extraOptions->getLabelAmount(); $i++) {
                    $collection->addConsignment($consignment);
                }
            }

            return $collection;
        }

        $collection->addConsignment($consignment);
        return $collection;
    }

    /**
     * @return null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractShipmentOptionsAdapter
     */
    private function getShipmentOptions(): ?AbstractShipmentOptionsAdapter
    {
        return $this->deliveryOptions->getShipmentOptions();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function setDeliveryOptions(): void
    {
        $deliveryOptionsData = json_decode($this->orderData['delivery_settings'] ?? '', true);

        try {
            // Create new instance from known json
            $this->deliveryOptions = DeliveryOptionsAdapterFactory::create((array) $deliveryOptionsData);
        } catch (BadMethodCallException $e) {
            OrderLogger::addLog(['message' => $e, 'order' => $this->orderObject->getId()], OrderLogger::INFO);

            // Create new instance from unknown json data
            $deliveryOptions       = (new ConsignmentNormalizer((array) $deliveryOptionsData))->normalize();
            $this->deliveryOptions = new DeliveryOptionsFromOrderAdapter($deliveryOptions);
        }
    }

    /**
     * @throws \Exception
     */
    private function setBaseData(): void
    {
        $floatWeight = $this->orderObject->getTotalWeight();
        $this->consignment
            ->setApiKey(Configuration::get(Constant::API_KEY_CONFIGURATION_NAME))
            ->setReferenceIdentifier((string) $this->orderObject->getId())
            ->setPackageType($this->getPackageType())
            ->setDeliveryDate($this->getDeliveryDate())
            ->setDeliveryType($this->getDeliveryType())
            ->setLabelDescription($this->getFormattedLabelDescription())
            ->setTotalWeight(WeightService::convertToGrams($floatWeight));
    }

    /**
     * @return int
     */
    private function getPackageType(): int
    {
        return (new PackageTypeCalculator())->convertToId($this->deliveryOptions->getPackageType());
    }

    /**
     * @return string|null
     */
    private function getDeliveryDate(): ?string
    {
        $date = $this->deliveryOptions->getDate();

        if (! $date) {
            return null;
        }

        return (DeliveryDateService::fixPastDeliveryDate($date))->format(self::FORMAT_TIMESTAMP);
    }

    /**
     * @return int
     */
    private function getDeliveryType(): int
    {
        $deliveryType = $this->deliveryOptions->getDeliveryTypeId() ?? $this->consignment->getDeliveryType();

        if ($this->module->isBE()) {
            return $deliveryType < AbstractConsignment::DELIVERY_TYPE_PICKUP
                ? AbstractConsignment::DELIVERY_TYPE_STANDARD
                : AbstractConsignment::DELIVERY_TYPE_PICKUP;
        }

        return $deliveryType;
    }

    /**
     * Get the label description from the Order and check the maximum number of characters.
     *
     * @return string
     */
    private function getFormattedLabelDescription(): string
    {
        $labelDescription = $this->getLabelDescription();

        if (strlen($labelDescription) > Constant::ORDER_DESCRIPTION_MAX_LENGTH) {
            return substr($labelDescription, 0, Constant::ORDER_DESCRIPTION_MAX_LENGTH - 3) . '...';
        }

        return $labelDescription;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    private function setOrderData(Order $order, ?AbstractDeliveryOptionsAdapter $deliveryOptions): void
    {
        $this->orderObject = $order;
        $requiredKeys      = $this->request[Constant::LABEL_CHECK_ONLY] ?? false ? [] : Constant::REQUIRED_LABEL_KEYS;
        $this->orderData   = OrderLabel::getDataForLabelsCreate($order->getId(), $requiredKeys);

        $carrierSettingsProvider = new CarrierSettingsProvider();
        $this->carrierSettings   = $carrierSettingsProvider->provide($order->getIdCarrier());

        if ($deliveryOptions) {
            $this->deliveryOptions = $deliveryOptions;
        } else {
            $this->setDeliveryOptions();
        }
    }

    /**
     * Gets the recipient and puts its data in the consignment.
     *
     * @throws Exception
     */
    private function setRecipient(): void
    {
        $this->consignment
            ->setCountry(strtoupper($this->orderData['iso_code']))
            ->setPerson($this->orderData['person'])
            ->setCompany($this->orderData['company'])
            ->setFullStreet($this->orderData['full_street'])
            ->setPostalCode($this->orderData['postcode'])
            ->setCity($this->orderData['city'])
            ->setRegion($this->orderData['state_name'])
            ->setEmail($this->getEmailConfiguration())
            ->setPhone($this->getPhoneConfiguration())
            ->setSaveRecipientAddress(false);
    }

    /**
     * @return string
     */
    private function getEmailConfiguration(): string
    {
        if ($this->module->isBE()) {
            return $this->orderData['email'];
        }

        $emailConfiguration = Configuration::get(Constant::SHARE_CUSTOMER_EMAIL_CONFIGURATION_NAME);

        return $emailConfiguration ? $this->orderData['email'] : '';
    }

    /**
     * @return bool
     */
    public static function isConceptFirstConfiguration(): bool
    {
        return Configuration::get(Constant::CONCEPT_FIRST);
    }

    /**
     * @return string
     */
    private function getPhoneConfiguration(): string
    {
        $phoneConfiguration = Configuration::get(Constant::SHARE_CUSTOMER_PHONE_CONFIGURATION_NAME);

        return $phoneConfiguration ? $this->orderData['phone'] : '';
    }

    /**
     * Set the shipment options.
     *
     * @throws Exception
     */
    private function setShipmentOptions(): void
    {
        $shipmentOptions = $this->getShipmentOptions();

        $this->consignment
            ->setOnlyRecipient($shipmentOptions && true === $shipmentOptions->hasOnlyRecipient())
            ->setLargeFormat($shipmentOptions && $shipmentOptions->hasLargeFormat())
            ->setReturn($shipmentOptions && $shipmentOptions->isReturn())
            ->setSignature($this->hasSignature())
            ->setInsurance($shipmentOptions ? $shipmentOptions->getInsurance() : null)
            ->setAgeCheck($shipmentOptions && true === $shipmentOptions->hasAgeCheck())
            ->setContents(AbstractConsignment::PACKAGE_CONTENTS_COMMERCIAL_GOODS)
            ->setInvoice($this->orderData['invoice_number']);
    }

    /**
     * @return bool
     */
    private function hasSignature(): bool
    {
        $canHaveSignature = $this->consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_SIGNATURE);
        $isHomeCountry    = $this->consignment->getCountry() === $this->module->getModuleCountry();
        $shipmentOptions  = $this->deliveryOptions->getShipmentOptions();

        $hasSignature = $shipmentOptions
            && $canHaveSignature
            && $isHomeCountry
            && $shipmentOptions->hasSignature();

        return $this->consignment->getDeliveryType() === AbstractConsignment::DELIVERY_TYPE_PICKUP || $hasSignature;
    }

    /**
     * Set the pickup location
     */
    private function setPickupLocation(): void
    {
        $pickupLocation = $this->deliveryOptions->getPickupLocation();

        if (! $pickupLocation || AbstractConsignment::DELIVERY_TYPE_PICKUP !== $this->getDeliveryType()) {
            return;
        }

        $this->consignment->setPickupCountry($pickupLocation->getCountry())
            ->setPickupCity($pickupLocation->getCity())
            ->setPickupLocationName($pickupLocation->getLocationName())
            ->setPickupStreet($pickupLocation->getStreet())
            ->setPickupNumber($pickupLocation->getNumber())
            ->setPickupPostalCode($pickupLocation->getPostalCode())
            ->setRetailNetworkId($pickupLocation->getRetailNetworkId())
            ->setPickupLocationCode($pickupLocation->getLocationCode());
    }

    /**
     * Sets a customs declaration for the consignment if necessary.
     *
     * @throws \Exception
     */
    private function setCustomsDeclaration(): void
    {
        $isToRowCountry          = $this->consignment->isCdCountry();
        $customFormConfiguration = Configuration::get(Constant::CUSTOMS_FORM_CONFIGURATION_NAME);

        if (! $isToRowCountry || 'No' === $customFormConfiguration) {
            return;
        }

        $this->setCustomItems();
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    private function setCustomItems(): void
    {
        $products = OrderLabel::getCustomsOrderProducts($this->orderObject->getId());

        foreach ($products as $product) {
            if (! $product) {
                continue;
            }

            $weight      = WeightService::convertToGrams($product['product_weight']);
            $description = $product['product_name'];
            $itemValue   = Tools::ps_round($product['unit_price_tax_incl'] * 100);

            $this->consignment->addItem(
                (new MyParcelCustomsItem())
                    ->setDescription($description)
                    ->setAmount($product['product_quantity'])
                    ->setWeight($weight)
                    ->setItemValue($itemValue)
                    ->setCountry($this->getCountryOfOrigin($product['product_id']))
                    ->setClassification($this->getHsCode($product['product_id']))
            );
        }
    }

    /**
     * @param  int $productId
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     */
    private function getCountryOfOrigin(int $productId): string
    {
        $productCountryOfOrigin = ProductConfigurationProvider::get($productId, Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME);
        $defaultCountryOfOrigin = Configuration::get(Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME);

        return $productCountryOfOrigin ?? $defaultCountryOfOrigin;
    }

    /**
     * @param  int $productId
     *
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getHsCode(int $productId): int
    {
        $productHsCode = ProductConfigurationProvider::get($productId, Constant::CUSTOMS_CODE_CONFIGURATION_NAME);
        $defaultHsCode = Configuration::get(Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME);

        return (int) ($productHsCode ?: $defaultHsCode);
    }

    /**
     * Create a new consignment
     *
     * @return void
     * @throws \Exception
     */
    private function createConsignment(): void
    {
        $carrier           = CarrierService::getMyParcelCarrier($this->orderObject->getIdCarrier());
        $this->consignment = SdkConsignmentFactory::createByCarrierId($carrier->getId());
    }

    /**
     * @return AbstractConsignment
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    private function initConsignment(): AbstractConsignment
    {
        $this->setBaseData();
        $this->setRecipient();
        $this->setShipmentOptions();
        $this->setPickupLocation();
        $this->setCustomsDeclaration();
        $this->setTotalWeight();

        return $this->consignment;
    }

    /**
     * @return string
     */
    private function getLabelDescription(): string
    {
        $labelDescription = Configuration::get(Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME);

        if (empty(trim($labelDescription))) {
            return (string) $this->orderObject->getId();
        }

        preg_match_all('/\{\w+\.\w+}/m', $labelDescription, $matches, PREG_SET_ORDER);

        $keys = [];

        if (! empty($matches)) {
            foreach ($matches as $result) {
                foreach ($result as $value) {
                    $key = trim($value, '{}');
                    $key = explode('.', $key);

                    if (count($key) === 1) {
                        $keys[$value] = $key;
                        continue;
                    }

                    if ((count($key) === 2) && $key[0] === 'order') {
                        $keys[$value] = $key[1];
                    }
                }
            }
        }

        if (empty($keys)) {
            return (string) $this->orderObject->getId();
        }

        foreach ($keys as $index => $key) {
            if (! isset($this->orderData[$key])) {
                unset($keys[$index]);
            }

            $labelDescription = str_replace($index, $this->orderData[$key] ?? '', $labelDescription);
        }

        return trim($labelDescription);
    }

    /**
     * @return void
     */
    private function setTotalWeight(): void
    {
        if (
            isset($this->request['extraOptions']['digitalStampWeight'])
            && AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP === $this->consignment->getPackageType()
        ) {
            $this->consignment->setTotalWeight($this->request['extraOptions']['digitalStampWeight']);
        }
    }
}

<?php

namespace Gett\MyparcelBE\Factory\Consignment;

use Configuration;
use DateInterval;
use DateTime;
use Exception;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use MyParcelBE;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractShipmentOptionsAdapter;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use OrderLabel;
use Tools;

class ConsignmentFactory
{
    private const FORMAT_TIMESTAMP = 'Y-m-d H:i:s';

    /**
     * @var string
     */
    private $api_key;

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
     * @param  string      $apiKey
     * @param  array       $request
     * @param  \MyParcelBE $module
     */
    public function __construct(string $apiKey, array $request, MyParcelBE $module)
    {
        $this->api_key = $apiKey;
        $this->module  = $module;
        $this->request = $request;
    }

    /**
     * @param  array $orders
     *
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function fromOrders(array $orders): ConsignmentCollection
    {
        $collection = new ConsignmentCollection();

        foreach ($orders as $order) {
            $this->setOrderData($order);
            $this->createConsignment();
            $collection->addConsignment($this->initConsignment());
        }

        return $collection;
    }

    /**
     * @param  array                                                                           $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function fromOrder(array $order, AbstractDeliveryOptionsAdapter $deliveryOptions = null): ConsignmentCollection
    {
        $this->setOrderData($order, $deliveryOptions);
        $this->createConsignment();

        $collection = new ConsignmentCollection();

        for ($i = 0; $i < $this->request['labelAmount']; ++$i) {
            $consignment = $this->initConsignment();
            foreach (Constant::SINGLE_LABEL_CREATION_OPTIONS as $key => $option) {
                if (isset($this->request[$key]) && method_exists($this, $option)) {
                    $consignment = $this->{$option}($consignment);
                }
            }

            $collection->addConsignment($consignment);
        }

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
     * @param  array                                                                           $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $deliveryOptions
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    private function setOrderData(array $order, AbstractDeliveryOptionsAdapter $deliveryOptions = null): void
    {
        $carrierSettingsProvider = new CarrierSettingsProvider($this->module);

        $this->orderData       = $order;
        $this->orderObject     = new Order((int) $order['id_order']);
        $this->carrierSettings = $carrierSettingsProvider->provide($order['id_carrier']);

        if ($deliveryOptions) {
            $this->deliveryOptions = $deliveryOptions;
        } else {
            $this->deliveryOptions = DeliveryOptions::updateDeliveryOptions($this->orderObject);
        }
    }

    /**
     * @throws \Exception
     */
    private function setBaseData(): void
    {
        $floatWeight = $this->orderObject->getTotalWeight();
        $this->consignment
            ->setApiKey($this->api_key)
            ->setReferenceIdentifier($this->orderData['id_order'])
            ->setPackageType($this->getPackageType())
            ->setDeliveryDate($this->getDeliveryDate())
            ->setDeliveryType($this->getDeliveryType())
            ->setLabelDescription($this->getFormattedLabelDescription())
            ->setTotalWeight((new OrderTotalWeight())->convertWeightToGrams($floatWeight));
    }

    /**
     * @return int
     * @throws \PrestaShopDatabaseException
     */
    private function getPackageType(): int
    {
        if ($this->module->isBE()) {
            return AbstractConsignment::PACKAGE_TYPE_PACKAGE;
        }

        $packageType = $this->request['packageType'] ?? (new PackageTypeCalculator())->getOrderPackageType($this->orderObject);

        if (! isset($this->carrierSettings['delivery']['packageType'][(int) $packageType])) {
            $packageType = AbstractConsignment::PACKAGE_TYPE_PACKAGE; // TODO: for NL the DPD and Bpost don't allow any.
        }

        return (int) $packageType;
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

        return $this->fixPastDeliveryDate($date);
    }

    /**
     * @return int
     */
    private function getDeliveryType(): int
    {
        if ($this->module->isBE()) {
            return $this->consignment->getDeliveryType() < AbstractConsignment::DELIVERY_TYPE_PICKUP
                ? AbstractConsignment::DELIVERY_TYPE_STANDARD
                : AbstractConsignment::DELIVERY_TYPE_PICKUP;
        }

        return $this->deliveryOptions->getDeliveryTypeId() ?? AbstractConsignment::DELIVERY_TYPE_STANDARD;
    }

    /**
     * Get the label description from the Order and check the maximum number of characters.
     *
     * @return string
     */
    private function getFormattedLabelDescription(): string
    {
        $labelDescription = $this->getLabelParams($this->orderData, Configuration::get(Constant::LABEL_DESCRIPTION_CONFIGURATION_NAME));

        if (strlen($labelDescription) > Constant::ORDER_DESCRIPTION_MAX_LENGTH) {
            return substr($labelDescription, 0, 42) . '...';
        }

        return $labelDescription;
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

        if (! $pickupLocation
            || ! $this->deliveryOptions->isPickup()
            || $this->consignment->getDeliveryType() !== AbstractConsignment::DELIVERY_TYPE_PICKUP) {
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
        $isCdCountry             = $this->consignment->isCdCountry();
        $customFormConfiguration = Configuration::get(Constant::CUSTOMS_FORM_CONFIGURATION_NAME);

        if ($isCdCountry && 'No' !== $customFormConfiguration) {
            $this->setCustomItems();
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     */
    private function setCustomItems(): void
    {
        $products = OrderLabel::getCustomsOrderProducts($this->orderData['id_order']);

        foreach ($products as $product) {

            if (! $product) {
                continue;
            }

            $weight      = (new OrderTotalWeight())->convertWeightToGrams($product['product_weight']);
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

        return (int) ($productHsCode ?? $defaultHsCode);
    }

    /**
     * Create a new consignment
     *
     * @return void
     * @throws \Exception
     */
    private function createConsignment(): void
    {
        $carrier           = CarrierService::getMyParcelCarrier($this->orderData['id_carrier']);
        $this->consignment = PlatformServiceFactory::create()
            ->generateConsignment($carrier);
    }

    /**
     * @param  string $deliveryDate
     *
     * @return string
     */
    private function fixPastDeliveryDate(string $deliveryDate): string
    {
        $tomorrow = new DateTime('tomorrow');

        try {
            $deliveryDateObject = new DateTime($deliveryDate);
        } catch (Exception $e) {
            return $tomorrow->format(self::FORMAT_TIMESTAMP);
        }

        $oldDate = clone $deliveryDateObject;
        $tomorrow->setTime(0, 0);
        $oldDate->setTime(0, 0);

        do {
            $deliveryDateObject->add(new DateInterval('P1D'));
        } while ($deliveryDateObject < $tomorrow || '0' === $deliveryDateObject->format('w'));

        return $deliveryDateObject->format(self::FORMAT_TIMESTAMP);
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
     * @param array  $order
     * @param string $labelParams
     * @param string $labelDefaultParam
     *
     * @return string
     */
    private function getLabelParams(array $order, string $labelParams, string $labelDefaultParam = 'id_order'): string
    {
        if (! isset($this->orderData[$labelDefaultParam])) {
            $labelDefaultParam = 'id_order';
        }

        if (empty(trim($labelParams))) {
            return $order[$labelDefaultParam];
        }

        $pattern = '/\{[a-zA-Z_]+\.[a-zA-Z_]+\}/m';

        preg_match_all($pattern, $labelParams, $matches, PREG_SET_ORDER, 0);

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
                    if (count($key) === 2) {
                        if ($key[0] === 'order') {
                            $keys[$value] = $key[1];
                            continue;
                        }
                    }
                }
            }
        }

        if (empty($keys)) {
            return $order[$labelDefaultParam];
        }

        foreach ($keys as $index => $key) {
            if (! isset($this->orderData[$key])) {
                unset($keys[$index]);
            }
            $labelParams = str_replace($index, $order[$key], $labelParams);
        }

        return trim($labelParams);
    }

    /**
     * @return void
     */
    private function setTotalWeight(): void
    {
        if (
            isset($this->request['digitalStampWeight'])
            && AbstractConsignment::PACKAGE_TYPE_DIGITAL_STAMP === $this->consignment->getPackageType()
        ) {
            $this->consignment->setTotalWeight($this->request['digitalStampWeight']);
        }
    }
}

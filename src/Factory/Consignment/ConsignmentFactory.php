<?php

namespace Gett\MyparcelBE\Factory\Consignment;

use Carrier;
use Configuration;
use Country;
use Exception;
use Gett\MyparcelBE\Carrier\PackageTypeCalculator;
use Gett\MyparcelBE\Constant;
use OrderLabel;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use Gett\MyparcelBE\Service\ProductConfigurationProvider;
use Module;
use MyParcelBE;
use MyParcelNL\Sdk\src\Factory\ConsignmentFactory as ConsignmentSdkFactory;
use MyParcelNL\Sdk\src\Factory\DeliveryOptionsAdapterFactory;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\BpostConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\DPDConsignment;
use MyParcelNL\Sdk\src\Model\Consignment\PostNLConsignment;
use MyParcelNL\Sdk\src\Model\MyParcelCustomsItem;
use Order;
use Tools;
use Validate;

class ConsignmentFactory
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var array|array[]
     */
    private $carrierSettings;

    /**
     * @var \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     */
    private $deliveryOptions;

    /**
     * @var array
     */
    private $request;

    /**
     * @var \Module
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
     * @param string        $apiKey
     * @param array         $request
     * @param Module        $module
     */
    public function __construct(string $apiKey, array $request, Module $module)
    {
        $this->api_key = $apiKey;
        $this->module  = $module;
        $this->request = $request;
    }

    /**
     * @param  array $orders
     *
     * @return MyParcelCollection
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function fromOrders(array $orders): MyParcelCollection
    {
        $myParcelCollection = (new MyParcelCollection());

        foreach ($orders as $order) {
            $this->setOrderData($order);
            $this->createConsignment();
            $myParcelCollection
                ->setUserAgents($this->getUserAgent())
                ->addConsignment($this->initConsignment());
        }

        return $myParcelCollection;
    }

    /**
     * @param  array $order
     *
     * @return MyParcelCollection
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function fromOrder(array $order): MyParcelCollection
    {
        $this->setOrderData($order);
        $this->createConsignment();

        $myParcelCollection = (new MyParcelCollection());

        for ($i = 0; $i < $this->request['label_amount']; ++$i) {
            $consignment = $this->initConsignment();
            foreach (Constant::SINGLE_LABEL_CREATION_OPTIONS as $key => $option) {
                if (isset($this->request[$key])) {
                    if (method_exists($this, $option)) {
                        $consignment = $this->{$option}($consignment);
                    }
                }
            }

            $myParcelCollection
                ->setUserAgents($this->getUserAgent())
                ->addConsignment($consignment);
        }

        return $myParcelCollection;
    }

    /**
     * @return array
     */
    private function getUserAgent(): array
    {
        return [
            'PrestaShop'            => _PS_VERSION_,
            'MyParcelBE-PrestaShop' => MyParcelBE::VERSION,
        ];
    }

    /**
     * @param  array $order
     *
     * @throws \Exception
     */
    private function setOrderData(array $order): void
    {
        $carrierSettingsProvider = new CarrierSettingsProvider($this->module);
        $deliveryOptionsData     = json_decode($order['delivery_settings'], true);

        $this->orderData       = $order;
        $this->carrierSettings = $carrierSettingsProvider->provide($order['id_carrier']);
        $this->deliveryOptions = DeliveryOptionsAdapterFactory::create($deliveryOptionsData);
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setBaseData(): void
    {
        $orderObject = new Order((int) $this->orderData['id_order']);
        $floatWeight = $orderObject->getTotalWeight();
        $this->consignment
            ->setApiKey($this->api_key)
            ->setReferenceId($this->orderData['id_order'])
            ->setPackageType($this->getPackageType())
            ->setDeliveryDate($this->getDeliveryDate())
            ->setDeliveryType($this->deliveryOptions->getDeliveryType())
            ->setLabelDescription($this->getFormattedLabelDescription())
            ->setTotalWeight((new OrderTotalWeight())->convertWeightToGrams($floatWeight));
    }

    /**
     * @return int
     */
    private function getPackageType(): int
    {
        $packageType = $this->request['packageType'] ?? (new PackageTypeCalculator())->getOrderPackageType(
                $this->orderData['id_order'],
                $this->orderData['id_carrier']
            );

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

        $timestamp        = strtotime($date);
        $deliveryDateTime = date('Y-m-d H:i:s', $timestamp);
        $deliveryDate     = date('Y-m-d', $timestamp);
        $dateOfToday      = date('Y-m-d');
        $dateOfTomorrow   = date('Y-m-d H:i:s', strtotime('now +1 day'));

        if ($deliveryDate <= $dateOfToday) {
            return $dateOfTomorrow;
        }

        return $deliveryDateTime;
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
            ->setFullStreet($this->orderData['full_street'])
            ->setPostalCode($this->orderData['postcode'])
            ->setCity($this->orderData['city'])
            ->setEmail($this->getEmailConfiguration())
            ->setPhone($this->getPhoneConfiguration())
            ->setSaveRecipientAddress(false);
    }

    /**
     * @return string
     */
    private function getEmailConfiguration(): string
    {
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
        $this->consignment
            ->setOnlyRecipient($this->hasOnlyRecipient())
            ->setSignature($this->hasSignature())
            ->setContents(AbstractConsignment::PACKAGE_CONTENTS_COMMERCIAL_GOODS)
            ->setInvoice($this->orderData['invoice_number']);
    }

    /**
     * @throws Exception
     */
    private function hasOnlyRecipient(): bool
    {
        $shipmentOptions = $this->deliveryOptions->getShipmentOptions();
        $hasOnlyRecipient = $shipmentOptions && $shipmentOptions->hasOnlyRecipient();

        if ($this->consignment instanceof PostNLConsignment && $hasOnlyRecipient) {
            $this->consignment->setOnlyRecipient(true);
        }

        return false;
    }

    private function hasSignature(): bool
    {
        $countryCode      = strtoupper($this->orderData['iso_code']);
        $shipmentOptions  = $this->deliveryOptions->getShipmentOptions();
        $signatureAllowed = ! empty($this->carrierSettings['allowSignature'][$countryCode]);
        $hasSignature     = $shipmentOptions && $shipmentOptions->hasSignature() && $signatureAllowed;

        // Signature is required for pickup delivery type
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
        $shippingCountry         = Country::getIdZone($this->orderData['id_country']);
        $customFormConfiguration = Configuration::get(Constant::CUSTOMS_FORM_CONFIGURATION_NAME);

        if (1 !== $shippingCountry && 'No' !== $customFormConfiguration) {
            $this->setCustomItems();
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    private function setCustomItems(): void
    {
        $products = OrderLabel::getCustomsOrderProducts($this->orderData['id_order']);

        foreach ($products as $product) {
            $product = $product->get_product();

            if (! $product) {
                continue;
            }

            $weight      = (new OrderTotalWeight())->convertWeightToGrams($product['product_weight']);
            $description = $product['product_name'];
            $itemValue   = Tools::ps_round($product['unit_price_tax_incl'] * 100);

            if (strlen($description) > Constant::ITEM_DESCRIPTION_MAX_LENGTH) {
                $description = substr_replace($description, '...', Constant::ITEM_DESCRIPTION_MAX_LENGTH - 3);
            }

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
     * @param int $productId
     *
     * @return string
     */
    private function getCountryOfOrigin(int $productId): string
    {
        $productCountryOfOrigin = ProductConfigurationProvider::get($productId, Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME);
        $defaultCountryOfOrigin = Configuration::get(Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME);

        return $productCountryOfOrigin ?? $defaultCountryOfOrigin;
    }

    /**
     * @param int $productId
     *
     * @return int
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
        $this->consignment = ConsignmentSdkFactory::createByCarrierId($this->getMyParcelCarrierId($this->orderData['id_carrier']));
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

        return $this->consignment;
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return false|AbstractConsignment
     */
    private function MYPARCELBE_RECIPIENT_ONLY(AbstractConsignment $consignment)
    {
        if ($consignment instanceof PostNLConsignment) {
            return $this->consignment->setOnlyRecipient(true);
        }

        return false;
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return AbstractConsignment
     */
    private function MYPARCELBE_AGE_CHECK(AbstractConsignment $consignment)
    {
        return $this->consignment->setAgeCheck(true);
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return AbstractConsignment
     */
    private function MYPARCELBE_PACKAGE_TYPE(AbstractConsignment $consignment)
    {
        return $this->consignment->setPackageType($this->request['packageType']);
    }

    /**
     * @return AbstractConsignment
     * @throws Exception
     */
    private function MYPARCELBE_INSURANCE()
    {
        $insuranceValue = 0;
        if (isset($postValues['insuranceAmount'])) {
            if (strpos($postValues['insuranceAmount'], 'amount') !== false) {
                $insuranceValue = (int)str_replace(
                    'amount',
                    '',
                    $postValues['insuranceAmount']
                );
            } else {
                $insuranceValue = (int)($postValues['insurance-amount-custom-value'] ?? 0);
                if (empty($insuranceValue)) {
                    throw new Exception('Insurance value cannot be empty');
                }
            }
        }

        if ($this->module->isBE() && $insuranceValue > 500) {
            $this->module->controller->errors[] = $this->module->l(
                'Insurance value cannot more than € 500',
                'consignmentfactory'
            );
            throw new Exception('Insurance value cannot more than € 500');
        }
        if ($this->module->isNL() && $insuranceValue > 5000) {
            $this->module->controller->errors[] = $this->module->l(
                'Insurance value cannot more than € 5000',
                'consignmentfactory'
            );
            throw new Exception('Insurance value cannot more than € 5000');
        }

        return $this->consignment->setInsurance($insuranceValue);
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return AbstractConsignment
     * @throws Exception
     */
    private function MYPARCELBE_RETURN_PACKAGE(AbstractConsignment $consignment): AbstractConsignment
    {
        return $this->consignment->setReturn(true);
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return false|AbstractConsignment
     */
    private function MYPARCELBE_SIGNATURE_REQUIRED(AbstractConsignment $consignment)
    {
        if (! $consignment instanceof DPDConsignment) {
            return $this->consignment->setSignature(true);
        }

        return false;
    }

    /**
     * @param AbstractConsignment $consignment
     *
     * @return AbstractConsignment
     */
    private function MYPARCELBE_PACKAGE_FORMAT(AbstractConsignment $consignment)
    {
        return $this->consignment->setLargeFormat($this->request['packageFormat'] == 2);
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
     * @param  int $carrierId
     *
     * @return int
     * @throws \Exception
     */
    public function getMyParcelCarrierId(int $carrierId): int
    {
        $carrier = new Carrier($carrierId);
        if (! Validate::isLoadedObject($carrier)) {
            throw new Exception('No carrier found.');
        }

        $carrierType = CarrierConfigurationProvider::get($carrierId, 'carrierType');

        if ($carrierType === Constant::POSTNL_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::POSTNL_CONFIGURATION_NAME)) {
            return PostNLConsignment::CARRIER_ID;
        }

        if ($carrierType === Constant::BPOST_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::BPOST_CONFIGURATION_NAME)) {
            return BpostConsignment::CARRIER_ID;
        }

        if ($carrierType === Constant::DPD_CARRIER_NAME
            || $carrier->id_reference === (int) Configuration::get(Constant::DPD_CONFIGURATION_NAME)) {
            return DPDConsignment::CARRIER_ID;
        }

        throw new Exception('Undefined carrier');
    }
}

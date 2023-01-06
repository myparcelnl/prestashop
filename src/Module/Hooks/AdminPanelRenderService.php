<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Closure;
use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsMerger;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Logger\OrderLogger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Module\Carrier\Provider\DeliveryOptionsProvider;
use Gett\MyparcelBE\Provider\OrderLabelProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\CountryService;
use Gett\MyparcelBE\Service\LabelOptionsService;
use Gett\MyparcelBE\Service\WeightService;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Address;
use PrestaShop\PrestaShop\Adapter\Entity\AddressFormat;
use PrestaShop\PrestaShop\Adapter\Entity\Customer;
use Throwable;

const RETURN_PACKAGE_TYPES = [
    AbstractConsignment::PACKAGE_TYPE_MAILBOX_NAME,
    AbstractConsignment::PACKAGE_TYPE_PACKAGE_NAME,
];
class AdminPanelRenderService extends RenderService
{
    public const  ID_PRINT_OPTIONS                         = 'printOptions';
    public const  ID_RETURNS_FORM                          = 'returnsForm';
    public const  ID_SHIPMENT_LABELS                       = 'shipmentLabels';
    public const  ID_SHIPMENT_OPTIONS                      = 'shipmentOptions';
    public const  ID_SHIPPING_ADDRESS                      = 'shippingAddress';
    private const CONSIGNMENT_OPTIONS_CARRIER_SETTINGS_MAP = [
        'canHaveAgeCheck'      => 'ageCheck',
        'canHaveInsurance'     => 'insurance',
        'canHaveOnlyRecipient' => 'onlyRecipient',
        'canHaveReturn'        => 'returnUndelivered',
        'canHaveSignature'     => 'signatureRequired',
    ];
    private const CONSIGNMENT_OPTIONS_MAP                  = [
        'canHaveAgeCheck'      => AbstractConsignment::SHIPMENT_OPTION_AGE_CHECK,
        'canHaveInsurance'     => AbstractConsignment::SHIPMENT_OPTION_INSURANCE,
        'canHaveLargeFormat'   => AbstractConsignment::SHIPMENT_OPTION_LARGE_FORMAT,
        'canHaveOnlyRecipient' => AbstractConsignment::SHIPMENT_OPTION_ONLY_RECIPIENT,
        'canHaveReturn'        => AbstractConsignment::SHIPMENT_OPTION_RETURN,
        'canHaveSignature'     => AbstractConsignment::SHIPMENT_OPTION_SIGNATURE,
    ];

    /**
     * @return array
     */
    public function getPrintOptionsContext(): array
    {
        $labelOptions = LabelOptionsService::getInstance();

        return [
            'labelFormat'            => Configuration::get(Constant::LABEL_SIZE_CONFIGURATION_NAME),
            'labelOutput'            => $labelOptions->getLabelOutput(),
            'labelPosition'          => $labelOptions->getLabelPosition(),
            'promptForLabelPosition' => (bool) Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
        ];
    }

    /**
     * @param  null|\Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getShipmentLabelsContext(Order $order): array
    {
        $deliveryAddress = new Address($order->id_address_delivery);

        return [
            'id'              => self::ID_SHIPMENT_LABELS,
            'orderId'         => $order->getId(),
            'deliveryAddress' => AddressFormat::generateAddress($deliveryAddress, [], '<br />'),
            'labels'          => (new OrderLabelProvider())->provideLabels($order->getId()),
        ];
    }

    /**
     * @param  null|\Gett\MyparcelBE\Model\Core\Order                                          $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $presetDeliveryOptions
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function getShipmentOptionsContext(
        ?Order                          $order = null,
        ?AbstractDeliveryOptionsAdapter $presetDeliveryOptions = null
    ): array {
        $context = [
            'id'              => self::ID_SHIPMENT_OPTIONS,
            'deliveryAddress' => null,
            'orderId'         => null,
            'consignment'     => null,
            'deliveryOptions' => null,
            'extraOptions'    => null,
            'labelOptions'    => null,
            'options'         => [
                'packageType'        => [],
                'packageFormat'      => [],
                'digitalStampWeight' => $this->getDigitalStampWeightOptions(),
            ],
        ];

        if ($order) {
            $orderSettings = OrderSettingsFactory::create($order);

            $context['orderId']      = $order->getId();
            $context['orderWeight']  = $orderSettings->getOrderWeight();
            $context['extraOptions'] = $orderSettings->getExtraOptions()
                ->toArray();
            $context['labelOptions'] = $orderSettings->getLabelOptions();

            $carrierOptionsCalculator            = $this->getCarrierOptionsCalculator($order);
            $context['options']['packageType']   = $this->getPackageTypes($carrierOptionsCalculator, $order);
            $context['options']['packageFormat'] = $carrierOptionsCalculator->getAvailablePackageFormats();

            $context = array_merge($context, $this->getDeliveryOptionsContext($order, $presetDeliveryOptions));

            $context['psCarrierId'] = $order->getIdCarrier();
        }

        return $context;
    }

    /**
     * @throws \Exception
     */
    public function getShippingAddressContext(Order $order): array
    {
        $address = (new Address($order->id_address_delivery));

        return [
            'id'               => self::ID_SHIPPING_ADDRESS,
            'addressId'        => $address->id,
            'action'           => $this->context->link->getAdminLink('AdminAddresses', true, [], [
                'id_order'     => $order->getId(),
                'id_address'   => $address->id,
                'addaddress'   => '',
                'realedit'     => 1,
                'address_type' => 1,
                'back'         => urlencode(str_replace('&conf=4', '', $_SERVER['REQUEST_URI'])),
            ]),
            'formattedAddress' => AddressFormat::generateAddress($address, [], '<br />'),
        ];
    }

    /**
     * @throws \Exception
     */
    public function renderLoadingPage(): string
    {
        return $this->renderWithContext('renderLoadingPage');
    }

    /**
     * @return string
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function renderModals(): string
    {
        return $this->renderWithContext('renderAfterHeader', [
            self::ID_PRINT_OPTIONS    => $this->getPrintOptionsContext(),
            self::ID_RETURNS_FORM     => [],
            self::ID_SHIPMENT_OPTIONS => $this->getShipmentOptionsContext(),
        ]);
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function renderOrderSettings(Order $order): string
    {
        return $this->renderWithContext('renderOrderCard', [
                self::ID_RETURNS_FORM     => $this->getReturnsContext($order),
                self::ID_SHIPMENT_LABELS  => $this->getShipmentLabelsContext($order),
                self::ID_SHIPMENT_OPTIONS => $this->getShipmentOptionsContext($order),
                self::ID_SHIPPING_ADDRESS => $this->getShippingAddressContext($order),
            ]
        );
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  array                                                                           $map
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $presetDeliveryOptions
     *
     * @return array
     */
    protected function getConsignmentOptions(
        Order                           $order,
        array                           $map,
        ?AbstractDeliveryOptionsAdapter $presetDeliveryOptions = null
    ): array {
        $consignment        = $this->createConsignmentForOrder($order, $presetDeliveryOptions);
        $consignmentOptions = [
            'canHaveInsurance' => true,
        ];

        if (! $consignment) {
            return [];
        }

        if (CountryService::isPostNLShipmentFromNLToBE($consignment)) {
            return $consignmentOptions + [
                    'insuranceOptions' => [Constant::INSURANCE_CONFIGURATION_BELGIUM_AMOUNT],
                ];
        }

        $isStandardDelivery = $consignment->getDeliveryType() === AbstractConsignment::DELIVERY_TYPE_STANDARD;
        $consignmentOptions += [
            'insuranceOptions' => $consignment->getInsurancePossibilities($consignment->country),
        ];

        if (CountryService::isPostNLToOtherCountry($consignment)) {
            return $consignmentOptions;
        }

        foreach ($map as $key => $consignmentOption) {
            $consignmentOptions[$key] = $isStandardDelivery && $consignment->canHaveShipmentOption($consignmentOption);
        }

        return $consignmentOptions;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $presetDeliveryOptions
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    protected function getDeliveryOptionsContext(
        Order                           $order,
        ?AbstractDeliveryOptionsAdapter $presetDeliveryOptions = null
    ): array {
        $deliveryOptionsProvider = new DeliveryOptionsProvider();
        $orderSettings           = OrderSettingsFactory::create($order);
        $deliveryOptions         = DeliveryOptionsMerger::create(
            $orderSettings->getDeliveryOptions(),
            $presetDeliveryOptions
        );

        return [
            'consignment'                => $this->getConsignmentOptions(
                $order,
                self::CONSIGNMENT_OPTIONS_MAP,
                $deliveryOptions
            ),
            'deliveryOptions'            => $deliveryOptions ? $deliveryOptions->toArray() : [],
            'deliveryOptionsDateChanged' => $deliveryOptionsProvider->provideWarningDisplay($order->getId()),
        ];
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  null|\MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter $presetDeliveryOptions
     *
     * @return null|\MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment
     */
    private function createConsignmentForOrder(
        Order                           $order,
        ?AbstractDeliveryOptionsAdapter $presetDeliveryOptions = null
    ): ?AbstractConsignment {
        $consignment = null;

        try {
            $consignment = (new ConsignmentFactory(Constant::CONSIGNMENT_INIT_PARAMS_FOR_CHECKING_ONLY))
                ->fromOrder(
                    $order,
                    $presetDeliveryOptions ?? OrderSettingsFactory::create($order)
                    ->getDeliveryOptions()
                )
                ->first();
        } catch (Throwable $e) {
            OrderLogger::addLog(['message' => $e, 'order' => $order], OrderLogger::ERROR);
            $this->addOrderError($e, $order);
        }

        return $consignment;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return \Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator
     * @throws \Exception
     */
    private function getCarrierOptionsCalculator(Order $order): CarrierOptionsCalculator
    {
        $myParcelCarrier = CarrierService::getMyParcelCarrier($order->getIdCarrier());

        return new CarrierOptionsCalculator($myParcelCarrier);
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return \Closure
     * @throws \Exception
     */
    private function getCarrierSettingsFilter(Order $order): Closure
    {
        $carrierSettings = (new CarrierSettingsProvider())->provide($order->getIdCarrier());

        return static function (string $key) use ($carrierSettings) {
            return static function (array $item) use ($key, $carrierSettings) {
                return true === Arr::get($carrierSettings, "return.$key.{$item['value']}");
            };
        };
    }

    /**
     * @return array
     */
    private function getDigitalStampWeightOptions(): array
    {
        return array_map(static function (array $range) {
            return [
                'label' => $range['min'] . ' – ' . $range['max'] . 'g',
                'value' => $range['average'],
            ];
        }, WeightService::DIGITAL_STAMP_RANGES);
    }

    /**
     * @param  \Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator $carrierOptionsCalculator
     * @param  \Gett\MyparcelBE\Model\Core\Order                        $order
     *
     * @return array
     */
    private function getPackageTypes(CarrierOptionsCalculator $carrierOptionsCalculator, Order $order): array
    {
        $packageTypes = $carrierOptionsCalculator->getAvailablePackageTypes();

        if (CountryService::getShippingCountryIso2($order) !== $this->module->getModuleCountry()) {
            return array_filter($packageTypes, static function (array $packageType) {
                return $packageType['name'] === AbstractConsignment::DEFAULT_PACKAGE_TYPE_NAME;
            });
        }

        return $packageTypes;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \Exception
     */
    private function getReturnsContext(Order $order): array
    {
        $address                  = (new Address($order->id_address_delivery));
        $carrierOptionsCalculator = $this->getCarrierOptionsCalculator($order);

        return [
            'name'        => $address->firstname . ' ' . $address->lastname,
            'email'       => (new Customer($order->id_customer))->email,
            'consignment' => $this->getConsignmentOptions($order, self::CONSIGNMENT_OPTIONS_CARRIER_SETTINGS_MAP),
            'options'     => [
                'packageType'   => array_filter(
                    $this->getPackageTypes($carrierOptionsCalculator, $order),
                    static function ($item) {
                        return in_array($item['name'], RETURN_PACKAGE_TYPES);
                    }
                ),
                'packageFormat' => $carrierOptionsCalculator->getAvailablePackageFormats(),
            ],
        ];
    }
}

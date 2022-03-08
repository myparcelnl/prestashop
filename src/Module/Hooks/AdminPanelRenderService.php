<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Hooks;

use Closure;
use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliverySettings\DeliverySettings;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Carrier\CarrierOptionsCalculator;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Module\Carrier\Provider\DeliveryOptionsProvider;
use Gett\MyparcelBE\Provider\OrderLabelProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\LabelOptionsService;
use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use Gett\MyparcelBE\Service\WeightService;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use PrestaShop\PrestaShop\Adapter\Entity\Address;
use PrestaShop\PrestaShop\Adapter\Entity\AddressFormat;
use PrestaShop\PrestaShop\Adapter\Entity\Customer;

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
     * @param  null|\Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \Exception
     */
    public function getShipmentOptionsContext(?Order $order = null): array
    {
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
            $extraOptions = DeliverySettings::getExtraOptionsFromOrder($order);

            $context['orderId']      = $order->getId();
            $context['orderWeight']  = (new OrderTotalWeight())->convertWeightToGrams($order->getTotalWeight());
            $context['extraOptions'] = [
                'digitalStampWeight' => $extraOptions->getDigitalStampWeight() ??
                    WeightService::convertToDigitalStampWeight($context['orderWeight']),
                'labelAmount'        => $extraOptions->getLabelAmount(),
            ];
            $context['labelOptions'] = (new LabelOptionsResolver())->getLabelOptions($order);

            $carrierOptionsCalculator            = $this->getCarrierOptionsCalculator($order);
            $context['options']['packageType']   = $carrierOptionsCalculator->getAvailablePackageTypeNames();
            $context['options']['packageFormat'] = $carrierOptionsCalculator->getAvailablePackageFormats();

            $context = array_merge($context, $this->getDeliveryOptionsContext($order));
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
        return $this->renderWithContext('renderOrderGridCard', [
                self::ID_RETURNS_FORM     => $this->getReturnsContext($order),
                self::ID_SHIPMENT_LABELS  => $this->getShipmentLabelsContext($order),
                self::ID_SHIPMENT_OPTIONS => $this->getShipmentOptionsContext($order),
                self::ID_SHIPPING_ADDRESS => $this->getShippingAddressContext($order),
            ]
        );
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     * @param  array                             $map
     *
     * @return array
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function getConsignmentOptions(Order $order, array $map): array
    {
        $consignment = $this->createConsignmentForOrder($order);
        if (! $consignment) {
            return [];
        }

        $isStandardDelivery = $consignment->getDeliveryType() === AbstractConsignment::DELIVERY_TYPE_STANDARD;
        $consignmentOptions = [
            'insuranceOptions' => $consignment->getInsurancePossibilities(),
        ];

        foreach ($map as $key => $consignmentOption) {
            $consignmentOptions[$key] = $isStandardDelivery && $consignment->canHaveShipmentOption($consignmentOption);
        }

        return $consignmentOptions;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \Exception
     */
    protected function getDeliveryOptionsContext(Order $order): array
    {
        $deliveryOptionsProvider = new DeliveryOptionsProvider();
        $deliveryOptions         = DeliverySettings::getDeliveryOptionsFromOrder($order);

        return [
            'consignment'                => $this->getConsignmentOptions($order, self::CONSIGNMENT_OPTIONS_MAP),
            'deliveryOptions'            => $deliveryOptions->toArray(),
            'deliveryOptionsDateChanged' => $deliveryOptionsProvider->provideWarningDisplay($order->getId()),
        ];
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return null|\MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    private function createConsignmentForOrder(Order $order): ?AbstractConsignment
    {
        $deliveryOptions = DeliverySettings::getDeliveryOptionsFromOrder($order);
        return (new ConsignmentFactory([]))
            ->fromOrder($order, $deliveryOptions)
            ->first();
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
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     *
     * @return array
     * @throws \Exception
     */
    private function getReturnsContext(Order $order): array
    {
        $address                  = (new Address($order->id_address_delivery));
        $carrierOptionsCalculator = $this->getCarrierOptionsCalculator($order);
        $filter                   = $this->getCarrierSettingsFilter($order);

        return [
            'name'        => $address->firstname . ' ' . $address->lastname,
            'email'       => (new Customer($order->id_customer))->email,
            'consignment' => $this->getConsignmentOptions($order, self::CONSIGNMENT_OPTIONS_CARRIER_SETTINGS_MAP),
            'options'     => [
                'packageType'   => Arr::where(
                    $carrierOptionsCalculator->getAvailablePackageTypeNames(),
                    $filter('packageType')
                ),
                'packageFormat' => Arr::where(
                    $carrierOptionsCalculator->getAvailablePackageFormats(),
                    $filter('packageFormat')
                ),
            ],
        ];
    }
}

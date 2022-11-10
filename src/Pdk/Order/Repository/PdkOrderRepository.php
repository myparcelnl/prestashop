<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use Configuration;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Pdk\Order\Storage\DatabaseOrderStorage;
use MyParcelNL\PrestaShop\Service\ProductConfigurationProvider;
use MyParcelNL\PrestaShop\Service\WeightService;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;
use Order;
use OrderLabel;
use Tools;

class PdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Service\CountryService
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\PrestaShop\Pdk\Order\Storage\DatabaseOrderStorage $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface         $api
     * @param  \MyParcelNL\Pdk\Base\Service\CountryService             $countryService
     */
    public function __construct(DatabaseOrderStorage $storage, ApiServiceInterface $api, CountryService $countryService)
    {
        parent::__construct($storage, $api);
        $this->countryService = $countryService;
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     */
    public function get($input): PdkOrder
    {
        $order = $input;

        if (! is_a($input, Order::class)) {
            $order = new Order($input);
        }

        return $this->retrieve((string) $order->id, function () use ($order) {
            return $this->getDataFromOrder($order);
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     */
    public function update(PdkOrder $order): PdkOrder
    {
        //        $originalOrder = $this->get($order->externalIdentifier);
        //        $updatedOrder  = $order->fill($order->toArray());

        $this->save();

        return $order;
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        return $collection->map([$this, 'update']);
    }

    /**
     * @param  \Order $order
     * @param  array  $orderData
     *
     * @return null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration
     * @throws \PrestaShopDatabaseException
     */
    private function getCustomsDeclaration(Order $order, array $orderData): ?CustomsDeclaration
    {
        $isToRowCountry          = $this->countryService->isRowCountry(strtoupper($orderData['iso_code']));
        $customFormConfiguration = Configuration::get(Constant::CUSTOMS_FORM_CONFIGURATION_NAME);

        if (! $isToRowCountry || 'No' === $customFormConfiguration) {
            return null;
        }

        $products = OrderLabel::getCustomsOrderProducts($order->id);

        $items = (new Collection($products))
            ->filter()
            ->map(function ($product) {
                $productHsCode = ProductConfigurationProvider::get(
                    $product['product_id'],
                    Constant::CUSTOMS_CODE_CONFIGURATION_NAME
                );

                $productCountryOfOrigin = ProductConfigurationProvider::get(
                    $product['product_id'],
                    Constant::CUSTOMS_ORIGIN_CONFIGURATION_NAME
                );

                return new CustomsDeclarationItem([
                    'amount'         => $product['product_quantity'],
                    'classification' => (int) ($productHsCode
                        ?: Configuration::get(
                            Constant::DEFAULT_CUSTOMS_CODE_CONFIGURATION_NAME
                        )),
                    'country'        => $productCountryOfOrigin ?? Configuration::get(
                            Constant::DEFAULT_CUSTOMS_ORIGIN_CONFIGURATION_NAME
                        ),
                    'description'    => $product['product_name'],
                    'itemValue'      => Tools::ps_round($product['unit_price_tax_incl'] * 100),
                    'weight'         => WeightService::convertToGrams($product['product_weight']),
                ]);
            });

        return new CustomsDeclaration([
            'contents' => null,
            'invoice'  => null,
            'items'    => $items->toArray(),
            'weight'   => null,
        ]);
    }

    /**
     * @param  \Order $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getDataFromOrder(Order $order): PdkOrder
    {
        $orderData        = OrderLabel::getDataForLabelsCreate((int) $order->id);
        $deliverySettings = json_decode($orderData['delivery_settings'] ?? '{}', true);
        $deliveryOptions  = new DeliveryOptions(Arr::except($deliverySettings, 'isPickup'));

        if (! $deliveryOptions->carrier) {
            $primaryCarrier = (new Collection(Config::get('carriers')))->firstWhere('primary', true);

            $deliveryOptions->carrier = $primaryCarrier['name'] ?? null;
        }

        $shipments = [];

        //        [
        //                [
        //                    'id'                       => null,
        //                    'orderId'                  => $order->id,
        ////                    'carrier'                  => $carrier ? ['name' => $carrier] : null,
        //                    'barcode'                  => null,
        //                    'isReturn'                 => null,
        //                    'sender'                   => null,
        //                    //  'deliveryOptions'          => $orderData,
        //                    'dropOffPoint'             => null,
        //                    // 'customsDeclaration'       => [],
        //                    'physicalProperties'       => [
        //                        'weight' => WeightService::convertToGrams($order->getTotalWeight()),
        //                    ],
        //                    'collectionContact'        => null,
        //                    'delayed'                  => null,
        //                    'delivered'                => null,
        //                    'externalIdentifier'       => null,
        //                    'linkConsumerPortal'       => null,
        //                    'multiColloMainShipmentId' => null,
        //                    'partnerTrackTraces'       => null,
        //                    'referenceIdentifier'      => "PrestaShop: $order->id",
        //                    'multiCollo'               => $deliveryOptions->labelAmount > 1,
        //                ],
        //            ]

        return new PdkOrder([
            'externalIdentifier' => $order->id,
            'recipient'          => [
                'cc'         => strtoupper($orderData['iso_code']),
                'city'       => $orderData['city'],
                'company'    => $orderData['company'],
                'email'      => $orderData['email'],
                'fullStreet' => $orderData['full_street'],
                'person'     => $orderData['person'],
                'phone'      => $orderData['phone'],
                'postalCode' => $orderData['postcode'],
                'region'     => $orderData['state_name'],
            ],
            'sender'             => [],
            'deliveryOptions'    => $deliveryOptions,
            'shipments'          => $shipments,
            'customsDeclaration' => $this->getCustomsDeclaration($order, $orderData),
        ]);
    }
}

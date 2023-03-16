<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use Address;
use Country;
use Customer;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsOrderShipmentRepository;
use Order;
use RuntimeException;
use State;

class PdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @var \MyParcelNL\Pdk\Product\Repository\AbstractProductRepository
     */
    protected $productRepository;

    /**
     * @var \MyParcelNL\Pdk\Base\Service\CurrencyService
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkShipmentRepository
     */
    private $pdkShipmentRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsOrderDataRepository
     */
    private $psOrderDataRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage                             $storage
     * @param  \MyParcelNL\Pdk\Base\Service\CurrencyService                           $currencyService
     * @param  \MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsOrderDataRepository     $psOrderDataRepository
     * @param  \MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface            $productRepository
     * @param  \MyParcelNL\PrestaShop\Pdk\Plugin\Repository\PsOrderShipmentRepository $psOrderShipmentRepository
     */
    public function __construct(
        MemoryCacheStorage         $storage,
        CurrencyService            $currencyService,
        PsOrderDataRepository      $psOrderDataRepository,
        ProductRepositoryInterface $productRepository,
        PsOrderShipmentRepository  $psOrderShipmentRepository
    ) {
        parent::__construct($storage);
        $this->currencyService           = $currencyService;
        $this->psOrderDataRepository     = $psOrderDataRepository;
        $this->productRepository         = $productRepository;
        $this->psOrderShipmentRepository = $psOrderShipmentRepository;
    }

    /**
     * @param  mixed $input
     *
     * @return void
     * @throws \Exception
     */
    public function delete($input): void
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function get($input): PdkOrder
    {
        $order = $input;

        if (! is_a($input, Order::class)) {
            $order = new Order($input);
        }

        return $this->retrieve((string) $order->id, function () use ($order) {
            $orderData = $this->psOrderDataRepository->firstWhere('idOrder', $order->id);
            $data      = $orderData ? $orderData->getData() : [];

            $orderProducts = $order->getProducts() ?: [];

            return new PdkOrder([
                'externalIdentifier'  => $order->id,
                'recipient'           => $this->getRecipient($order),
                'deliveryOptions'     => $data['deliveryOptions'] ?? [],
                'shipments'           => $this->getShipments($order),
                'referenceIdentifier' => "PrestaShop: $order->id",
                'shipmentPrice'       => $this->currencyService->convertToCents($order->total_shipping_tax_incl),
                'shipmentVat'         => $this->currencyService->convertToCents($order->total_shipping_tax_excl),
                'lines'               => $this->createOrderLines($orderProducts),
                'customsDeclaration'  => $this->createCustomsDeclaration($order, $orderProducts),
            ]);
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function update(PdkOrder $order): PdkOrder
    {
        $collection = new PdkOrderCollection([$order]);

        return $this->updateMany($collection)
            ->first();
    }

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        $collection->each(function (PdkOrder $order) {
            $this->psOrderDataRepository->updateOrCreate(
                [
                    'idOrder' => $order->externalIdentifier,
                ],
                [
                    'data' => json_encode([
                        'deliveryOptions' => $order->deliveryOptions->toArray(),
                    ]),
                ]
            );

            $order->shipments->each(function (Shipment $shipment) use ($order) {
                $this->psOrderShipmentRepository->updateOrCreate(
                    [
                        'idOrder'    => $order->externalIdentifier,
                        'idShipment' => $shipment->id,
                    ],
                    [
                        'data' => json_encode($shipment->toStorableArray()),
                    ]
                );
            });
        });

        $this->psOrderShipmentRepository->flush();
        $this->psOrderDataRepository->flush();

        return $collection;
    }

    /**
     * @param  \Order $order
     * @param  array  $orderProducts
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration
     */
    protected function createCustomsDeclaration(Order $order, array $orderProducts): CustomsDeclaration
    {
        return new CustomsDeclaration([
            'invoice' => $order->invoice_number,
            'items'   => array_map(function (array $product) {
                $pdkProduct = $this->productRepository->getProduct($product['id_product']);

                return CustomsDeclarationItem::fromProduct($pdkProduct, [
                    'amount'    => $product['product_quantity'] ?? 1,
                    'itemValue' => [
                        'amount' => $this->currencyService->convertToCents($product['product_price_wt'] ?? 0),
                    ],
                ]);
            }, array_values($orderProducts)),
        ]);
    }

    /**
     * @param  array $orderProducts
     *
     * @return array|array[]
     */
    protected function createOrderLines(array $orderProducts): array
    {
        return array_map(
            function (array $product) {
                return [
                    'quantity'      => $this->currencyService->convertToCents(
                        $product['product_quantity'] ?? 0
                    ),
                    'price'         => $this->currencyService->convertToCents($product['product_price'] ?? 0),
                    'priceAfterVat' => $this->currencyService->convertToCents(
                        $product['product_price_wt'] ?? 0
                    ),
                    'product'       => $this->productRepository->getProduct($product['product_id']),
                ];
            },
            array_values($orderProducts)
        );
    }

    /**
     * @param  \Order $order
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function getRecipient(Order $order): array
    {
        $address  = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $country  = new Country($address->id_country);

        return [
            'cc'         => $country->iso_code,
            'city'       => $address->city,
            'company'    => $address->company,
            'email'      => $customer->email,
            'fullStreet' => $address->address1,
            'person'     => $customer->firstname . ' ' . $customer->lastname,
            'phone'      => $address->phone,
            'postalCode' => $address->postcode,
            'region'     => $country->iso_code === Platform::get('localCountry')
                ? null
                : (new State($address->id_state))->name,
        ];
    }

    /**
     * @param  \Order $order
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    protected function getShipments(Order $order): ShipmentCollection
    {
        $shipments = $this->psOrderShipmentRepository->where('idOrder', $order->id);

        $shipmentsArray = $shipments->map(function ($shipment) {
            return array_merge(
                $shipment->getData(),
                [
                    'id'      => $shipment->getIdShipment(),
                    'orderId' => $shipment->getIdOrder(),
                ]
            );
        })
            ->toArray();

        return (new ShipmentCollection($shipmentsArray))
            ->where('deleted', false)
            ->values();
    }
}

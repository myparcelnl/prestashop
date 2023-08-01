<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use Address;
use Country;
use Customer;
use CustomerMessage;
use DateTimeImmutable;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use Order;
use State;

class PsPdkOrderRepository extends AbstractPdkOrderRepository
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter
     */
    private $addressAdapter;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository
     */
    private $psCartDeliveryOptionsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $psOrderDataRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $psOrderShipmentRepository;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface
     */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage                        $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository           $psOrderDataRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository       $psOrderShipmentRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface            $currencyService
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface  $productRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface              $weightService
     * @param  \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter          $addressAdapter
     */
    public function __construct(
        MemoryCacheStorage              $storage,
        PsOrderDataRepository           $psOrderDataRepository,
        PsOrderShipmentRepository       $psOrderShipmentRepository,
        PsCartDeliveryOptionsRepository $psCartDeliveryOptionsRepository,
        CurrencyServiceInterface        $currencyService,
        PdkProductRepositoryInterface   $productRepository,
        WeightServiceInterface          $weightService,
        PsAddressAdapter                $addressAdapter
    ) {
        parent::__construct($storage);
        $this->psOrderDataRepository           = $psOrderDataRepository;
        $this->psOrderShipmentRepository       = $psOrderShipmentRepository;
        $this->psCartDeliveryOptionsRepository = $psCartDeliveryOptionsRepository;
        $this->currencyService                 = $currencyService;
        $this->productRepository               = $productRepository;
        $this->weightService                   = $weightService;
        $this->addressAdapter                  = $addressAdapter;
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     * @throws \Doctrine\ORM\ORMException
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
            $orderData     = $this->getOrderData($order);
            $orderProducts = $order->getProducts() ?: [];

            return new PdkOrder(
                array_replace([
                    'externalIdentifier'  => $order->id,
                    'shippingAddress'     => $this->addressAdapter->fromOrder($order, 'shipping'),
                    'billingAddress'      => $this->addressAdapter->fromOrder($order, 'billing'),
                    'physicalProperties'  => $this->getPhysicalProperties($orderProducts),
                    'shipments'           => $this->getShipments($order),
                    'referenceIdentifier' => "PrestaShop: $order->id",
                    'shipmentPrice'       => $this->currencyService->convertToCents($order->total_shipping_tax_incl),
                    'shipmentVat'         => $this->currencyService->convertToCents($order->total_shipping_tax_excl),
                    'lines'               => $this->createOrderLines($orderProducts),
                    'customsDeclaration'  => $this->createCustomsDeclaration($order, $orderProducts),
                    'invoiceId'           => $order->id,
                    'invoiceDate'         => $order->date_add,
                    'paymentMethod'       => $order->payment,
                ], $orderData, [
                    'notes' => $this->getOrderNotes($order, $orderData['notes'] ?? []),
                ])
            );
        });
    }

    /**
     * @param  null|string $date
     * @param  string      $fallback
     *
     * @return string
     */
    private function getDate(?string $date, string $fallback): string
    {
        try {
            return (new DateTimeImmutable($date))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return $fallback;
        }
    }

    /**
     * @param  \Order $order
     * @param  array  $existingNotes
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     * @throws \Exception
     */
    public function getOrderNotes(Order $order, array $existingNotes): PdkOrderNoteCollection
    {
        return $this->retrieve(
            sprintf("notes_%s", $order->id),
            function () use ($existingNotes, $order) {
                $collection = new PdkOrderNoteCollection($existingNotes);
                $orderNotes = new PdkOrderNoteCollection();
                $orderDate  = (new DateTimeImmutable($order->date_add))->format('Y-m-d H:i:s');

                $customerNotes = CustomerMessage::getMessagesByOrderId($order->id);

                foreach ($customerNotes as $customerNote) {
                    $author = '0' === ($customerNote['id_employee'] ?? '0')
                        ? OrderNote::AUTHOR_CUSTOMER
                        : OrderNote::AUTHOR_WEBSHOP;

                    $orderNotes->push([
                        'apiIdentifier'      => null,
                        'externalIdentifier' => $customerNote['id_customer_message'],
                        'note'               => $customerNote['message'],
                        'author'             => $author,
                        'createdAt'          => $this->getDate($customerNote['date_add'] ?? null, $orderDate),
                        'updatedAt'          => $this->getDate($customerNote['date_upd'] ?? null, $orderDate),
                    ]);
                }

                return $orderNotes->mergeByKey($collection, 'externalIdentifier');
            }
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
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
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection $collection
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function updateMany(PdkOrderCollection $collection): PdkOrderCollection
    {
        $collection->each(function (PdkOrder $order) {
            $this->saveOrderData((string) $order->externalIdentifier, $order->toStorableArray());

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
                    'vatRate'       => $product['tax_rate'],
                ];
            },
            array_values($orderProducts)
        );
    }

    /**
     * @param  \Order $order
     *
     * @return array
     */
    protected function getShipments(Order $order): array
    {
        $shipments = $this->psOrderShipmentRepository->where('idOrder', $order->id);

        return $shipments
            ->map(function (MyparcelnlOrderShipment $shipment) {
                return array_merge(
                    $shipment->getData(),
                    [
                        'id'      => $shipment->getIdShipment(),
                        'orderId' => $shipment->getIdOrder(),
                    ]
                );
            })
            ->toArray();
    }

    /**
     * @param  \Order $order
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function getShippingAddress(Order $order): array
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
     * In PrestaShop, the delivery options are stored in the cart, not in the order. So we need to get them from the
     * cart if they are not present in the order yet.
     *
     * @param  \Order $order
     *
     * @return array
     * @throws \Doctrine\ORM\ORMException
     */
    private function getOrderData(Order $order): array
    {
        $fromOrder = $this->psOrderDataRepository->findOneBy(['idOrder' => $order->id]);

        if (! $fromOrder) {
            $fromCart = $this->psCartDeliveryOptionsRepository->findOneBy(['idCart' => $order->id_cart]);

            $context = [
                'cartId'  => $order->id_cart,
                'orderId' => $order->id,
            ];

            if (! $fromCart) {
                Logger::debug('No delivery options found in cart, saving empty order data to order', $context);
            } else {
                Logger::debug('Delivery options found in cart, saving to order', $context);
            }

            $deliveryOptions = $fromCart ? $fromCart->getData() : [];

            $this->saveOrderData((string) $order->id, $deliveryOptions);

            return $deliveryOptions;
        }

        return $fromOrder->getData();
    }

    /**
     * @param  array $orderProducts
     *
     * @return int[]
     */
    private function getPhysicalProperties(array $orderProducts): array
    {
        $weight = 0;

        foreach ($orderProducts as $product) {
            $weight += ((float) $product['product_weight'] * $product['product_quantity']);
        }

        return [
            'weight' => $this->weightService->convertToGrams($weight),
        ];
    }

    /**
     * @param  string $id
     * @param  array  $orderData
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    private function saveOrderData(string $id, array $orderData): void
    {
        $this->psOrderDataRepository->updateOrCreate(
            [
                'idOrder' => $id,
            ],
            [
                'data' => json_encode($orderData),
            ]
        );
        $this->psOrderDataRepository->flush();
    }
}

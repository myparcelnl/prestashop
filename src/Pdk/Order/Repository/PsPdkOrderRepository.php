<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Service\PsProductService;
use Order as PsOrder;

final class PsPdkOrderRepository extends AbstractPdkOrderRepository
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
     * @var \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface
     */
    private $psOrderService;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository
     */
    private $psOrderShipmentRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Service\PsProductService
     */
    private PsProductService $psProductService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage                       $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository      $psOrderShipmentRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface           $currencyService
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository
     * @param  \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter         $addressAdapter
     * @param  \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface          $psOrderService
     * @param  \MyParcelNL\PrestaShop\Service\PsProductService                  $psProductService
     */
    public function __construct(
        MemoryCacheStorage            $storage,
        PsOrderShipmentRepository     $psOrderShipmentRepository,
        CurrencyServiceInterface      $currencyService,
        PdkProductRepositoryInterface $productRepository,
        PsAddressAdapter              $addressAdapter,
        PsOrderServiceInterface       $psOrderService,
        PsProductService              $psProductService
    ) {
        parent::__construct($storage);
        $this->psOrderShipmentRepository = $psOrderShipmentRepository;
        $this->currencyService           = $currencyService;
        $this->productRepository         = $productRepository;
        $this->addressAdapter            = $addressAdapter;
        $this->psOrderService            = $psOrderService;
        $this->psProductService          = $psProductService;
    }

    /**
     * @param  string|int|PsOrder $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Doctrine\ORM\ORMException
     */
    public function get($input): PdkOrder
    {
        if (! $this->psOrderService->exists($input)) {
            throw new InvalidArgumentException('Order not found');
        }

        /** @var \Order $psOrder */
        $psOrder = $this->psOrderService->get($input);

        return $this->retrieve((string) $psOrder->id, function () use ($psOrder) {
            $orderData     = $this->psOrderService->getOrderData($psOrder);
            $orderProducts = new Collection($psOrder->getProducts() ?: []);

            return new PdkOrder(
                array_replace([
                    'externalIdentifier'  => $psOrder->id,
                    'shippingAddress'     => $this->addressAdapter->fromOrder($psOrder, 'shipping'),
                    'billingAddress'      => $this->addressAdapter->fromOrder($psOrder, 'billing'),
                    'shipments'           => $this->getShipments($psOrder),
                    'referenceIdentifier' => "PrestaShop: $psOrder->id",
                    'shipmentPrice'       => $this->currencyService->convertToCents($psOrder->total_shipping_tax_incl),
                    'shipmentVat'         => $this->currencyService->convertToCents($psOrder->total_shipping_tax_excl),
                    'lines'               => $this->createOrderLines($orderProducts),
                    'invoiceId'           => $psOrder->id,
                    'invoiceDate'         => $psOrder->date_add,
                    'paymentMethod'       => $psOrder->payment,
                ], $orderData)
            );
        });
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
            $this->psOrderService->updateOrderData((string) $order->externalIdentifier, $order->toStorableArray());

            $order->shipments->each(function (Shipment $shipment) use ($order) {
                $this->psOrderShipmentRepository->updateOrCreate(
                    [
                        'orderId'    => $order->externalIdentifier,
                        'shipmentId' => $shipment->id,
                    ],
                    [
                        'data' => json_encode($shipment->toStorableArray()),
                    ]
                );
            });
        });

        return $collection;
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection $orderProducts
     *
     * @return array|array[]
     */
    protected function createOrderLines(Collection $orderProducts): array
    {
        return $orderProducts
            ->filter(function (array $product) {
                return $this->psProductService->exists($product['id_product'] ?? 0);
            })
            ->map(function (array $product) {
                return [
                    'quantity'      => $product['product_quantity'] ?? 1,
                    'price'         => $this->currencyService->convertToCents($product['product_price'] ?? 0),
                    'priceAfterVat' => $this->currencyService->convertToCents($product['product_price_wt'] ?? 0),
                    'product'       => $this->productRepository->getProduct($product['product_id'] ?? 0),
                    'vatRate'       => $product['tax_rate'] ?? 0,
                ];
            })
            ->toArrayWithoutNull();
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return self::class;
    }

    /**
     * @param  \Order $psOrder
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getShipments(PsOrder $psOrder): Collection
    {
        return $this->psOrderShipmentRepository
            ->where('orderId', $psOrder->id)
            ->map(static function (MyparcelnlOrderShipment $shipment) {
                return array_replace($shipment->getData(), [
                    'id'      => $shipment->getShipmentId(),
                    'orderId' => $shipment->getOrderId(),
                ]);
            })
            ->values();
    }
}

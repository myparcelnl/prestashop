<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderData;
use MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Repository\PsOrderDataRepository;
use MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository;
use MyParcelNL\PrestaShop\Service\PsProductService;
use Order as PsOrder;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;
use PrestaShopCollection;

final class PsPdkOrderRepository extends AbstractPdkOrderRepository implements PdkOrderRepositoryInterface
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
     * @var \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository
     */
    private $psOrderDataRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Service\PsProductService
     */
    private PsProductService $psProductService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorage                       $storage
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderShipmentRepository      $psOrderShipmentRepository
     * @param  \MyParcelNL\PrestaShop\Repository\PsOrderDataRepository          $psOrderDataRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface           $currencyService
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository
     * @param  \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter         $addressAdapter
     * @param  \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface          $psOrderService
     * @param  \MyParcelNL\PrestaShop\Service\PsProductService                  $psProductService
     */
    public function __construct(
        MemoryCacheStorage            $storage,
        PsOrderShipmentRepository     $psOrderShipmentRepository,
        PsOrderDataRepository         $psOrderDataRepository,
        CurrencyServiceInterface      $currencyService,
        PdkProductRepositoryInterface $productRepository,
        PsAddressAdapter              $addressAdapter,
        PsOrderServiceInterface       $psOrderService,
        PsProductService              $psProductService
    ) {
        parent::__construct($storage);
        $this->psOrderShipmentRepository = $psOrderShipmentRepository;
        $this->psOrderDataRepository     = $psOrderDataRepository;
        $this->currencyService           = $currencyService;
        $this->productRepository         = $productRepository;
        $this->addressAdapter            = $addressAdapter;
        $this->psOrderService            = $psOrderService;
        $this->psProductService          = $psProductService;
    }

    /**
     * Build a single, fully-detailed PdkOrder from a PrestaShop order (id or model).
     *
     * Intended for the order detail / export flows where the full order is needed. Loads the
     * PrestaShop order model for its base fields (addresses, lines, prices) and merges in the
     * MyParcel-managed data and shipments via the shared assembler. Result is memoized per order
     * id through retrieve(). Throws when the order does not exist; use find() for a null-safe lookup.
     *
     * @param  string|int|PsOrder $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Doctrine\ORM\ORMException
     */
    public function get($input): PdkOrder
    {
        /** @var \Order $psOrder */
        $psOrder = $this->psOrderService->get($input);

        if (! $psOrder) {
            throw new InvalidArgumentException('Order not found');
        }

        return $this->retrieve((string) $psOrder->id, function () use ($psOrder) {
            return $this->assembleOrder(
                $this->baseFromOrder($psOrder),
                $this->psOrderService->getOrderData($psOrder),
                ['shipments' => $this->getShipments($psOrder)]
            );
        });
    }

    /**
     * Get multiple fully-detailed orders by their identifier.
     *
     * Action flows such as export need addresses, lines and prices. Keep findAll() as the
     * lightweight list-level lookup used by the order grid.
     *
     * @param string|string[] $orderIds
     * @return PdkOrderCollection
     */
    public function getMany($orderIds): PdkOrderCollection
    {
        return parent::getMany($orderIds);
    }

    /**
     * @param  string|int $id
     *
     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function find($id): ?PdkOrder
    {
        try {
            return $this->get($id);
        } catch (InvalidArgumentException $e) {
            // Specifically check for the "order not found exception" and return null in that case, rethrow otherwise
            if ($e->getMessage() === 'Order not found') {
                return null;
            }
            // Fallthrough, re-throw exception if it's not the "order not found" case
            throw $e;
        }
    }

    /**
     * Fetch a single order by id or throw when it does not exist (implements the contract's
     * findOrFail()). Intended for callers that treat a missing order as an error rather than a null
     * result; delegates to find() and raises ModelNotFoundException when nothing is found.
     *
     * @param  string|int $id
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     * @throws \MyParcelNL\Pdk\Base\Exception\ModelNotFoundException
     */
    public function findOrFail($id): PdkOrder
    {
        $order = $this->find($id);

        if (! $order) {
            throw new ModelNotFoundException(PdkOrder::class, [$id]);
        }

        return $order;
    }

    /**
     * Batch-fetch existing PdkOrders by their PrestaShop order ids.
     *
     * Missing ids are skipped (null-safe, like find()).
     *
     * Fully bulk: the order base rows are fetched in a single query via PrestaShopCollection, and
     * the stored MyParcel data and shipments are eager-loaded once for the whole set by
     * assembleOrders() — no per-order querying. The orders are list-level (the base covers the
     * order-table fields a grid item needs); use get() when a single, fully-detailed order with
     * addresses and lines is required.
     *
     * @param  int[] $ids
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function findAll(array $ids): PdkOrderCollection
    {
        if (empty($ids)) {
            return new PdkOrderCollection([]);
        }

        return $this->assembleOrders($this->fetchBasesByOrderIds($ids));
    }

    /**
     * Fetch every order in the shop as PdkOrders.
     *
     * Mirrors findAll() without an id filter: a single PrestaShopCollection query for the bases,
     * then one eager-load of stored data and shipments via assembleOrders(). This is unbounded by
     * the nature of the contract — prefer findAll() with a known id set wherever possible.
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function all(): PdkOrderCollection
    {
        return $this->assembleOrders($this->fetchBasesByOrderIds());
    }

    /**
     * Build a batch of lightweight PdkOrders directly from a PrestaShop order-grid record set.
     *
     * Intended for rendering the order grid: the grid records already carry the base PrestaShop
     * fields, so no order models are loaded. Bases are taken straight from the records and handed
     * to assembleOrders(), which eager-loads the MyParcel data and shipments for the whole page in
     * one query each. Notes are forced empty because the grid does not render them, which avoids a
     * lazy per-order notes lookup.
     *
     * @param  \PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection $collection
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    public function fromOrderGridCollection(RecordCollection $collection): PdkOrderCollection
    {
        $basesByOrderId = [];

        foreach ($collection->all() as $record) {
            $basesByOrderId[(int) $record['id_order']] = $this->baseFromOrderGridRecord($record);
        }

        return $this->assembleOrders($basesByOrderId, ['notes' => []]);
    }

    /**
     * @param  string $uuid
     *
     * @return null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder
     * @throws \Doctrine\ORM\ORMException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getByApiIdentifier(string $uuid): ?PdkOrder
    {
        $orderData = $this->psOrderDataRepository->findOneByApiIdentifier($uuid);

        if (! $orderData) {
            return null;
        }

        try {
            return $this->get($orderData->getOrderId());
        } catch (InvalidArgumentException $e) {
            return null;
        }
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
     * Merge the data sets that make up a single order into one PdkOrder.
     *
     * This is the single source of merge precedence used by every build path (single and batch):
     * the prestashop order fields are the foundation, the stored MyParcel data overrides them, and the
     * eager-loaded relations (shipments, and optionally an empty notes collection) override last.
     *
     * @param  array $base      Base order attributes from the platform (order model or grid record).
     * @param  array $stored    Stored MyParcel data (delivery options, physical properties, exported, ...).
     * @param  array $relations Separately-resolved relations, applied last so they win (e.g. shipments, notes).
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    private function assembleOrder(array $base, array $stored, array $relations = []): PdkOrder
    {
        return new PdkOrder(array_replace($base, $stored, $relations));
    }

    /**
     * Assemble many PdkOrders from pre-built bases, eager-loading the shared relations once.
     *
     * Intended as the common batch path behind findAll() and fromOrderGridCollection(): callers
     * supply the per-order base attributes keyed by order id, and this fetches the stored MyParcel
     * data and shipments for the whole set in a single query each, then merges every order through
     * assembleOrder(). $relationDefaults are applied as the final override for every order (e.g.
     * ['notes' => []] to suppress lazy notes loading in list views).
     *
     * @param  array $basesByOrderId   Base order attributes keyed by PrestaShop order id.
     * @param  array $relationDefaults Relation overrides applied to every assembled order.
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection
     */
    private function assembleOrders(array $basesByOrderId, array $relationDefaults = []): PdkOrderCollection
    {
        if (empty($basesByOrderId)) {
            return new PdkOrderCollection([]);
        }

        $orderIds   = array_keys($basesByOrderId);
        // Eager load the related myparcel data in one query each. Order data is keyed by order id
        // (findAll), shipments belong to an order via a foreign key (where on orderId).
        $storedData = $this->psOrderDataRepository->findAll($orderIds);
        $shipments  = $this->psOrderShipmentRepository->where('orderId', $orderIds);

        $orders = [];

        foreach ($basesByOrderId as $orderId => $base) {
            $storedRecord = $storedData->first(static function (MyparcelnlOrderData $row) use ($orderId): bool {
                return $row->getOrderId() === (int) $orderId;
            });

            $orderShipments = $shipments
                ->filter(static function (MyparcelnlOrderShipment $shipment) use ($orderId): bool {
                    return $shipment->getOrderId() === (int) $orderId;
                })
                ->map(function (MyparcelnlOrderShipment $shipment): array {
                    return $this->toShipmentArray($shipment);
                })
                ->values();

            $orders[] = $this->assembleOrder(
                $base,
                $storedRecord ? ($storedRecord->getData() ?? []) : [],
                array_replace(['shipments' => $orderShipments], $relationDefaults)
            );
        }

        return new PdkOrderCollection($orders);
    }

    /**
     * Extract the base PdkOrder attributes from a full PrestaShop order model.
     *
     * Intended for build paths that have (or load) the complete order model: produces the platform
     * foundation fields (identifiers, addresses, prices, lines, invoice, payment) WITHOUT the stored
     * MyParcel data or shipments, which the assembler merges in separately.
     *
     * @param  \Order $psOrder
     *
     * @return array
     */
    private function baseFromOrder(PsOrder $psOrder): array
    {
        $orderProducts = new Collection($psOrder->getProducts() ?: []);

        return [
            'externalIdentifier'  => $psOrder->id,
            'shippingAddress'     => $this->addressAdapter->fromOrder($psOrder, 'shipping'),
            'billingAddress'      => $this->addressAdapter->fromOrder($psOrder, 'billing'),
            'referenceIdentifier' => $psOrder->reference,
            'shipmentPrice'       => $this->currencyService->convertToCents($psOrder->total_shipping_tax_incl),
            'shipmentVat'         => $this->currencyService->convertToCents($psOrder->total_shipping_tax_excl),
            'lines'               => $this->createOrderLines($orderProducts),
            'invoiceId'           => $psOrder->id,
            'invoiceDate'         => $psOrder->date_add,
            'paymentMethod'       => $psOrder->payment,
        ];
    }

    /**
     * Extract the base PdkOrder attributes from a PrestaShop order-grid record.
     *
     * Intended for the grid build path: the grid record already carries the minimal PrestaShop
     * fields needed for a list item, so this avoids loading the full order model. Stored MyParcel
     * data and shipments are merged in separately by the assembler.
     *
     * @param  array $record A single PrestaShop order-grid record.
     *
     * @return array
     */
    private function baseFromOrderGridRecord(array $record): array
    {
        return [
            'externalIdentifier'  => $record['id_order'],
            'referenceIdentifier' => $record['reference'],
            'invoiceId'           => $record['id_order'],
            'invoiceDate'         => $record['date_add'],
            'paymentMethod'       => $record['payment'],
        ];
    }

    /**
     * Bulk-fetch order base attributes keyed by order id with a single PrestaShopCollection query.
     *
     * Shared by findAll() and all(): pass ids to restrict to those orders (non-existent ids are
     * naturally excluded by the query), or null to fetch every order. Reads only the order-table
     * fields needed for a base; stored MyParcel data and shipments are merged in by assembleOrders().
     *
     * @param  null|int[] $ids
     *
     * @return array Base order attributes keyed by PrestaShop order id.
     */
    private function fetchBasesByOrderIds(?array $ids = null): array
    {
        $orders = new PrestaShopCollection(PsOrder::class);

        if (null !== $ids) {
            $orders->where('id_order', 'in', array_map('intval', $ids));
        }

        $basesByOrderId = [];

        /** @var \Order $psOrder */
        foreach ($orders->getResults() as $psOrder) {
            $basesByOrderId[(int) $psOrder->id] = $this->baseFromOrderGridRecord([
                'id_order'  => $psOrder->id,
                'reference' => $psOrder->reference,
                'date_add'  => $psOrder->date_add,
                'payment'   => $psOrder->payment,
            ]);
        }

        return $basesByOrderId;
    }

    /**
     * Serialize a stored shipment entity into the array shape the PdkOrder shipments attribute
     * expects. Centralizes the shipment mapping so the single and batch build paths stay identical.
     *
     * @param  \MyParcelNL\PrestaShop\Entity\MyparcelnlOrderShipment $shipment
     *
     * @return array
     */
    private function toShipmentArray(MyparcelnlOrderShipment $shipment): array
    {
        return array_replace($shipment->getData(), [
            'id'      => $shipment->getShipmentId(),
            'orderId' => $shipment->getOrderId(),
        ]);
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
     * Fetch and serialize the stored shipments for a single PrestaShop order.
     *
     * Intended for the single-order build path (get()): queries the shipments belonging to the
     * order and maps them through toShipmentArray() into the shape the PdkOrder shipments attribute
     * expects.
     *
     * @param  \Order $psOrder
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getShipments(PsOrder $psOrder): Collection
    {
        return $this->psOrderShipmentRepository
            ->where('orderId', $psOrder->id)
            ->map(function (MyparcelnlOrderShipment $shipment): array {
                return $this->toShipmentArray($shipment);
            })
            ->values();
    }
}

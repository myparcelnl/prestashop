<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use CustomerMessage;
use DateTimeImmutable;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderNoteRepository;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use Throwable;

final class PsPdkOrderNoteRepository extends AbstractPdkOrderNoteRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface
     */
    private $psOrderService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface       $storage
     * @param  \MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface $psOrderService
     */
    public function __construct(StorageInterface $storage, PsOrderServiceInterface $psOrderService)
    {
        parent::__construct($storage);
        $this->psOrderService = $psOrderService;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderNote $note
     *
     * @return void
     * @throws \PrestaShopException
     */
    public function add(PdkOrderNote $note): void
    {
        // create order note
        $customerMessage = new CustomerMessage();

        $customerMessage->id_customer_thread = $note->orderIdentifier;
        $customerMessage->id_employee        = 0;
        $customerMessage->message            = $note->note;

        $customerMessage->save();
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     * @throws \Exception
     */
    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection
    {
        $data = $this->psOrderService->getOrderData($order->externalIdentifier);

        return $this->retrieve($order->externalIdentifier, function () use ($data, $order) {
            $collection = new PdkOrderNoteCollection($data['notes'] ?? []);
            $messages   = CustomerMessage::getMessagesByOrderId($order->externalIdentifier);

            $customerNotes = (new Collection($messages))
                ->map(function (array $customerNote) use ($order) {
                    $author = ($customerNote['id_employee'] ?? '0') === '0'
                        ? OrderNote::AUTHOR_CUSTOMER
                        : OrderNote::AUTHOR_WEBSHOP;

                    return [
                        'apiIdentifier'      => null,
                        'externalIdentifier' => $customerNote['id_customer_message'],
                        'note'               => $customerNote['message'],
                        'author'             => $author,
                        'createdAt'          => $this->getDate(
                            $customerNote['date_add'] ?? null,
                            $order->orderDate
                        ),
                        'updatedAt'          => $this->getDate(
                            $customerNote['date_upd'] ?? null,
                            $order->orderDate
                        ),
                    ];
                });

            return new PdkOrderNoteCollection($customerNotes->mergeByKey($collection, 'externalIdentifier'));
        });
    }

    public function update(PdkOrderNote $note): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return self::class;
    }

    /**
     * @param  null|string             $date
     * @param  null|\DateTimeImmutable $fallback
     *
     * @return string
     */
    private function getDate(?string $date, ?DateTimeImmutable $fallback = null): string
    {
        try {
            $resolvedDate = new DateTimeImmutable($date);
        } catch (Throwable $e) {
            $resolvedDate = $fallback ?? new DateTimeImmutable();
        }

        return $resolvedDate->format(Pdk::get('defaultDateFormat'));
    }
}

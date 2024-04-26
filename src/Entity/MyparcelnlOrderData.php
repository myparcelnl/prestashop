<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;

/**
 * @ORM\Table
 * @ORM\Entity
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration
 * @final
 */
class MyparcelnlOrderData extends AbstractEntity implements EntityWithTimestampsInterface
{
    use HasTimestamps;
    use HasJsonData;

    /**
     * @var string
     * @ORM\Column(name="notes", type="text")
     */
    private $notes;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="order_id", type="integer")
     */
    private $orderId;

    public static function getTable(): string
    {
        return Table::TABLE_ORDER_DATA;
    }

    /**
     * @return array
     */
    public function getNotes(): array
    {
        return $this->notes ? json_decode($this->notes, true) : [];
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @param  string $notes
     *
     * @return $this
     */
    public function setNotes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @param  int|string $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId): MyparcelnlOrderData
    {
        $this->orderId = (int) $orderId;

        return $this;
    }

    public function toArray(?int $flags = null): array
    {
        return [
            'orderId' => $this->getOrderId(),
            'data'    => $this->getData(),
            'notes'   => $this->getNotes(),
        ];
    }
}

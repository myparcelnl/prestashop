<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;

/**
 * @ORM\Table
 * @ORM\Entity
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration
 */
final class MyparcelnlOrderData extends AbstractEntity
{
    use HasJsonData;

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

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function setOrderId(int $orderId): MyparcelnlOrderData
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'orderId' => $this->getOrderId(),
            'data'    => $this->getData(),
        ];
    }
}

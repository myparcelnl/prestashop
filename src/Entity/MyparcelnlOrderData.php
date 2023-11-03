<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;
use MyParcelNL\PrestaShop\Entity\Concern\HasTimestamps;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;

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
        ];
    }
}

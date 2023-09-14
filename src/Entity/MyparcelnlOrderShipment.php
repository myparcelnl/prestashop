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
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderShipmentTableDatabaseMigration
 */
final class MyparcelnlOrderShipment extends AbstractEntity implements EntityWithTimestampsInterface
{
    use HasJsonData;
    use HasTimestamps;

    /**
     * @var int
     * @ORM\Column(name="order_id", type="integer")
     */
    private $orderId;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="shipment_id", type="integer")
     */
    private $shipmentId;

    public static function getTable(): string
    {
        return Table::TABLE_ORDER_SHIPMENT;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getShipmentId(): int
    {
        return $this->shipmentId;
    }

    public function setOrderId($orderId): self
    {
        $this->orderId = (int) $orderId;

        return $this;
    }

    public function setShipmentId(int $shipmentId): self
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'orderId'    => $this->getOrderId(),
            'shipmentId' => $this->getShipmentId(),
            'data'       => $this->getData(),
            'dateAdd'    => $this->getDateAdd(),
            'dateUpd'    => $this->getDateUpd(),
        ];
    }
}

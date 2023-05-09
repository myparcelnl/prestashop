<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderShipmentTableDatabaseMigration
 */
class MyparcelnlOrderShipment extends AbstractEntity
{
    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=false)
     */
    protected $data;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=false)
     */
    protected $idOrder;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    protected $idShipment;

    /**
     * @return array
     */
    public function getData(): array
    {
        return json_decode($this->data, true);
    }

    /**
     * @return string
     */
    public function getIdOrder(): string
    {
        return $this->idOrder;
    }

    /**
     * @return int
     */
    public function getIdShipment(): int
    {
        return $this->idShipment;
    }

    /**
     * @param  string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @param  string $idOrder
     */
    public function setIdOrder(string $idOrder): void
    {
        $this->idOrder = $idOrder;
    }

    /**
     * @param  int $idShipment
     */
    public function setIdShipment(int $idShipment): void
    {
        $this->idShipment = $idShipment;
    }
}

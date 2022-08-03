<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderDataTableMigration
 */
class MyparcelnlOrderData extends AbstractEntity
{
    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=false)
     */
    protected $data;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=false, unique=true)
     */
    protected $idOrder;

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
}

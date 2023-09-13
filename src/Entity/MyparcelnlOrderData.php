<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration
 */
final class MyparcelnlOrderData extends AbstractEntity
{
    use HasJsonData;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=false, unique=true)
     */
    public $idOrder;

    public static function getTable(): string
    {
        return Table::TABLE_ORDER_DATA;
    }
}

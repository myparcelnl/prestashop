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
final class MyparcelnlCartDeliveryOptions extends AbstractEntity
{
    use HasJsonData;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    public $idCart;

    public static function getTable(): string
    {
        return Table::TABLE_CART_DELIVERY_OPTIONS;
    }
}

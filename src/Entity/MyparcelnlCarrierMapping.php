<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Database\Table;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateCarrierMappingTableDatabaseMigration
 */
final class MyparcelnlCarrierMapping extends AbstractEntity
{
    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    public $idCarrier;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=false, unique=true)
     */
    public $myparcelCarrier;

    public static function getTable(): string
    {
        return Table::TABLE_CARRIER_MAPPING;
    }
}

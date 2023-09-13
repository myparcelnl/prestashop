<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateProductSettingsTableDatabaseMigration
 */
final class MyparcelnlProductSettings extends AbstractEntity
{
    use HasJsonData;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    public $idProduct;

    public static function getTable(): string
    {
        return Table::TABLE_PRODUCT_SETTINGS;
    }
}

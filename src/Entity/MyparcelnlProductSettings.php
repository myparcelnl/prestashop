<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;
use MyParcelNL\PrestaShop\Entity\Concern\HasTimestamps;

/**
 * @ORM\Table
 * @ORM\Entity
 * @see \MyParcelNL\PrestaShop\Database\CreateProductSettingsTableDatabaseMigration
 */
final class MyparcelnlProductSettings extends AbstractEntity
{
    use HasJsonData;
    use HasTimestamps;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;

    public static function getTable(): string
    {
        return Table::TABLE_PRODUCT_SETTINGS;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $productId): MyparcelnlProductSettings
    {
        $this->productId = $productId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'productId' => $this->getProductId(),
            'data'      => $this->getData(),
            'dateAdd'   => $this->getDateAdd(),
            'dateUpd'   => $this->getDateUpd(),
        ];
    }
}

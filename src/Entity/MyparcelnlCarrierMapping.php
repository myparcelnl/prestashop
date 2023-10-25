<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasTimestamps;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;

/**
 * @ORM\Table()
 * @ORM\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateCarrierMappingTableDatabaseMigration
 */
final class MyparcelnlCarrierMapping extends AbstractEntity implements EntityWithTimestampsInterface
{
    use HasTimestamps;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="carrier_id", type="integer", nullable=false, unique=true)
     */
    private $carrierId;

    /**
     * @var string
     * @ORM\Column(name="myparcel_carrier", type="string", nullable=false, unique=true)
     */
    private $myparcelCarrier;

    public static function getTable(): string
    {
        return Table::TABLE_CARRIER_MAPPING;
    }

    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    public function getMyparcelCarrier(): string
    {
        return $this->myparcelCarrier;
    }

    public function setCarrierId(int $carrierId): MyparcelnlCarrierMapping
    {
        $this->carrierId = $carrierId;

        return $this;
    }

    public function setMyparcelCarrier(string $myparcelCarrier): MyparcelnlCarrierMapping
    {
        $this->myparcelCarrier = $myparcelCarrier;

        return $this;
    }

    public function toArray(?int $flags = null): array
    {
        return [
            'carrierId'       => $this->getCarrierId(),
            'myparcelCarrier' => $this->getMyparcelCarrier(),
        ];
    }
}

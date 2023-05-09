<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

/**
 * @Doctrine\ORM\Mapping\Table()
 * @Doctrine\ORM\Mapping\Entity()
 * @see \MyParcelNL\PrestaShop\Database\CreateCarrierConfigurationTableDatabaseMigration
 */
class MyparcelnlCarrierConfiguration extends AbstractEntity
{
    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    protected $idCarrier;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Column(type="integer", nullable=false, unique=true)
     */
    protected $idConfiguration;

    /**
     * @var string
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=false, unique=true)
     */
    protected $myparcelCarrier;

    /**
     * @return int
     */
    public function getIdCarrier(): int
    {
        return $this->idCarrier;
    }

    /**
     * @return int
     */
    public function getIdConfiguration(): int
    {
        return $this->idConfiguration;
    }

    /**
     * @return string
     */
    public function getMyparcelCarrier(): string
    {
        return $this->myparcelCarrier;
    }

    /**
     * @param  int $idCarrier
     */
    public function setIdCarrier(int $idCarrier): void
    {
        $this->idCarrier = $idCarrier;
    }

    /**
     * @param  int $idConfiguration
     */
    public function setIdConfiguration(int $idConfiguration): void
    {
        $this->idConfiguration = $idConfiguration;
    }
}

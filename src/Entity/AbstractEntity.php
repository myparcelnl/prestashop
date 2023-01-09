<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

abstract class AbstractEntity
{
    /**
     * @var \DateTime
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=false, options={"default" = "CURRENT_TIMESTAMP"})
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     */
    public $created;

    /**
     * @var int
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\Column(type="integer")
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var \DateTime
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=false, options={"default" = "CURRENT_TIMESTAMP", "onUpdate" = "CURRENT_TIMESTAMP"})
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     */
    public $updated;

    /**
     * @return \DateTime
     */
    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated(): \DateTime
    {
        return $this->updated;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

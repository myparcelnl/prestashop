<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;

abstract class AbstractEntity implements EntityInterface
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
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=false, options={"default" = "CURRENT_TIMESTAMP",
     *                                               "onUpdate" = "CURRENT_TIMESTAMP"})
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="AUTO")
     */
    public $updated;

    public function __construct() {}

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}

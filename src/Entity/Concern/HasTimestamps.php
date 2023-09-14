<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Concern;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\HasLifeCycleCallbacks
 */
trait HasTimestamps
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;

    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    public function getDateUpd(): DateTime
    {
        return $this->dateUpd;
    }

    public function setDateAdd(DateTime $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function setDateUpd(DateTime $dateUpd): self
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps(): void
    {
        $this->setDateUpd(new DateTime());

        if (! $this->getDateAdd()) {
            $this->setDateAdd(new DateTime());
        }
    }
}

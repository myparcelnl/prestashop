<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Concern;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;

/**
 * @see EntityWithTimestampsInterface
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

    public function getDateAdd(): ?DateTime
    {
        return $this->dateAdd;
    }

    public function getDateUpd(): ?DateTime
    {
        return $this->dateUpd;
    }

    public function setDateAdd(DateTime $dateAdd): EntityWithTimestampsInterface
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function setDateUpd(DateTime $dateUpd): EntityWithTimestampsInterface
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    public function updateTimestamps(): void
    {
        $this->setDateUpd(new DateTime());

        if (null === $this->getDateAdd()) {
            $this->setDateAdd(new DateTime());
        }
    }
}

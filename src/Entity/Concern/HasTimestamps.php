<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Concern;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;

/**
 * @see \MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface
 * @see \MyParcelNL\PrestaShop\Entity\Concern\HasCreatedTimestamps
 */
trait HasTimestamps
{
    use HasCreatedTimestamps;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;

    public function getDateUpd(): ?DateTime
    {
        return $this->dateUpd;
    }

    public function setDateUpd(DateTime $dateUpd): EntityWithTimestampsInterface
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    public function updateTimestamps(): void
    {
        $this->setDateUpd(new DateTime());

        if ($this->getDateAdd() !== null) {
            return;
        }

        $this->setDateAdd(new DateTime());
    }
}

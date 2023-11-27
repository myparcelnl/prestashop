<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Concern;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithCreatedTimestampsInterface;

/**
 * @see \MyParcelNL\PrestaShop\Entity\Contract\EntityWithCreatedTimestampsInterface
 */
trait HasCreatedTimestamps
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    public function getDateAdd(): ?DateTime
    {
        return $this->dateAdd;
    }

    public function setDateAdd(DateTime $dateAdd): EntityWithCreatedTimestampsInterface
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function updateTimestamps(): void
    {
        if ($this->getDateAdd() !== null) {
            return;
        }

        $this->setDateAdd(new DateTime());
    }
}

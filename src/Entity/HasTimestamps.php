<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Concern namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 * @see  \MyParcelNL\PrestaShop\Entity\EntityWithTimestampsInterface
 * @see  \MyParcelNL\PrestaShop\Entity\HasCreatedTimestamps
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

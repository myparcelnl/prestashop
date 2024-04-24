<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Concern namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 * @see  \MyParcelNL\PrestaShop\Entity\EntityWithCreatedTimestampsInterface
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

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use DateTime;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Contract namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 */
interface EntityWithCreatedTimestampsInterface extends EntityInterface
{
    public function getDateAdd(): ?DateTime;

    public function setDateAdd(DateTime $dateAdd): self;

    public function updateTimestamps(): void;
}

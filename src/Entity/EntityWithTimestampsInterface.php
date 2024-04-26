<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use DateTime;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Contract namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 */
interface EntityWithTimestampsInterface extends EntityWithCreatedTimestampsInterface
{
    public function getDateUpd(): ?DateTime;

    public function setDateUpd(DateTime $dateUpd): self;
}

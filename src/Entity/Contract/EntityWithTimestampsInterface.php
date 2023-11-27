<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Contract;

use DateTime;

interface EntityWithTimestampsInterface extends EntityWithCreatedTimestampsInterface
{
    public function getDateUpd(): ?DateTime;

    public function setDateUpd(DateTime $dateUpd): self;
}

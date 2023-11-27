<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Contract;

use DateTime;

interface EntityWithCreatedTimestampsInterface extends EntityInterface
{
    public function getDateAdd(): ?DateTime;

    public function setDateAdd(DateTime $dateAdd): self;

    public function updateTimestamps(): void;
}

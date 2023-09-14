<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Contract;

use DateTime;

interface EntityWithTimestampsInterface extends EntityInterface
{
    public function getDateAdd(): ?DateTime;

    public function getDateUpd(): ?DateTime;

    public function setDateAdd(DateTime $dateAdd): self;

    public function setDateUpd(DateTime $dateUpd): self;

    public function updateTimestamps(): void;
}

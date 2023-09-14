<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Entity\Contract\EntityInterface;

abstract class AbstractEntity implements EntityInterface
{
    public function __construct() {}
}

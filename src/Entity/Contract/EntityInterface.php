<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

interface EntityInterface extends Arrayable
{
    public function __construct();

    /**
     * @return string
     */
    public static function getTable(): string;

    /**
     * @return int
     */
    public function getId(): int;
}

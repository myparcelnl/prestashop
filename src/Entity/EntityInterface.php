<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

/**
 * @TODO Move to \MyParcelNL\PrestaShop\Entity\Contract namespace when possible.
 * @see  https://github.com/myparcelnl/prestashop/issues/242
 */
interface EntityInterface extends Arrayable
{
    public function __construct();

    /**
     * @return string
     */
    public static function getTable(): string;
}


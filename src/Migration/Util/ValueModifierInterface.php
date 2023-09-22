<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

interface ValueModifierInterface
{
    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    public function modify($value);
}

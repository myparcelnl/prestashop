<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

interface DataMigratorInterface
{
    /**
     * @param  string $key
     * @param  array  $data
     * @param  mixed  $default
     *
     * @return mixed|null
     */
    public function getValue(string $key, array $data, $default = null);

    /**
     * @param  array|\MyParcelNL\Pdk\Base\Support\Collection $input
     * @param  \Iterator<MigratableValue>|MigratableValue[]  $map
     *
     * @return array
     */
    public function transform($input, $map): array;
}

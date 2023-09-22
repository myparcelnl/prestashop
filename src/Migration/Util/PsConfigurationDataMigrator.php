<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

final class PsConfigurationDataMigrator extends DataMigrator
{
    /**
     * @param  string $key
     * @param  array  $data
     * @param         $default
     *
     * @return mixed|null
     */
    public function getValue(string $key, array $data, $default = null)
    {
        foreach ($data as $value) {
            if ($value['name'] !== $key) {
                continue;
            }

            return $value['value'] ?? $default;
        }
    }
}

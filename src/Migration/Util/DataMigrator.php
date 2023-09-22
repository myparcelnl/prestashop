<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use Iterator;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;

class DataMigrator implements DataMigratorInterface
{
    /**
     * @param  string $key
     * @param  array  $data
     * @param  mixed  $default
     *
     * @return mixed|null
     */
    public function getValue(string $key, array $data, $default = null)
    {
        return Arr::get($data, $key, $default);
    }

    /**
     * @param  array|\MyParcelNL\Pdk\Base\Support\Collection $input
     * @param  Iterator<MigratableValue>|MigratableValue[]   $map
     *
     * @return array
     */
    public function transform($input, $map): array
    {
        if ($input instanceof Collection) {
            $input = $input->toArray();
        }

        $newSettings = [];

        /** @var \MyParcelNL\PrestaShop\Migration\Util\MigratableValue $migratable */
        foreach ($map as $migratable) {
            $value = $this->getValue($migratable->getSource(), $input);

            Arr::set($newSettings, $migratable->getTarget(), $migratable->modify($value));
        }

        return $newSettings;
    }
}

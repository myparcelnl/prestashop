<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Arr;
use ObjectModel;
use Zone;

abstract class MockPsCarrier extends ObjectModel
{
    public const PS_CARRIERS_ONLY                           = 1;
    public const CARRIERS_MODULE                            = 2;
    public const CARRIERS_MODULE_NEED_RANGE                 = 3;
    public const PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE = 4;
    public const ALL_CARRIERS                               = 5;

    public $attributes = [
        'zones' => [],
    ];

    /**
     * @param  int $idZone
     *
     * @return bool
     */
    public function addZone(int $idZone): bool
    {
        $this->attributes['zones'][] = $idZone;

        return true;
    }

    /**
     * @param  int $idZone
     *
     * @return bool
     */
    public function deleteZone(int $idZone): bool
    {
        $this->attributes['zones'] = Arr::where(
            $this->attributes['zones'],
            static function (int $zoneId) use ($idZone) {
                return $zoneId !== $idZone;
            }
        );

        return true;
    }

    /**
     * @return array<Zone>
     */
    public function getZones(): array
    {
        return Arr::where(Zone::getZones(), function (array $zone) {
            return in_array($zone['id_zone'], $this->attributes['zones'], true);
        });
    }
}

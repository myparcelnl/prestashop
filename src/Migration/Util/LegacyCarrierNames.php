<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

/**
 * Legacy carrier names as older plugin versions stored them, with their V2 names from the SDK.
 */
final class LegacyCarrierNames
{
    /**
     * Map each legacy carrier name to its V2 name. Carriers without a V2 name are left out.
     *
     * @return array<string, string>
     */
    public static function toV2Map(): array
    {
        $map = [];

        foreach (ApiMapperService::forCarrier()->allRows() as $row) {
            $legacyName = $row[ApiMapperService::COLUMN_LEGACY_NAME];
            $v2Name     = $row[ApiMapperService::COLUMN_V2_NAME];

            if (null !== $legacyName && null !== $v2Name) {
                $map[$legacyName] = $v2Name;
            }
        }

        return $map;
    }
}

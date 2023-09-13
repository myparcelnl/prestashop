<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\ShippingMethod\Repository;

use Carrier;
use Context;
use MyParcelNL\Pdk\App\ShippingMethod\Collection\PdkShippingMethodCollection;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Base\Support\Collection;

final class PsShippingMethodRepository extends Repository implements PdkShippingMethodRepositoryInterface
{
    public function all(): PdkShippingMethodCollection
    {
        $lang     = Context::getContext()->language->id;
        $carriers = Carrier::getCarriers($lang, true, false, false, null, Carrier::ALL_CARRIERS);

        $shippingMethods = (new Collection($carriers))
            ->filter(static function (array $shippingMethod) {
                return 1 === $shippingMethod['active'];
            })
            ->map(static function (array $shippingMethod) {
                return [
                    'id'   => $shippingMethod['id_reference'],
                    'name' => $shippingMethod['name'],
                ];
            });

        return new PdkShippingMethodCollection($shippingMethods);
    }
}

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Adapter;

use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

class ShipmentOptionsFromOrderGridAdapter extends ShipmentOptions
{
    public function __construct(?array $data = null)
    {
        $options = $data ?? [];

        parent::__construct([
            'ageCheck'         => $this->boolOrNull($options['age_check']),
            'insurance'        => $options['insurance'],
            'labelDescription' => null,
            'largeFormat'      => $this->boolOrNull($options['large_format']),
            'onlyRecipient'    => $this->boolOrNull($options['only_recipient']),
            'return'           => $this->boolOrNull($options['return']),
            'sameDayDelivery'  => null,
            'signature'        => $this->boolOrNull($options['signature']),
        ]);
    }

    /**
     * @param $key
     *
     * @return null|bool
     */
    private function boolOrNull($key): ?bool
    {
        return $key ? (bool) $key : null;
    }
}

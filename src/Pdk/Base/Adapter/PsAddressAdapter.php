<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Adapter;

use Address;
use Country;
use MyParcelNL\Pdk\Base\Model\ContactDetails;

final class PsAddressAdapter
{
    /**
     * @var null|\Context
     */
    private $context;

    public function __construct()
    {
        $this->context = \Context::getContext();
    }

    /**
     * @param  \Address|int|null $address
     *
     * @return \MyParcelNL\Pdk\Base\Model\ContactDetails
     */
    public function fromAddress($address): ContactDetails
    {
        if (! $address instanceof Address) {
            $address = new Address((int) $address);
        }

        return $this->getContactDetails($address);
    }

    /**
     * @param  \Address $address
     *
     * @return \MyParcelNL\Pdk\Base\Model\ContactDetails
     */
    private function getContactDetails(Address $address): ContactDetails
    {
        return new ContactDetails([
            'boxNumber'            => null,
            'cc'                   => Country::getIsoById($address->id_country),
            'city'                 => $address->city,
            'fullStreet'           => $address->address1,
            'number'               => null,
            'numberSuffix'         => null,
            'postalCode'           => $address->postcode,
            'region'               => null,
            'state'                => null,
            'street'               => null,
            'streetAdditionalInfo' => null,
            'person'               => sprintf("%s %s", $address->firstname, $address->lastname),
            'email'                => $this->context ? $this->context->customer->email : null,
            'phone'                => $address->phone,
        ]);
    }
}

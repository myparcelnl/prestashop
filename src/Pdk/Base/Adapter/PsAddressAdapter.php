<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Adapter;

use Address;
use Country;
use Customer;
use MyParcelNL\Pdk\Facade\Platform;
use Order;
use State;

final class PsAddressAdapter
{
    public const ADDRESS_TYPE_SHIPPING = 'shipping';
    public const ADDRESS_TYPE_BILLING  = 'billing';

    /**
     * @param  \Address|int|null $address
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function fromAddress($address): array
    {
        if (! $address instanceof Address) {
            $address = new Address((int) $address);
        }

        return $this->createFromAddress($address);
    }

    /**
     * @param  int|string|\Order $order
     * @param  string            $addressType
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function fromOrder($order, string $addressType = self::ADDRESS_TYPE_SHIPPING): array
    {
        if (! $order instanceof Order) {
            $order = new Order((int) $order);
        }

        $addressId = $addressType === self::ADDRESS_TYPE_SHIPPING
            ? $order->id_address_delivery
            : $order->id_address_invoice;

        $address  = new Address($addressId);
        $customer = new Customer($order->id_customer);

        return $this->createFromCustomer($customer) + $this->createFromAddress($address);
    }

    /**
     * @param  \Address $address
     *
     * @return array
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function createFromAddress(Address $address): array
    {
        $country = new Country($address->id_country);

        return [
            'cc'         => $country->iso_code,
            'city'       => $address->city,
            'address1'   => $address->address1,
            'address2'   => $address->address2,
            'postalCode' => $address->postcode,
            'person'     => trim(sprintf('%s %s', $address->firstname, $address->lastname)),
            'phone'      => $address->phone,
            'region'     => $country->iso_code === Platform::get('localCountry')
                ? null
                : (new State($address->id_state))->name,
        ];
    }

    /**
     * @param  \Customer $customer
     *
     * @return string[]
     */
    private function createFromCustomer(Customer $customer): array
    {
        return [
            'person' => trim("$customer->firstname $customer->lastname"),
            'email'  => $customer->email,
        ];
    }
}

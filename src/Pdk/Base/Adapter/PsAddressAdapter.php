<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Adapter;

use Address;
use Country;
use Customer;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use Order;
use State;

final class PsAddressAdapter
{
    public const ADDRESS_TYPE_SHIPPING = 'shipping';
    public const ADDRESS_TYPE_BILLING  = 'billing';

    private PsObjectModelServiceInterface $psObjectModelService;

    /**
     * @param  \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface $psObjectModelService
     */
    public function __construct(PsObjectModelServiceInterface $psObjectModelService)
    {
        $this->psObjectModelService = $psObjectModelService;
    }

    /**
     * @param  \Address|int|null $address
     *
     * @return array
     */
    public function fromAddress($address): array
    {
        $model = $this->psObjectModelService->getWithFallback(Address::class, $address);

        return $this->createFromAddress($model);
    }

    /**
     * @param  int|string|\Order $order
     * @param  string            $addressType
     *
     * @return array
     */
    public function fromOrder($order, string $addressType = self::ADDRESS_TYPE_SHIPPING): array
    {
        $orderModel = $this->psObjectModelService->getWithFallback(Order::class, $order);
        $addressId  = $addressType === self::ADDRESS_TYPE_SHIPPING
            ? $order->id_address_delivery
            : $order->id_address_invoice;

        $address  = $this->psObjectModelService->getWithFallback(Address::class, $addressId);
        $customer = $this->psObjectModelService->getWithFallback(Customer::class, $orderModel->id_customer);

        return array_replace($this->createFromCustomer($customer), $this->createFromAddress($address));
    }

    /**
     * @param  \Address $address
     *
     * @return array
     */
    private function createFromAddress(Address $address): array
    {
        $country = $this->psObjectModelService->getWithFallback(Country::class, $address->id_country);
        $state   = $this->psObjectModelService->getWithFallback(State::class, $address->id_state);

        return array_merge(
            [
                'cc'         => $country->iso_code,
                'city'       => $address->city,
                'address1'   => $address->address1,
                'address2'   => $address->address2,
                'postalCode' => $address->postcode,
                'person'     => trim(sprintf('%s %s', $address->firstname, $address->lastname)),
                'phone'      => $address->phone,
            ],
            $state ? [
                'region' => $state->name,
                'state'  => $state->iso_code,
            ] : []
        );
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

<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCarrier;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCurrency;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCustomer;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithGuest;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithShopGroup;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithTimestamps;
use PrestaShop\PrestaShop\Adapter\AddressFactory;

/**
 * @method $this withIdAddressDelivery(int $idAddressDelivery)
 * @method $this withIdAddressInvoice(int $idAddressInvoice)
 * @method $this withRecyclable(bool $recyclable)
 * @method $this withGift(bool $gift)
 * @method $this withGiftMessage(string $giftMessage)
 * @method $this withMobileTheme(bool $mobileTheme)
 * @method $this withSecureKey(string $secureKey)
 * @method $this withCheckedTos(bool $checkedTos)
 * @method $this withPictures(array $pictures)
 * @method $this withTextFields(array $textFields)
 * @method $this withDeliveryOption(string $deliveryOption)
 * @method $this withAllowSeperatedPackage(bool $allowSeperatedPackage)
 * @extends AbstractPsObjectModelFactory<Cart>
 * @see \CartCore
 */
final class CartFactory extends AbstractPsObjectModelFactory implements WithShopGroup, WithLang, WithCurrency,
                                                                        WithCustomer, WithCarrier, WithTimestamps,
                                                                        WithGuest
{
    /**
     * @param  int|Address|AddressFactory $input
     * @param  array                      $attributes
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException,
     */
    public function withAddressDelivery($input, array $attributes = []): self
    {
        return $this->withRelation(Address::class, 'address_delivery', $input, $attributes);
    }

    /**
     * @param  int|Address|AddressFactory $input
     * @param  array                      $attributes
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public function withAddressInvoice($input, array $attributes = []): self
    {
        return $this->withRelation(Address::class, 'address_invoice', $input, $attributes);
    }

    protected function getObjectModelClass(): string
    {
        return Cart::class;
    }
}

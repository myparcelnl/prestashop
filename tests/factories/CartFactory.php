<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCarrier;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCurrency;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCustomer;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShopGroup;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithTimestamps;

/**
 * @method $this withIdAddressDelivery(int $idAddressDelivery)
 * @method $this withIdAddressInvoice(int $idAddressInvoice)
 * @method $this withIdGuest(int $idGuest)
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
 *
 */
final class CartFactory extends AbstractPsObjectModelFactory implements WithShopGroup, WithLang, WithCurrency,
                                                                        WithCustomer, WithCarrier, WithTimestamps
{
    protected function getObjectModelClass(): string
    {
        return Cart::class;
    }
}

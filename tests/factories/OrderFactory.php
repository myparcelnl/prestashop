<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCarrier;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCart;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCurrency;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithCustomer;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShop;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShopGroup;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithTimestamps;
use function MyParcelNL\PrestaShop\psFactory;

/**
 * @see \OrderCore
 * @method $this add()
 * @method $this withConversionRate(float $conversionRate)
 * @method $this withCurrentState(int $currentState)
 * @method $this withDeliveryDate(string $deliveryDate)
 * @method $this withDeliveryNumber(int $deliveryNumber)
 * @method $this withGift(bool $gift)
 * @method $this withGiftMessage(string $giftMessage)
 * @method $this withIdAddressDelivery(int $idAddressDelivery)
 * @method $this withIdAddressInvoice(int $idAddressInvoice)
 * @method $this withInvoiceDate(string $invoiceDate)
 * @method $this withInvoiceNumber(int $invoiceNumber)
 * @method $this withMobileTheme(bool $mobileTheme)
 * @method $this withModule(string $module)
 * @method $this withNote(string $note)
 * @method $this withPayment(string $payment)
 * @method $this withRecyclable(bool $recyclable)
 * @method $this withReference(string $reference)
 * @method $this withRoundMode(int $roundMode)
 * @method $this withRoundType(int $roundType)
 * @method $this withSecureKey(string $secureKey)
 * @method $this withTotalDiscounts(float $totalDiscounts)
 * @method $this withTotalDiscountsTaxExcl(float $totalDiscountsTaxExcl)
 * @method $this withTotalDiscountsTaxIncl(float $totalDiscountsTaxIncl)
 * @method $this withTotalPaid(float $totalPaid)
 * @method $this withTotalPaidReal(float $totalPaidReal)
 * @method $this withTotalPaidTaxExcl(float $totalPaidTaxExcl)
 * @method $this withTotalPaidTaxIncl(float $totalPaidTaxIncl)
 * @method $this withTotalProducts(float $totalProducts)
 * @method $this withTotalProductsWt(float $totalProductsWt)
 * @method $this withTotalShipping(float $totalShipping)
 * @method $this withTotalShippingTaxExcl(float $totalShippingTaxExcl)
 * @method $this withTotalShippingTaxIncl(float $totalShippingTaxIncl)
 * @method $this withTotalWrapping(float $totalWrapping)
 * @method $this withTotalWrappingTaxExcl(float $totalWrappingTaxExcl)
 * @method $this withTotalWrappingTaxIncl(float $totalWrappingTaxIncl)
 * @method $this withValid(bool $valid)
 */
final class OrderFactory extends AbstractPsObjectModelFactory implements WithShop, WithShopGroup, WithLang,
                                                                         WithCustomer, WithCurrency, WithCart,
                                                                         WithCarrier, WithTimestamps
{
    /**
     * @param  int|Address|AddressFactory $input
     * @param  array                      $attributes
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public function withAddressDelivery($input, array $attributes = []): self
    {
        return $this->withRelation('address_delivery', $input, $attributes, 'id_customer');
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
        return $this->withRelation('address_invoice', $input, $attributes, 'id_customer');
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withAddressDelivery(psFactory(Address::class, 1))
            ->withAddressInvoice(psFactory(Address::class, 2))
            ->withIdCarrier(1)
            ->withIdCart(1)
            ->withIdCurrency(1)
            ->withIdCustomer(1)
            ->withIdLang(1)
            ->withIdShop(1)
            ->withIdShopGroup(1);
    }

    protected function getObjectModelClass(): string
    {
        return Order::class;
    }
}



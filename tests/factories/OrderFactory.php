<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use function MyParcelNL\PrestaShop\psFactory;

/**
 * @see \OrderCore
 * @method self add()
 * @method self withCarrier(int|Carrier|CarrierFactory $carrier, array $attributes = [])
 * @method self withCart(int|Cart|CartFactory $cart, array $attributes = [])
 * @method self withConversionRate(float $conversionRate)
 * @method self withCurrency(int|Currency|CurrencyFactory $currency, array $attributes = [])
 * @method self withCurrentState(int $currentState)
 * @method self withCustomer(int|Customer|CustomerFactory $customer, array $attributes = [])
 * @method self withDateAdd(string $dateAdd)
 * @method self withDateUpd(string $dateUpd)
 * @method self withDeliveryDate(string $deliveryDate)
 * @method self withDeliveryNumber(int $deliveryNumber)
 * @method self withGift(bool $gift)
 * @method self withGiftMessage(string $giftMessage)
 * @method self withIdAddressDelivery(int $idAddressDelivery)
 * @method self withIdAddressInvoice(int $idAddressInvoice)
 * @method self withIdCarrier(int $id_carrier)
 * @method self withIdCart(int $id_cart)
 * @method self withIdCurrency(int $idCurrency)
 * @method self withIdCustomer(int $idCustomer)
 * @method self withIdLang(int $idLang)
 * @method self withIdShop(int $idShop)
 * @method self withIdShopGroup(int $idShopGroup)
 * @method self withInvoiceDate(string $invoiceDate)
 * @method self withInvoiceNumber(int $invoiceNumber)
 * @method self withLang(int|Lang|LangFactory $lang, array $attributes = [])
 * @method self withMobileTheme(bool $mobileTheme)
 * @method self withModule(string $module)
 * @method self withNote(string $note)
 * @method self withPayment(string $payment)
 * @method self withRecyclable(bool $recyclable)
 * @method self withReference(string $reference)
 * @method self withRoundMode(int $roundMode)
 * @method self withRoundType(int $roundType)
 * @method self withSecureKey(string $secureKey)
 * @method self withShop(int|Shop|ShopFactory $shop, array $attributes = [])
 * @method self withShopGroup(int|ShopGroup|ShopGroupFactory $shopGroup, array $attributes = [])
 * @method self withTotalDiscounts(float $totalDiscounts)
 * @method self withTotalDiscountsTaxExcl(float $totalDiscountsTaxExcl)
 * @method self withTotalDiscountsTaxIncl(float $totalDiscountsTaxIncl)
 * @method self withTotalPaid(float $totalPaid)
 * @method self withTotalPaidReal(float $totalPaidReal)
 * @method self withTotalPaidTaxExcl(float $totalPaidTaxExcl)
 * @method self withTotalPaidTaxIncl(float $totalPaidTaxIncl)
 * @method self withTotalProducts(float $totalProducts)
 * @method self withTotalProductsWt(float $totalProductsWt)
 * @method self withTotalShipping(float $totalShipping)
 * @method self withTotalShippingTaxExcl(float $totalShippingTaxExcl)
 * @method self withTotalShippingTaxIncl(float $totalShippingTaxIncl)
 * @method self withTotalWrapping(float $totalWrapping)
 * @method self withTotalWrappingTaxExcl(float $totalWrappingTaxExcl)
 * @method self withTotalWrappingTaxIncl(float $totalWrappingTaxIncl)
 * @method self withValid(bool $valid)
 */
final class OrderFactory extends AbstractPsObjectModelFactory
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



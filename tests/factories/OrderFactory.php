<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;

/**
 * @method self add()
 * @method self withCarrierTaxRate(float $carrierTaxRate)
 * @method self withConversionRate(float $conversionRate)
 * @method self withCurrentState(int $currentState)
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
 * @method self withMobileTheme(bool $mobileTheme)
 * @method self withModule(string $module)
 * @method self withNote(string $note)
 * @method self withPayment(string $payment)
 * @method self withRecyclable(bool $recyclable)
 * @method self withReference(string $reference)
 * @method self withRoundMode(int $roundMode)
 * @method self withRoundType(int $roundType)
 * @method self withSecureKey(string $secureKey)
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
     * @param  \Address|\AddressFactory $addressDelivery
     *
     * @return self
     */
    public function withAddressDelivery($addressDelivery): PsObjectModelFactoryInterface
    {
        return $this->withModel('addressDelivery', $addressDelivery);
    }

    /**
     * @param  \Address|\AddressFactory $addressInvoice
     *
     * @return self
     */
    public function withAddressInvoice($addressInvoice): PsObjectModelFactoryInterface
    {
        return $this->withModel('addressInvoice', $addressInvoice);
    }

    /**
     * @param  Carrier|CarrierFactory $carrier
     *
     * @return self
     */
    public function withCarrier($carrier): PsObjectModelFactoryInterface
    {
        return $this->withModel('carrier', $carrier);
    }

    /**
     * @param  Cart|CartFactory $cart
     *
     * @return self
     */
    public function withCart($cart): PsObjectModelFactoryInterface
    {
        return $this->withModel('cart', $cart);
    }

    /**
     * @param  Currency|CurrencyFactory $currency
     *
     * @return self
     */
    public function withCurrency($currency): PsObjectModelFactoryInterface
    {
        return $this->withModel('currency', $currency);
    }

    /**
     * @param  Customer|CustomerFactory $customer
     *
     * @return self
     */
    public function withCustomer($customer): PsObjectModelFactoryInterface
    {
        return $this->withModel('customer', $customer);
    }

    /**
     * @param  Lang|LangFactory $lang
     *
     * @return self
     */
    public function withLang($lang): PsObjectModelFactoryInterface
    {
        return $this->withModel('lang', $lang);
    }

    /**
     * @param  Shop|ShopFactory $shop
     *
     * @return self
     */
    public function withShop($shop): PsObjectModelFactoryInterface
    {
        return $this->withModel('shop', $shop);
    }

    /**
     * @param  ShopGroup|ShopGroupFactory $shopGroup
     *
     * @return self
     */
    public function withShopGroup($shopGroup): PsObjectModelFactoryInterface
    {
        return $this->withModel('shopGroup', $shopGroup);
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withDateAdd(date('Y-m-d H:i:s'))
            ->withDateUpd(date('Y-m-d H:i:s'));
    }

    protected function getObjectModelClass(): string
    {
        return Order::class;
    }
}



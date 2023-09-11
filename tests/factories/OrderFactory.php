<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsClassFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsClassFactoryInterface;

/**
 * @method Order make()
 * @method OrderFactory withCarrierTaxRate(float $carrierTaxRate)
 * @method OrderFactory withConversionRate(float $conversionRate)
 * @method OrderFactory withCurrentState(int $currentState)
 * @method OrderFactory withDateAdd(string $dateAdd)
 * @method OrderFactory withDateUpd(string $dateUpd)
 * @method OrderFactory withDeliveryDate(string $deliveryDate)
 * @method OrderFactory withDeliveryNumber(int $deliveryNumber)
 * @method OrderFactory withGift(bool $gift)
 * @method OrderFactory withGiftMessage(string $giftMessage)
 * @method OrderFactory withId(int $id)
 * @method OrderFactory withIdAddressDelivery(int $idAddressDelivery)
 * @method OrderFactory withIdAddressInvoice(int $idAddressInvoice)
 * @method OrderFactory withIdCarrier(int $idCarrier)
 * @method OrderFactory withIdCart(int $idCart)
 * @method OrderFactory withIdCurrency(int $idCurrency)
 * @method OrderFactory withIdCustomer(int $idCustomer)
 * @method OrderFactory withIdLang(int $idLang)
 * @method OrderFactory withIdShop(int $idShop)
 * @method OrderFactory withIdShopGroup(int $idShopGroup)
 * @method OrderFactory withInvoiceDate(string $invoiceDate)
 * @method OrderFactory withInvoiceNumber(int $invoiceNumber)
 * @method OrderFactory withMobileTheme(bool $mobileTheme)
 * @method OrderFactory withModule(string $module)
 * @method OrderFactory withNote(string $note)
 * @method OrderFactory withPayment(string $payment)
 * @method OrderFactory withRecyclable(bool $recyclable)
 * @method OrderFactory withReference(string $reference)
 * @method OrderFactory withRoundMode(int $roundMode)
 * @method OrderFactory withRoundType(int $roundType)
 * @method OrderFactory withSecureKey(string $secureKey)
 * @method OrderFactory withTotalDiscounts(float $totalDiscounts)
 * @method OrderFactory withTotalDiscountsTaxExcl(float $totalDiscountsTaxExcl)
 * @method OrderFactory withTotalDiscountsTaxIncl(float $totalDiscountsTaxIncl)
 * @method OrderFactory withTotalPaid(float $totalPaid)
 * @method OrderFactory withTotalPaidReal(float $totalPaidReal)
 * @method OrderFactory withTotalPaidTaxExcl(float $totalPaidTaxExcl)
 * @method OrderFactory withTotalPaidTaxIncl(float $totalPaidTaxIncl)
 * @method OrderFactory withTotalProducts(float $totalProducts)
 * @method OrderFactory withTotalProductsWt(float $totalProductsWt)
 * @method OrderFactory withTotalShipping(float $totalShipping)
 * @method OrderFactory withTotalShippingTaxExcl(float $totalShippingTaxExcl)
 * @method OrderFactory withTotalShippingTaxIncl(float $totalShippingTaxIncl)
 * @method OrderFactory withTotalWrapping(float $totalWrapping)
 * @method OrderFactory withTotalWrappingTaxExcl(float $totalWrappingTaxExcl)
 * @method OrderFactory withTotalWrappingTaxIncl(float $totalWrappingTaxIncl)
 * @method OrderFactory withValid(bool $valid)
 */
final class OrderFactory extends AbstractPsClassFactory
{
    /**
     * @param  AddressDelivery|AddressDeliveryFactory $addressDelivery
     *
     * @return self
     */
    public function withAddressDelivery($addressDelivery): PsClassFactoryInterface
    {
        return $this->withModel('addressDelivery', $addressDelivery);
    }

    /**
     * @param  AddressInvoice|AddressInvoiceFactory $addressInvoice
     *
     * @return self
     */
    public function withAddressInvoice($addressInvoice): PsClassFactoryInterface
    {
        return $this->withModel('addressInvoice', $addressInvoice);
    }

    /**
     * @param  Carrier|CarrierFactory $carrier
     *
     * @return self
     */
    public function withCarrier($carrier): PsClassFactoryInterface
    {
        return $this->withModel('carrier', $carrier);
    }

    /**
     * @param  Cart|CartFactory $cart
     *
     * @return self
     */
    public function withCart($cart): PsClassFactoryInterface
    {
        return $this->withModel('cart', $cart);
    }

    /**
     * @param  Currency|CurrencyFactory $currency
     *
     * @return self
     */
    public function withCurrency($currency): PsClassFactoryInterface
    {
        return $this->withModel('currency', $currency);
    }

    /**
     * @param  Customer|CustomerFactory $customer
     *
     * @return self
     */
    public function withCustomer($customer): PsClassFactoryInterface
    {
        return $this->withModel('customer', $customer);
    }

    /**
     * @param  Lang|LangFactory $lang
     *
     * @return self
     */
    public function withLang($lang): PsClassFactoryInterface
    {
        return $this->withModel('lang', $lang);
    }

    /**
     * @param  Shop|ShopFactory $shop
     *
     * @return self
     */
    public function withShop($shop): PsClassFactoryInterface
    {
        return $this->withModel('shop', $shop);
    }

    /**
     * @param  ShopGroup|ShopGroupFactory $shopGroup
     *
     * @return self
     */
    public function withShopGroup($shopGroup): PsClassFactoryInterface
    {
        return $this->withModel('shopGroup', $shopGroup);
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withId($this->getNextId())
            ->withDateAdd(date('Y-m-d H:i:s'))
            ->withDateUpd(date('Y-m-d H:i:s'));
    }

    protected function getEntityClass(): string
    {
        return Order::class;
    }
}



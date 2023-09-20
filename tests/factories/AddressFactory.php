<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \AddressCore
 * @method self withAddress1(string $address1)
 * @method self withAddress2(string $address2)
 * @method self withAlias(string $alias)
 * @method self withCity(string $city)
 * @method self withCompany(string $company)
 * @method self withCountry(int|Country|CountryFactory $country)
 * @method self withCustomer(int|Customer|CustomerFactory $customer)
 * @method self withDni(string $dni)
 * @method self withFirstname(string $firstname)
 * @method self withIdCountry(int $idCountry)
 * @method self withIdCustomer(int $idCustomer)
 * @method self withIdManufacturer(int $idManufacturer)
 * @method self withIdState(int $idState)
 * @method self withIdSupplier(int $idSupplier)
 * @method self withIdWarehouse(int $idWarehouse)
 * @method self withLastname(string $lastname)
 * @method self withManufacturer(int|Manufacturer|ManufacturerFactory $manufacturer)
 * @method self withOther(string $other)
 * @method self withPhone(string $phone)
 * @method self withPhoneMobile(string $phoneMobile)
 * @method self withPostcode(string $postcode)
 * @method self withState(int|State|StateFactory $state)
 * @method self withSupplier(int|Supplier|SupplierFactory $supplier)
 * @method self withVatNumber(string $vatNumber)
 * @method self withWarehouse(int|Warehouse|WarehouseFactory $warehouse)
 */
final class AddressFactory extends AbstractPsObjectModelFactory
{
    /**
     * @return $this
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withCity('Hoofddorp')
            ->withPostcode('2132JE')
            ->withAddress1('Antareslaan 31')
            ->withFirstname('Meredith')
            ->withLastname('Mailbox')
            ->withCountry(1)
            ->withCustomer(1)
            ->withManufacturer(1)
            ->withState(1)
            ->withSupplier(1)
            ->withWarehouse(1);
    }

    protected function getObjectModelClass(): string
    {
        return Address::class;
    }
}


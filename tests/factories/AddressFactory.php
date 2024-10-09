<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCountry;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithCustomer;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithManufacturer;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithState;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithSupplier;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithWarehouse;

/**
 * @see \AddressCore
 * @method $this withAddress1(string $address1)
 * @method $this withAddress2(string $address2)
 * @method $this withAlias(string $alias)
 * @method $this withCity(string $city)
 * @method $this withCompany(string $company)
 * @method $this withDni(string $dni)
 * @method $this withFirstname(string $firstname)
 * @method $this withLastname(string $lastname)
 * @method $this withOther(string $other)
 * @method $this withPhone(string $phone)
 * @method $this withPhoneMobile(string $phoneMobile)
 * @method $this withPostcode(string $postcode)
 * @method $this withVatNumber(string $vatNumber)
 * @extends AbstractPsObjectModelFactory<Address>
 * @see \AddressCore
 */
final class AddressFactory extends AbstractPsObjectModelFactory implements WithCountry, WithCustomer, WithManufacturer,
                                                                           WithState, WithSupplier, WithWarehouse
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
            ->withCountry(Country::getByIso('NL'))
            ->withIdCustomer(1)
            ->withIdManufacturer(1)
            ->withIdState(1)
            ->withIdSupplier(1)
            ->withIdWarehouse(1);
    }

    protected function getObjectModelClass(): string
    {
        return Address::class;
    }
}


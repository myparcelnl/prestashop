<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \CustomerCore
 * @method $this withActive(bool $active)
 * @method $this withApe(string $ape)
 * @method $this withBirthday(string $birthday)
 * @method $this withCompany(string $company)
 * @method $this withDefaultGroup(int|Group|GroupFactory $defaultGroup)
 * @method $this withDeleted(bool $deleted)
 * @method $this withEmail(string $email)
 * @method $this withFirstname(string $firstname)
 * @method $this withGender(int|Gender|GenderFactory $gender)
 * @method $this withIdDefaultGroup(int $idDefaultGroup)
 * @method $this withIdGender(int $idGender)
 * @method $this withIdLang(int $idLang)
 * @method $this withIdRisk(int $idRisk)
 * @method $this withIdShop(int $idShop)
 * @method $this withIdShopGroup(int $idShopGroup)
 * @method $this withIpRegistrationNewsletter(string $ipRegistrationNewsletter)
 * @method $this withIsGuest(bool $isGuest)
 * @method $this withLang(int|Lang|LangFactory $lang)
 * @method $this withLastPasswdGen(string $lastPasswdGen)
 * @method $this withLastname(string $lastname)
 * @method $this withMaxPaymentDays(int $maxPaymentDays)
 * @method $this withNewsletter(bool $newsletter)
 * @method $this withNewsletterDateAdd(string $newsletterDateAdd)
 * @method $this withNote(string $note)
 * @method $this withOptin(bool $optin)
 * @method $this withOutstandingAllowAmount(float $outstandingAllowAmount)
 * @method $this withPasswd(string $passwd)
 * @method $this withRisk(int|Risk|RiskFactory $risk)
 * @method $this withSecureKey(string $secureKey)
 * @method $this withShop(int|Shop|ShopFactory $shop)
 * @method $this withShopGroup(int|ShopGroup|ShopGroupFactory $shopGroup)
 * @method $this withShowPublicPrices(int $showPublicPrices)
 * @method $this withSiret(string $siret)
 * @method $this withWebsite(string $website)
 */
final class CustomerFactory extends AbstractPsObjectModelFactory
{
    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withFirstname('Felicia')
            ->withLastname('Parcel')
            ->withDefaultGroup(1)
            ->withGender(1)
            ->withLang(1)
            ->withRisk(1)
            ->withShop(1)
            ->withShopGroup(1);
    }

    protected function getObjectModelClass(): string
    {
        return Customer::class;
    }
}

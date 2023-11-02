<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithGender;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithRisk;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShop;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithShopGroup;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\WithSoftDeletes;
use function MyParcelNL\PrestaShop\psFactory;

/**
 * @see \CustomerCore
 * @method $this withActive(bool $active)
 * @method $this withApe(string $ape)
 * @method $this withBirthday(string $birthday)
 * @method $this withCompany(string $company)
 * @method $this withDefaultGroup(int|Group|GroupFactory $defaultGroup)
 * @method $this withEmail(string $email)
 * @method $this withFirstname(string $firstname)
 * @method $this withIdDefaultGroup(int $idDefaultGroup)
 * @method $this withIpRegistrationNewsletter(string $ipRegistrationNewsletter)
 * @method $this withIsGuest(bool $isGuest)
 * @method $this withLastPasswdGen(string $lastPasswdGen)
 * @method $this withLastname(string $lastname)
 * @method $this withMaxPaymentDays(int $maxPaymentDays)
 * @method $this withNewsletter(bool $newsletter)
 * @method $this withNewsletterDateAdd(string $newsletterDateAdd)
 * @method $this withNote(string $note)
 * @method $this withOptin(bool $optin)
 * @method $this withOutstandingAllowAmount(float $outstandingAllowAmount)
 * @method $this withPasswd(string $passwd)
 * @method $this withSecureKey(string $secureKey)
 * @method $this withShowPublicPrices(int $showPublicPrices)
 * @method $this withSiret(string $siret)
 * @method $this withWebsite(string $website)
 */
final class CustomerFactory extends AbstractPsObjectModelFactory implements WithShop, WithShopGroup, WithLang, WithRisk,
                                                                            WithGender, WithSoftDeletes
{
    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function createDefault(): FactoryInterface
    {
        return parent::createDefault()
            ->withFirstname('Felicia')
            ->withLastname('Parcel')
            ->withDefaultGroup(psFactory(Group::class, 1))
            ->withIdGender(1)
            ->withIdLang(1)
            ->withIdRisk(1)
            ->withIdShop(1)
            ->withIdShopGroup(1);
    }

    protected function getObjectModelClass(): string
    {
        return Customer::class;
    }
}

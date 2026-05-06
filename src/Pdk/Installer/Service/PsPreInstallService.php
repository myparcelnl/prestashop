<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use Context;
use Currency;
use Doctrine\ORM\EntityManagerInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use MyParcelNL\PrestaShop\Pdk\Base\Service\DoctrineEntityRegistrar;

final class PsPreInstallService
{
    /**
     * Do some preparations that are missing in the installation flow of PrestaShop.
     */
    public function prepare(): void
    {
        $this->prepareEntityManager();
        $this->prepareContext();
    }

    /**
     * PrestaShop throws an error during install because context->currency is undefined.
     *
     * @return void
     * @todo See if this can be done in a better way (preferably not at all)
     */
    private function prepareContext(): void
    {
        /** @var \Context $context */
        $context = Context::getContext();

        /** @var \MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface $psObjectModelService */
        $psObjectModelService = Pdk::get(PsObjectModelServiceInterface::class);

        $context->currency = $context->currency ?? $psObjectModelService->create(Currency::class, 1);
    }

    private function prepareEntityManager(): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = Pdk::get('ps.entityManager');

        DoctrineEntityRegistrar::register($entityManager);
    }
}

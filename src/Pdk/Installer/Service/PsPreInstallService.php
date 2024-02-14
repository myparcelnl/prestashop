<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Installer\Service;

use Context;
use Currency;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Contract\PsObjectModelServiceInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class PsPreInstallService
{
    /**
     * Do some preparations that are missing in the installation flow of PrestaShop.
     *
     * @return void
     * @throws \Doctrine\Common\Annotations\AnnotationException
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

    /**
     * @return void
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    private function prepareEntityManager(): void
    {
        $appInfo = Pdk::getAppInfo();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = Pdk::get('ps.entityManager');

        $driverChain = $entityManager
            ->getConfiguration()
            ->getMetadataDriverImpl();

        $docParser = new DocParser();
        $reader    = new AnnotationReader($docParser);

        if (class_exists(PsrCachedReader::class)) {
            $reader = new PsrCachedReader($reader, new ArrayAdapter());
        }

        $driver = new AnnotationDriver($reader, ["{$appInfo->path}src/Entity"]);

        if ($driverChain instanceof MappingDriverChain) {
            $driverChain->addDriver($driver, 'MyParcelNL\PrestaShop\Entity');
        }
    }
}

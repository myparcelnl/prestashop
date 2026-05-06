<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class DoctrineEntityRegistrar
{
    private const ENTITY_NAMESPACE = 'MyParcelNL\PrestaShop\Entity';

    /**
     * Register the module's entity namespace with Doctrine's driver chain if it's missing.
     * This is needed when the Symfony container was compiled before the module was active.
     */
    public static function register(EntityManagerInterface $em): void
    {
        $driverChain = $em->getConfiguration()->getMetadataDriverImpl();

        if (! $driverChain instanceof MappingDriverChain) {
            return;
        }

        if (isset($driverChain->getDrivers()[self::ENTITY_NAMESPACE])) {
            return;
        }

        $entityDir = Pdk::getAppInfo()->path . 'src/Entity';
        $reader    = new AnnotationReader(new DocParser());

        if (class_exists(PsrCachedReader::class)) {
            $reader = new PsrCachedReader($reader, new ArrayAdapter());
        }

        $driverChain->addDriver(
            new AnnotationDriver($reader, [$entityDir]),
            self::ENTITY_NAMESPACE
        );
    }
}

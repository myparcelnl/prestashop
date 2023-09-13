<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApiService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockCarrierSchema;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockFileSystem;
use MyParcelNL\Pdk\Tests\Bootstrap\MockLanguageService;
use MyParcelNL\Pdk\Tests\Bootstrap\MockLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdk;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use Psr\Log\LoggerInterface;
use function DI\get;

final class MockPsPdkBootstrapper extends PsPdkBootstrapper implements StaticMockInterface
{
    /**
     * @var array
     */
    private static $config = [];

    /**
     * @return void
     */
    public static function reset(): void
    {
        self::setConfig([]);
        self::$initialized = false;
    }

    /**
     * @param  array $config
     *
     * @return void
     */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return array
     * @throws \PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException
     */
    protected function getAdditionalConfig(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): array {
        return array_replace(
            parent::getAdditionalConfig($name, $title, $version, $path, $url),
            [
                ApiServiceInterface::class      => get(MockApiService::class),
                CarrierSchema::class            => get(MockCarrierSchema::class),
                ClientAdapterInterface::class   => get(Guzzle7ClientAdapter::class),
                ConfigInterface::class          => get(MockConfig::class),
                FileSystemInterface::class      => get(MockFileSystem::class),
                LoggerInterface::class          => get(MockLogger::class),
                MemoryCacheStorage::class       => get(MockMemoryCacheStorage::class),
                PdkInterface::class             => get(MockPdk::class),
                StorageInterface::class         => get(MockMemoryCacheStorage::class),
                LanguageServiceInterface::class => get(MockLanguageService::class),
            ],
            self::$config
        );
    }
}

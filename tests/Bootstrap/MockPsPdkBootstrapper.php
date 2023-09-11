<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use Configuration;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockMyParcelNL;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsModule;
use function MyParcelNL\PrestaShop\psFactory;

final class MockPsPdkBootstrapper extends PsPdkBootstrapper implements StaticMockInterface
{
    /**
     * @var array
     */
    private static $config;

    /**
     * @param  array $config
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public static function create(array $config = []): void
    {
        self::setConfig(MockPdkConfig::create($config));

        MockPsModule::setInstance('pest', new MockMyParcelNL());

        psFactory(Configuration::class)->make();
    }

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
        return array_replace(parent::getAdditionalConfig($name, $title, $version, $path, $url), self::$config);
    }
}

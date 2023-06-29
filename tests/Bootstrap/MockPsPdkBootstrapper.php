<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Bootstrap;

use MyParcelNL\PrestaShop\Pdk\Base\PsPdkBootstrapper;

final class MockPsPdkBootstrapper extends PsPdkBootstrapper
{
    /**
     * @var array
     */
    private static $config;

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

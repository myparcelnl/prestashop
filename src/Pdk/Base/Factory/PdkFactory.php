<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Base\Factory;

use Configuration;
use Gett\MyparcelBE\Constant;
use MyParcelNL\Pdk\Api\MyParcelApiService;
use MyParcelNL\Pdk\Base\Factory\PdkFactory as RealPdkFactory;
use MyParcelNL\Pdk\Base\Pdk;

class PdkFactory extends RealPdkFactory
{
    /**
     * @var \MyParcelNL\Pdk\Base\Pdk
     */
    private static $pdk;

    /**
     * @param  array $config
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    public static function create(array $config): Pdk
    {
        if (! self::$pdk) {
            $config1   = self::getConfig($config);
            self::$pdk = RealPdkFactory::create($config1);
        }

        return self::$pdk;
    }

    /**
     * @param  array $config
     *
     * @return array
     */
    private static function getConfig(array $config): array
    {
        $apiKey = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);

        return array_merge(
            $config,
            [
                'api' => new MyParcelApiService([
                        'apiKey' => $apiKey,
                    ]
                ),
            ]
        );
    }
}

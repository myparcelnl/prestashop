<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Configuration\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

/**
 * PrestaShop >= 1.7
 */
final class Ps17PsConfigurationService implements PsConfigurationServiceInterface
{
    /**
     * @var \PrestaShop\PrestaShop\Adapter\Configuration
     */
    private $configurationService;

    public function __construct()
    {
        $this->configurationService = Pdk::get('ps.configuration');
    }

    /**
     * @param  string $key
     *
     * @return void
     * @throws \Exception
     */
    public function delete(string $key): void
    {
        $this->configurationService->remove($key);
    }

    /**
     * @param  string                                                                  $key
     * @param                                                                          $default
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return mixed
     */
    public function get(string $key, $default = null, ShopConstraint $shopConstraint = null)
    {
        $value = $this->configurationService->get($key, $default, $shopConstraint);

        if (is_string($value) && Str::startsWith($value, ['{', '['])) {
            return json_decode($value, true);
        }

        return $value;
    }

    /**
     * @param  string                                                                  $key
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return bool
     */
    public function has(string $key, ShopConstraint $shopConstraint = null): bool
    {
        return $this->configurationService->has($key, $shopConstraint);
    }

    /**
     * @param  string                                                                  $key
     * @param                                                                          $value
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return void
     * @throws \Exception
     */
    public function set(string $key, $value, ShopConstraint $shopConstraint = null): void
    {
        $storableValue = is_scalar($value) ? $value : json_encode($value);

        $this->configurationService->set($key, $storableValue, $shopConstraint);
    }
}

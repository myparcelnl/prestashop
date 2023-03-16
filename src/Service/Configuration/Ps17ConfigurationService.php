<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service\Configuration;

use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

/**
 * PrestaShop >= 1.7
 */
class Ps17ConfigurationService implements ConfigurationServiceInterface
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
     * @return null|array|mixed
     */
    public function get(string $key, $default = null, ShopConstraint $shopConstraint = null)
    {
        return json_decode($this->configurationService->get($key, $default, $shopConstraint) ?? '', true);
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
        $this->configurationService->set($key, json_encode($value), $shopConstraint);
    }
}

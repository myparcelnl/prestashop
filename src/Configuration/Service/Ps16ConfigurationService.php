<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Configuration\Service;

use MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId;

/**
 * PrestaShop < 1.7
 */
class Ps16ConfigurationService implements ConfigurationServiceInterface
{
    /**
     * @param  string $key
     *
     * @return void
     * @throws \Exception
     */
    public function delete(string $key): void
    {
        \Configuration::deleteByName($key);
    }

    /**
     * @param  string                                                                  $key
     * @param                                                                          $default
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return null|mixed
     */
    public function get(string $key, $default = null, ShopConstraint $shopConstraint = null)
    {
        return \Configuration::get($key, null, null, $this->getShopId($shopConstraint), $default) ?: null;
    }

    /**
     * @param  string                                                                  $key
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return bool
     */
    public function has(string $key, ShopConstraint $shopConstraint = null): bool
    {
        return \Configuration::hasKey($key, null, null, $this->getShopId($shopConstraint));
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
        \Configuration::set($key, $value, null, $this->getShopId($shopConstraint));
    }

    /**
     * @param  null|\PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint $shopConstraint
     *
     * @return null|\PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopId
     */
    private function getShopId(?ShopConstraint $shopConstraint): ?ShopId
    {
        return $shopConstraint ? $shopConstraint->getShopId() : null;
    }
}

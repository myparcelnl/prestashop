<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Configuration\Contract;

use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;

/**
 * This is an interface for PrestaShop's configuration service as they currently have multiple legacy implementations
 * and there will be a new one in PrestaShop 8.
 */
interface ConfigurationServiceInterface
{
    /**
     * Delete a configuration entry.
     *
     * @param  string $key
     *
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Retrieve a configuration entry.
     *
     * @param  string                                                                  $key
     * @param                                                                          $default
     * @param  \PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint|null $shopConstraint
     *
     * @return mixed
     */
    public function get(string $key, $default = null, ShopConstraint $shopConstraint = null);

    /**
     * Check if a configuration entry exists
     *
     * @param  string                                                                  $key
     * @param  null|\PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint $shopConstraint
     *
     * @return bool
     */
    public function has(string $key, ShopConstraint $shopConstraint = null): bool;

    /**
     * Set a configuration entry to a new value.
     *
     * @param  string                                                                  $key
     * @param  mixed                                                                   $value
     * @param  null|\PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint $shopConstraint
     *
     * @return void
     */
    public function set(string $key, $value, ShopConstraint $shopConstraint = null): void;
}

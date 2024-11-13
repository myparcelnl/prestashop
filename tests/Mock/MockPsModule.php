<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use Context;
use DI\Container;
use Hook;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\PrestaShop\Tests\Bootstrap\Contract\StaticMockInterface;

abstract class MockPsModule extends BaseMock implements StaticMockInterface
{
    /**
     * @var string[]
     */
    protected static $hooks = [];

    /**
     * @var array<string, self>
     */
    protected static $instances = [];

    /**
     * @var \DI\Container
     */
    public $container;

    /**
     * @var \Context
     */
    public $context;

    public function __construct()
    {
        $this->context   = Context::getContext();
        $this->container = $this->context->container;

        $this->attributes['local_path'] = __DIR__ . '/../..';
    }

    /**
     * @param  string $name
     *
     * @return false|self
     * @noinspection ProperNullCoalescingOperatorUsageInspection
     */
    public static function getInstanceByName(string $name)
    {
        return static::$instances[$name] ?? false;
    }

    /**
     * @param  \Module $module
     *
     * @return bool|null
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @see          \ModuleCore::needUpgrade()
     */
    public static function needUpgrade($module)
    {
        return static::loadUpgradeVersionList($module->name, $module->version, $module->version);
    }

    public static function reset(): void
    {
        static::$instances = [];
    }

    /**
     * @param  string $name
     * @param  self   $instance
     *
     * @return void
     */
    public static function setInstance(string $name, MockPsModule $instance): void
    {
        static::$instances[$name] = $instance;
    }

    /**
     * @param  string $module_name
     * @param  string $module_version
     * @param  string $registered_version
     *
     * @return bool
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingParamTypeInspection
     * @see          \ModuleCore::loadUpgradeVersionList()
     */
    protected static function loadUpgradeVersionList($module_name, $module_version, $registered_version)
    {
        return true;
    }

    public function disable(): bool
    {
        return true;
    }

    public function enable(): bool
    {
        return true;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    public function install(): bool
    {
        return true;
    }

    /**
     * @see \Module::registerHook()
     */
    public function registerHook($hookName, $shopList = null): bool
    {
        $hooks = Utils::toArray($hookName);

        return array_reduce($hooks, static function (bool $carry, string $hook) {
            $instance       = new Hook();
            $instance->name = $hook;

            return $carry && MockPsObjectModels::add($instance);
        }, true);
    }

    public function uninstall(): bool
    {
        return true;
    }

    /**
     * @see \Module::unregisterHook()
     */
    public function unregisterHook($input, $shop_list = null): bool
    {
        return MockPsObjectModels::getByClass(Hook::class)
            ->filter(static function (Hook $hook) use ($input) {
                return is_numeric($input)
                    ? $hook->id === (int) $input
                    : $hook->name === $input;
            })
            ->reduce(static function (bool $carry, Hook $hook) {
                return $carry && $hook->delete();
            }, true);
    }
}

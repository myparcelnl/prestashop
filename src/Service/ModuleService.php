<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Module;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException;
use MyParcelNL\PrestaShop\Service\ModuleHookService;
use Throwable;

final class ModuleService
{
    private static bool $isRegisteringHooks = false;

    /**
     * @return \MyParcelNL
     */
    public function getInstance(): Module
    {
        return Pdk::get('moduleInstance');
    }

    /**
     * @param  \Module $module
     *
     * @return bool
     */
    public function install(Module $module): bool
    {
        try {
            Installer::install($module);
        } catch (Throwable $e) {
            Logger::error('Failed to install module', ['exception' => $e]);

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return Module::isEnabled($this->getInstance()->name);
    }

    /**
     * Register all hooks at install time
     * @return void
     * @throws \MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException
     */
    public function registerHooks(): void
    {
        /**
         * registerHooks can be called multiple times during one request / install.
         * However: this throws a ‘key already exists’ / ‘duplicate key’ exception from the database.
         * Ensure we run this only once per request.
         */
        if (self::$isRegisteringHooks) {
            return;
        }
        self::$isRegisteringHooks = true;

        $instance = $this->getInstance();

        foreach (Pdk::get('moduleHooks') as $hook) {
            if ($instance->registerHook($hook)) {
                Logger::debug("Hook $hook registered");
                continue;
            }

            throw new InstallationException(sprintf('Hook %s could not be registered', $hook));
        }
    }

    /**
     * Provide a method to reset the registering hooks flag for testing purposes.
     * @return void
     */
    public static function resetRegisteringHooks(): void
    {
        self::$isRegisteringHooks = false;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\PrestaShop\Pdk\Installer\Exception\InstallationException;
use Module;

final class ModuleHookService
{
    /**
     * Get ALL hooks that should be registered at install time
     * PrestaShop can't dynamically register hooks, so we register them all upfront
     * and make the CONTENT conditional in the hook methods themselves
     * 
     * IMPORTANT: Only include hooks that actually have corresponding hook methods!
     */
    private function getAllHooks(): array
    {
        return [
            // Admin hooks - always needed (these have corresponding methods in traits)
            'displayBackOfficeHeader',      // HasPdkScriptHooks
            'displayBackOfficeFooter',      // HasPdkRenderHooks - PDK context injection (critical!)
            'displayAdminAfterHeader',      // HasPdkRenderHooks - Admin notifications and plugin settings
            'displayAdminEndContent',       // HasPdkRenderHooks - PDK init script
            
            // Frontend hooks - only include if methods exist
            'displayHeader',                // HasPdkCheckoutHooks
            'displayOrderConfirmation',     // HasPdkCheckoutDeliveryOptionsHooks
            'displayAdminOrderLeft',        // HasPdkOrderHooks
            'displayAdminOrderMainBottom',  // HasPdkOrderHooks
            'displayAdminProductsExtra',    // HasPdkProductHooks
            'displayCarrierList',           // HasPsCarrierListHooks
            
            // Action hooks - only include if methods exist
            'actionOrderGridDefinitionModifier',     // HasPdkOrderGridHooks
            'actionOrderGridPresenterModifier',      // HasPdkOrderGridHooks
            'actionProductUpdate',           // HasPdkProductHooks
            'actionCarrierUpdate',           // HasPsCarrierUpdateHooks
        ];
    }

    /**
     * Register hooks at install time
     */
    public function registerHooks(): void
    {
        $instance = $this->getInstance();
        $hooksToRegister = $this->getAllHooks();
        
        Logger::debug('Registering hooks at install time', ['hookCount' => count($hooksToRegister)]);

        foreach ($hooksToRegister as $hook) {
            if ($instance->registerHook($hook)) {
                Logger::debug("Hook $hook registered successfully");
                continue;
            }

            throw new InstallationException(sprintf('Hook %s could not be registered', $hook));
        }
        
        Logger::info('All hooks registered successfully - content will be conditional based on API key presence');
    }

    /**
     * @return Module
     */
    private function getInstance(): Module
    {
        return Module::getInstanceByName(PdkBootstrapper::PLUGIN_NAMESPACE);
    }
}

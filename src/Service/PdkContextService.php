<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Facade\Pdk;
use Context;

final class PdkContextService extends ContextService
{
    /**
     * Check if we're on a MyParcel admin page
     * Only render PDK components on our own pages to avoid conflicts
     */
    public function shouldRenderPdkComponents(): bool
    {
        $context = Context::getContext();
        
        if (!isset($context->controller)) {
            return false;
        }

        $controller = $context->controller;
        $controllerName = get_class($controller);

        // Check if we're on our settings controller or configure page
        return strpos($controllerName, 'MyParcelNL') !== false
            || (isset($_GET['configure']) && $_GET['configure'] === 'myparcelnl')
            || (isset($_GET['controller']) && strpos($_GET['controller'], 'MyParcelNL') !== false);
    }
    
    /**
     * Check if we have an API key configured
     * Uses the same configuration key as the account repository
     */
    public function hasApiKey(): bool
    {
        // Use the same key pattern as PsPdkAccountRepository
        $configKey = Pdk::get('createSettingsKey')('data_account');
        $accountData = \Configuration::get($configKey);
        
        if (!$accountData) {
            return false;
        }
        
        // Account data is stored as JSON
        if (is_string($accountData)) {
            $decodedData = json_decode($accountData, true);
            return !empty($decodedData['apiKey'] ?? null);
        }
        
        // Or as array directly
        if (is_array($accountData)) {
            return !empty($accountData['apiKey'] ?? null);
        }
        
        return false;
    }
    
    /**
     * Render minimal boot context for API key entry
     * This allows the settings page to render even without an API key
     */
    public function renderMinimalBootContainer(): string
    {
        // Only render on our settings pages
        if (!$this->shouldRenderPdkComponents()) {
            return '';
        }

        $appInfo = Pdk::getAppInfo();
        
        // Create minimal context data that allows API key entry
        $minimalContext = [
            'global' => [
                'appInfo' => [
                    'name' => $appInfo->name,
                    'title' => $appInfo->title,
                    'version' => $appInfo->version,
                    'path' => $appInfo->path,
                    'url' => $this->getCurrentAdminUrl(),
                ],
                'baseUrl' => $this->getBackendBaseUrl(),
                'bootstrapId' => 'myparcel-pdk-boot',
                'endpoints' => [
                    'base' => Pdk::get('routeBackend'),
                    'routes' => [
                        'backend' => Pdk::get('routeBackend'),
                        'backendPdk' => 'pdk',
                        'frontend' => Pdk::get('routeFrontend'),
                    ],
                ],
                'eventPing' => 'myparcel_pdk_ping',
                'eventPong' => 'myparcel_pdk_pong',
                'language' => $this->getCurrentLocale(),
                'mode' => Pdk::getMode(),
                'platform' => [
                    'name' => 'prestashop',
                    'human' => 'PrestaShop',
                    'backofficeUrl' => $this->getCurrentAdminUrl(),
                    'supportUrl' => 'https://github.com/myparcelnl/prestashop',
                    'localCountry' => 'NL',
                    'defaultCarrier' => 'postnl',
                    'defaultCarrierId' => 1,
                ],
                'translations' => [],
            ],
            'dynamic' => [],
            'checkout' => null,
            'orderData' => null,
            'pluginSettingsView' => null,
            'productData' => null,
            'productSettingsView' => null,
        ];
        
        $contextJson = json_encode($minimalContext, JSON_UNESCAPED_SLASHES);

        return sprintf(
            '<span id="myparcel-pdk-boot" data-pdk-context="%s"></span>',
            htmlspecialchars($contextJson, ENT_QUOTES, 'UTF-8')
        );
    }
    
    /**
     * Get the backend base URL for API calls
     */
    private function getBackendBaseUrl(): string
    {
        $context = Context::getContext();
        $baseUrl = $context->shop->getBaseURL(true);
        
        // Add the REST API base path
        return rtrim($baseUrl, '/') . '/rest/' . Pdk::get('routeBackend');
    }

    /**
     * Get the current admin URL
     */
    private function getCurrentAdminUrl(): string
    {
        $context = Context::getContext();
        
        if (!$context->link) {
            return '';
        }

        try {
            return $context->link->getAdminLink('AdminModules', true, [], ['configure' => 'myparcelnl']);
        } catch (\Exception $e) {
            return $context->link->getAdminLink('AdminModules');
        }
    }

    /**
     * Get current locale
     */
    private function getCurrentLocale(): string
    {
        $context = Context::getContext();
        
        if (!$context->language) {
            return 'en';
        }

        // Convert PrestaShop locale to standard format
        $locale = $context->language->locale ?? $context->language->iso_code ?? 'en';
        
        // Convert from format like 'en-US' to 'en' if needed for consistency
        if (strpos($locale, '-') !== false) {
            return strtolower(substr($locale, 0, 2));
        }
        
        return strtolower($locale);
    }
}

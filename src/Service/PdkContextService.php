<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Module;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Facade\Logger;
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
        return Module::getInstanceByName('myparcelnl') instanceof \MyParcelNL;
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

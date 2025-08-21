<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Base;

use DI\Definition\Helper\FactoryDefinitionHelper;
use FileLogger;
use Module;
use MyParcelNL;
use MyParcelNL\Pdk\Base\PdkBootstrapper;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException;
use Configuration;
use PrestaShopBundle\Exception\InvalidModuleException;
use Psr\Log\LogLevel;
use ReflectionClass;
use ReflectionMethod;

use function DI\factory;
use function DI\value;

class PsPdkBootstrapper extends PdkBootstrapper
{
    /**
     * @param  string $name
     * @param  string $title
     * @param  string $version
     * @param  string $path
     * @param  string $url
     *
     * @return array
     * @throws \PrestaShop\PrestaShop\Core\Exception\ContainerNotFoundException
     */
    protected function getAdditionalConfig(
        string $name,
        string $title,
        string $version,
        string $path,
        string $url
    ): array {
        return array_replace(
            $this->getConfig($version, $name, $path),
            $this->resolvePrestaShopServices()
        );
    }

    /**
     * @param  string $version
     * @param  string $name
     * @param  string $path
     *
     * @return array
     */
    protected function getConfig(string $version, string $name, string $path): array
    {
        return [
            'userAgent' => value([
                'PrestaShop'          => _PS_VERSION_,
                'MyParcel-PrestaShop' => $version,
            ]),

            /**
             * The name of the tab we want to show the settings page under.
             */

            'sidebarParentClass' => value('AdminParentShipping'),

            /**
             * The MyParcel column on the Orders page.
             */

            'orderColumnBefore' => value('actions'),

            'orderColumnOptions' => value([
                'sortable'  => false,
                'clickable' => false,
            ]),

            /**
             * Logging
             */

            'logDirectory' => value(sprintf('%s/var/logs/%s', _PS_ROOT_DIR_, $name)),

            'logLevelFilenameMap' => value([
                LogLevel::DEBUG     => FileLogger::DEBUG,
                LogLevel::INFO      => FileLogger::INFO,
                LogLevel::NOTICE    => FileLogger::WARNING,
                LogLevel::WARNING   => FileLogger::WARNING,
                LogLevel::ERROR     => FileLogger::ERROR,
                LogLevel::CRITICAL  => FileLogger::ERROR,
                LogLevel::ALERT     => FileLogger::ERROR,
                LogLevel::EMERGENCY => FileLogger::ERROR,
            ]),

            /**
             * Carrier logos
             */

            'carrierLogosDirectory' => value(sprintf('%sprivate/carrier-logos/', $path)),

            'carrierLogoFileExtensions' => value(['.png', '.jpg']),

            /**
             * The symfony routes that are used by the pdk. Must match the routes in config/routes.yml.
             *
             * @see config/routes.yml
             */

            'routeNameFrontend' => value('myparcelnl_frontend'),
            'routeNamePdk'      => value('myparcelnl_pdk'),
            'routeNameSettings' => value('myparcelnl_settings'),
            'routeNameWebhook'  => value('myparcelnl_webhook'),

            'legacyControllerSettings' => value('MyParcelNLAdminSettings'),

            'updateAccountModeUninstall' => value('uninstall'),

            /**
             * @TODO: Move to pdk
             */
            'countryCodesZoneRow'        => factory(function () {
                $nonRowCountries = array_merge(CountryCodes::EU_COUNTRIES, CountryCodes::UNIQUE_COUNTRIES);

                return array_diff(CountryCodes::ALL, $nonRowCountries);
            }),

            /**
             * Settings that are not available in the module.
             */

            'disabledSettings' => value([
                CheckoutSettings::ID => [
                    CheckoutSettings::ALLOWED_SHIPPING_METHODS,
                    CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS,
                    CheckoutSettings::DELIVERY_OPTIONS_POSITION,
                    CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS,
                    CheckoutSettings::ENABLE_ADDRESS_WIDGET
                ],
            ]),

            'moduleInstance' => factory(static function (): Module {
                $name = Pdk::getAppInfo()->name;

                /** @var MyParcelNL|false $module */
                $module = Module::getInstanceByName($name);

                if (! $module) {
                    throw new InvalidModuleException("Failed to get module instance '$name'");
                }

                return $module;
            }),

            /**
             * Get hooks based on API key availability - conditional loading
             */
            'moduleHooks'    => factory(function () {
                // Check if we have an API key
                $apiKey = $this->getApiKey();
                
                if (!$apiKey) {
                    // Early initialization - only essential admin hooks for settings interface
                    return $this->getPluginInitHooks();
                }
                
                // Full initialization - all hooks via reflection (existing logic)
                $reflectionClass = new ReflectionClass(MyParcelNL::class);

                $hooks = (new Collection($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC)))
                    ->filter(function (ReflectionMethod $method) {
                        return Str::startsWith($method->getName(), 'hook');
                    })
                    ->map(function (ReflectionMethod $method) {
                        return lcfirst(preg_replace('/^hook/', '', $method->getName()));
                    })
                    ->values();

                return $hooks->toArray();
            }),
        ];
    }

    /**
     * @return array|FactoryDefinitionHelper[]
     * @throws ContainerNotFoundException
     */
    private function resolvePrestaShopServices(): array
    {
        return [
            'ps.version'   => value(_PS_VERSION_),
            'ps.container' => factory(function () {
                return Pdk::get('moduleInstance')
                    ->getContainer();
            }),

            'getPsService' => factory(function () {
                return static function (string $serviceName) {
                    /** @var \Psr\Container\ContainerInterface $container */
                    $container = Pdk::get('ps.container');

                    return $container->get($serviceName);
                };
            }),

            'ps.configuration' => factory(function () {
                return Pdk::get('getPsService')('prestashop.adapter.legacy.configuration');
            }),

            'ps.entityManager' => factory(function () {
                return Pdk::get('getPsService')('doctrine.orm.entity_manager');
            }),

            'ps.tabRepository' => factory(function () {
                return Pdk::get('getPsService')('prestashop.core.admin.tab.repository');
            }),

            'ps.twig' => factory(function () {
                return Pdk::get('getPsService')('twig');
            }),

        ];
    }

    /**
     * Get API key for early initialization check
     */
    private function getApiKey(): ?string
    {
        try {
            // First try current configuration keys that are likely to exist
            $candidates = [
                'myparcelnl_account',            // Current primary key
                'myparcelnl_data_account',       // Current data key
                "_myparcelnl_account",          // Legacy with underscore
            ];
            
            // Try the PDK namespace keys too (for future compatibility)
            try {
                $namespace = PdkBootstrapper::PLUGIN_NAMESPACE;
                $candidates = array_merge([
                    "_{$namespace}_account",        // New format with underscore
                    "{$namespace}_account",         // New format
                    "{$namespace}_data_account",    // New format data
                ], $candidates);
            } catch (\Throwable $e) {
                // If PLUGIN_NAMESPACE is not available yet, continue with legacy keys
            }
            
            foreach ($candidates as $key) {
                $value = Configuration::get($key);
                if (!$value) {
                    continue;
                }
                
                // Handle both JSON string and array formats
                if (is_string($value)) {
                    $data = json_decode($value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        continue; // Skip invalid JSON
                    }
                } else {
                    $data = (array) $value;
                }
                
                if (isset($data['apiKey']) && !empty($data['apiKey']) && is_string($data['apiKey'])) {
                    return trim($data['apiKey']);
                }
            }
            
            return null;
        } catch (\Throwable $e) {
            // Log the error but don't break the initialization
            if (class_exists('PrestaShopLogger')) {
                \PrestaShopLogger::addLog(
                    'MyParcel PDK: Error checking API key during bootstrap: ' . $e->getMessage(),
                    \PrestaShopLogger::LOG_SEVERITY_LEVEL_WARNING
                );
            }
            return null;
        }
    }

    /**
     * Get minimal hooks for plugin initialization (settings interface only)
     */
    private function getPluginInitHooks(): array
    {
        return [
            'displayBackOfficeHeader',    // For admin CSS/JS
            'displayAdminAfterHeader',    // For admin container
            'displayAdminEndContent',     // For admin footer scripts
            'actionAdminControllerSetMedia', // For settings page assets
        ];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL;
use MyParcelNL\Pdk\Facade\Logger;
use Configuration;

final class NamespaceMigrationService
{
    private const OLD_NAMESPACE = 'myparcelnl';
    private const NEW_NAMESPACE = MyParcelNL::MODULE_NAME;
    private const MIGRATION_COMPLETED_KEY = '_namespace_migration_completed';

    /**
     * Check if namespace migration has been completed
     */
    public function isMigrationCompleted(): bool
    {
        return (bool) Configuration::get(self::MIGRATION_COMPLETED_KEY);
    }

    /**
     * Perform the namespace migration from myparcelnl to myparcelcom
     * This migrates all configuration settings to the new namespace
     */
    public function migrate(): void
    {
        if (self::OLD_NAMESPACE === self::NEW_NAMESPACE || $this->isMigrationCompleted()) {
            Logger::debug('Namespace migration already completed, skipping');

            return;
        }

        Logger::info('Starting namespace migration from ' . self::OLD_NAMESPACE . ' to ' . self::NEW_NAMESPACE);

        $migrated = 0;

        $oldKey = self::OLD_NAMESPACE . '_';
        $newKey = self::NEW_NAMESPACE . '_';

        // Migrate standard configuration keys
        $migrated += $this->migrateKeys($oldKey, $newKey);

        // Migrate underscore-prefixed configuration keys (main settings)
        $migrated += $this->migrateKeys("_$oldKey", "_$newKey");

        // Mark migration as completed
        Configuration::updateValue(self::MIGRATION_COMPLETED_KEY, true);

        Logger::info("Namespace migration completed. Migrated {$migrated} configuration keys");
    }

    /**
     * @param  string $oldPrefix
     * @param  string $newPrefix
     *
     * @return int
     */
    private function migrateKeys(string $oldPrefix, string $newPrefix): int
    {
        $migrated = 0;

        // Get all configuration keys that start with the old prefix
        $oldKeys = $this->getConfigurationKeysWithPrefix($oldPrefix);

        foreach ($oldKeys as $oldKey) {
            $newKey = str_replace($oldPrefix, $newPrefix, $oldKey);
            $value  = Configuration::get($oldKey);

            // Only migrate if new key doesn't already exist
            if (false !== $value && false === Configuration::get($newKey)) {
                // Handle JSON values that might have embedded namespaces
                $migratedValue = $this->migrateJsonNamespaces($value);

                Configuration::updateValue($newKey, $migratedValue);
                Logger::debug("Migrated {$oldKey} -> {$newKey}");
                $migrated++;
            }
        }

        return $migrated;
    }

    /**
     * Migrate any embedded namespaces within JSON values
     */
    private function migrateJsonNamespaces($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        // Try to decode as JSON
        $decoded = json_decode($value, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            // Not JSON, just do string replacement
            return str_replace(self::OLD_NAMESPACE, self::NEW_NAMESPACE, $value);
        }

        // Recursively replace namespace in JSON structure
        $migrated = $this->replaceNamespaceRecursive($decoded);

        return json_encode($migrated);
    }

    /**
     * Recursively replace namespace in array/object structures
     */
    private function replaceNamespaceRecursive($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                // Replace in both key and value
                $newKey          = is_string($key) ? str_replace(self::OLD_NAMESPACE, self::NEW_NAMESPACE, $key) : $key;
                $result[$newKey] = $this->replaceNamespaceRecursive($value);
            }

            return $result;
        }

        if (is_string($data)) {
            return str_replace(self::OLD_NAMESPACE, self::NEW_NAMESPACE, $data);
        }

        return $data;
    }

    /**
     * Get configuration keys that start with a specific prefix
     * This is a simplified approach - in a real implementation you might want to
     * query the database directly for better performance
     */
    private function getConfigurationKeysWithPrefix(string $prefix): array
    {
        $keys = [];

        // Common configuration keys that we know exist
        $commonKeys = [
            $prefix . 'account',
            $prefix . 'settings',
            $prefix . 'carriers',
            $prefix . 'delivery_options',
            $prefix . 'checkout_settings',
            $prefix . 'order_settings',
            $prefix . 'product_settings',
            $prefix . 'shipping_settings',
            $prefix . 'webhook_settings',
        ];

        foreach ($commonKeys as $key) {
            if (false !== Configuration::get($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Get API key from either old or new namespace (for backwards compatibility)
     */
    public function getApiKey(): ?string
    {
        // Try new namespace first
        $newKey  = '_' . self::NEW_NAMESPACE . '_account';
        $newData = Configuration::get($newKey);

        if ($newData) {
            $decoded = json_decode($newData, true);
            if (isset($decoded['apiKey'])) {
                return $decoded['apiKey'];
            }
        }

        // Fallback to old namespace
        $oldKey  = '_' . self::OLD_NAMESPACE . '_account';
        $oldData = Configuration::get($oldKey);

        if ($oldData) {
            $decoded = json_decode($oldData, true);
            if (isset($decoded['apiKey'])) {
                return $decoded['apiKey'];
            }
        }

        return null;
    }
}

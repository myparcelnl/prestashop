<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Throwable;

/**
 * @mixin  \Module
 */
trait HasModuleUpgradeOverrides
{
    /**
     * @param $name
     * @param $version
     *
     * @return bool
     */
    public static function upgradeModuleVersion($name, $version): bool
    {
        // Leave the registered version behind while migrations still have work. canBeUpgraded()
        // compares it with the version on disk, so PrestaShop keeps offering the upgrade and the
        // merchant can run it again to finish. Bumping it here would hide the button and strand
        // whatever is left unconverted.
        //
        // There is no limit on the number of attempts, on purpose. A shop whose migration cannot
        // finish keeps being offered the upgrade, and every attempt logs the warning below. Giving up
        // after a few tries would hide the problem and leave the records half converted, which is the
        // outcome this guard exists to prevent.
        if (static::hasPendingMigrations()) {
            Logger::warning('Migrations are not finished, so the registered module version stays as it is.', [
                'module'  => $name,
                'version' => $version,
            ]);

            return false;
        }

        $result = parent::upgradeModuleVersion($name, $version);

        try {
            /** @var \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem */
            $fileSystem = Pdk::get(FileSystemInterface::class);
            $filename   = static::getUpgradeFileName();

            if ($fileSystem->fileExists($filename)) {
                $fileSystem->unlink($filename);
            }
        } catch (Throwable $e) {
            Logger::error("Failed to remove upgrade file: {$e->getMessage()}", [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);
        }

        return $result;
    }

    /**
     * @param  string $moduleName
     * @param  string $moduleVersion
     * @param  string $registeredVersion
     *
     * @return bool
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected static function loadUpgradeVersionList($moduleName, $moduleVersion, $registeredVersion)
    {
        try {
            // Trigger pdk setup to use facades
            new MyParcelNL();

            static::writeUpgradeFile();
        } catch (Throwable $e) {
            Logger::error("Failed to write upgrade file: {$e->getMessage()}", [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTrace(),
            ]);

            return false;
        }

        return parent::loadUpgradeVersionList($moduleName, $moduleVersion, $registeredVersion);
    }

    /**
     * When the module is upgraded, PrestaShop checks to see if upgrade files exist. We need every update ever to
     * trigger MyParcelModule::install(). So, whenever PrestaShop checks our module for upgrade files, write a new
     * upgrade file for the current version to trigger the install method.
     *
     * @return void
     */
    protected static function writeUpgradeFile(): void
    {
        /** @var \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem */
        $fileSystem = Pdk::get(FileSystemInterface::class);

        $fileSystem->mkdir(static::getUpgradeDir(), true);

        $content = '<?php function upgrade_module___VERSION__($module): bool { return \\MyParcelNL\\PrestaShop\\Facade\\MyParcelModule::install($module); }';

        $fileSystem->put(static::getUpgradeFileName(), strtr($content, [
            '__VERSION__' => str_replace(['.', '-', '+'], '_', static::getVersionFromComposer()),
        ]));
    }

    /**
     * Whether the installer still has migrations to run.
     *
     * Answers false when it cannot tell. An installer that stays silent must not keep the module
     * upgradable for ever, and a PDK without this check behaves as it always did.
     *
     * @return bool
     */
    private static function hasPendingMigrations(): bool
    {
        try {
            return (bool) Installer::hasPendingMigrations();
        } catch (Throwable $e) {
            Logger::error("Could not read pending migrations: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * @return string
     */
    private static function getUpgradeDir(): string
    {
        return sprintf('%s/../../upgrade', __DIR__);
    }

    /**
     * @return string
     */
    private static function getUpgradeFileName(): string
    {
        $upgradeDir = static::getUpgradeDir();
        $version    = str_replace(['-', '+'], '_', static::getVersionFromComposer());

        return sprintf('%s/upgrade-%s.php', $upgradeDir, $version);
    }

    /**
     * For some reason the cache is cleared halfway throughout the upgrade process when running it via the CLI.
     * PrestaShop then proceeds to throw errors because these properties are not set.
     *
     * @return array
     */
    public function runUpgradeModule(): array
    {
        $upgrade = &static::$modules_cache[$this->name]['upgrade'];

        $upgrade['success']             ??= false;
        $upgrade['available_upgrade']   ??= 0;
        $upgrade['number_upgraded']     ??= 0;
        $upgrade['number_upgrade_left'] ??= 0;
        $upgrade['upgrade_file_left']   ??= [];
        $upgrade['version_fail']        ??= 0;
        $upgrade['upgraded_from']       ??= 0;
        $upgrade['upgraded_to']         ??= 0;

        return parent::runUpgradeModule();
    }
}

<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

use MyParcelNL\PrestaShop\Controller\SettingsController;

/**
 * WARNING the moduleName and route MUST MATCH the plugin name as currently defined in myparcelnl.php
 *
 * @ModuleActivated(moduleName='myparcelnl', redirectRoute='myparcelnl_settings')
 */
final class MyParcelNLAdminSettingsController extends SettingsController { }

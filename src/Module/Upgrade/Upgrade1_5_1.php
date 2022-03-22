<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

use Gett\MyparcelBE\Module\Installer;
use Gett\MyparcelBE\Module\Uninstaller;

class Upgrade1_5_1 extends AbstractUpgrade
{
    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function upgrade(): void
    {
        $this->reinstallTabs();
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function reinstallTabs(): void
    {
        (new Uninstaller())->uninstallTabs();
        (new Installer())->installTabs();
    }
}

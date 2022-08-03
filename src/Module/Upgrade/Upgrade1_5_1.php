<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Upgrade;

use MyParcelNL\PrestaShop\Module\Installer;
use MyParcelNL\PrestaShop\Module\Uninstaller;
use PrestaShop\PrestaShop\Adapter\Entity\Tools;

class Upgrade1_5_1 extends AbstractUpgrade
{
    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function upgrade(): void
    {
        $this->clearSymfonyCache();
        $this->reinstallTabs();
    }

    /**
     * @return void
     */
    private function clearSymfonyCache(): void
    {
        Tools::clearSf2Cache();
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

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

defined('_PS_VERSION_') or die();

use Exception;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Module\Installer;
use Gett\MyparcelBE\Module\Uninstaller;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractUpgrade
{
    use HasInstance;

    /**
     * @var \Gett\MyparcelBE\Service\Platform\AbstractPlatformService
     */
    protected $platformService;

    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Db
     */
    protected $db;

    /**
     * @throws \Exception
     */
    final public function __construct()
    {
        $this->db              = Db::getInstance();
        $this->platformService = PlatformServiceFactory::create();
    }

    /**
     * @return void
     */
    abstract public function upgrade(): void;

    /**
     * Execute the upgrade.
     *
     * @return bool
     */
    final public function execute(): bool
    {
        try {
            $this->upgrade();
        } catch (Exception $e) {
            ApiLogger::addLog($e, ApiLogger::ERROR);
            return false;
        }

        return true;
    }
}

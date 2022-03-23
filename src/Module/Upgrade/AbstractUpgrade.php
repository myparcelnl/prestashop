<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Upgrade;

defined('_PS_VERSION_') or die();

use Exception;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Service\Concern\HasInstance;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractUpgrade
{
    use HasInstance;

    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Db
     */
    protected $db;

    /**
     * @var \Gett\MyparcelBE\Service\Platform\AbstractPlatformService
     */
    protected $platformService;

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
            ApiLogger::addLog(sprintf('%s] Attempting to execute upgrade', static::class), ApiLogger::INFO);
            $this->upgrade();
            ApiLogger::addLog(sprintf('%s] Successfully executed upgrade', static::class), ApiLogger::INFO);
        } catch (Exception $e) {
            ApiLogger::addLog($e, ApiLogger::ERROR);
            return false;
        }

        return true;
    }
}

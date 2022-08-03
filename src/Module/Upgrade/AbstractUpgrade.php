<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Upgrade;

defined('_PS_VERSION_') or die();

use Exception;
use MyParcelNL\PrestaShop\Service\Concern\HasInstance;
use MyParcelNL\PrestaShop\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use PrestaShop\PrestaShop\Adapter\Entity\Db;

abstract class AbstractUpgrade
{
    use HasInstance;

    /**
     * @var \PrestaShop\PrestaShop\Adapter\Entity\Db
     */
    protected $db;

    /**
     * @var \MyParcelNL\PrestaShop\Service\Platform\AbstractPlatformService
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
        $upgradeClass = static::class;

        try {
            DefaultLogger::debug('Attempting to execute upgrade', compact('upgradeClass'));
            $this->upgrade();
            DefaultLogger::debug('Successfully executed upgrade', compact('upgradeClass'));
        } catch (Exception $exception) {
            DefaultLogger::error($exception->getMessage(), compact('exception', 'upgradeClass'));
            return false;
        }

        return true;
    }
}

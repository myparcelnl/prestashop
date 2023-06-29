<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface;

class PdkSettingsRepository extends AbstractSettingsRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                           $storage
     * @param  \MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface $configurationService
     */
    public function __construct(StorageInterface $storage, ConfigurationServiceInterface $configurationService)
    {
        parent::__construct($storage);
        $this->configurationService = $configurationService;
    }

    /**
     * @param  string $namespace
     *
     * @return array|\MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel
     */
    public function getGroup(string $namespace)
    {
        return $this->configurationService->get($namespace);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    public function store(string $key, $value): void
    {
        if ($value === null) {
            Logger::debug("Deleting option $key");
            $this->configurationService->delete($key);
            return;
        }

        $this->configurationService->set($key, $value);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Base\Repository\Repository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\Sdk\src\Support\Str;

class PdkSettingsRepository extends Repository implements SettingsRepositoryInterface
{
    /**
     * @var \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                          $storage
     * @param  \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        StorageInterface              $storage,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage);
        $this->configurationService = $configurationService;
    }

    public function all(): Settings
    {
        return new Settings();
    }

    public function get(string $key)
    {
        return '';
    }

    /**
     * @param  string $namespace
     *
     * @return array|\MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel
     */
    public function getGroup(string $namespace)
    {
        return $this->configurationService->get($this->getOptionName($namespace));
    }

    public function storeSettings($settings): void
    {
        // TODO: Implement storeSettings() method.
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    protected function getOptionName(string $key): string
    {
        $appInfo = Pdk::getAppInfo();

        return strtr('_:plugin_:name', [
            ':plugin' => $appInfo['name'],
            ':name'   => Str::snake($key),
        ]);
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return void
     */
    protected function store(string $key, $value): void
    {
        $this->configurationService->set($this->getOptionName($key), $value);
    }
}

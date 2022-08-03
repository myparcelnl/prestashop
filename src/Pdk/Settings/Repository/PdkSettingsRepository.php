<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;
use MyParcelNL\Sdk\src\Support\Str;

class PdkSettingsRepository extends AbstractSettingsRepository
{
    protected const KEY_CONFIGURATION = ':module_:name';

    /**
     * @var \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\StorageInterface                                   $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface                            $api
     * @param  \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        StorageInterface              $storage,
        ApiServiceInterface           $api,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $api);
        $this->configurationService = $configurationService;
    }

    /**
     * @param  string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->retrieve($name, function () use ($name) {
            return $this->configurationService->get($this->getConfigurationKey($name));
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel $settingsModel
     *
     * @return void
     */
    public function store(AbstractSettingsModel $settingsModel): void
    {
        $id = $settingsModel->getId();

        foreach ($settingsModel->getAttributes() as $key => $value) {
            $this->configurationService->set($this->getConfigurationKey("$id.$key"), $value);
        }
    }

    /**
     * @param  string $name
     *
     * @return string
     */
    protected function getConfigurationKey(string $name): string
    {
        return strtr(self::KEY_CONFIGURATION, [
            ':module' => \MyParcelNL::MODULE_NAME,
            ':name'   => Str::snake(str_replace('.', '_', $name)),
        ]);
    }
}

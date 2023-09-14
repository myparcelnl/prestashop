<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Account\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface;

final class PsPdkAccountRepository extends AbstractPdkAccountRepository
{
    private const STORAGE_KEY_ACCOUNT = 'data_account';

    /**
     * @var \MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                           $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository                        $accountRepository
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface               $settingsRepository
     * @param  \MyParcelNL\PrestaShop\Configuration\Contract\ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        StorageInterface              $storage,
        AccountRepository             $accountRepository,
        SettingsRepositoryInterface   $settingsRepository,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $accountRepository, $settingsRepository);
        $this->configurationService = $configurationService;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function store(?Account $account): ?Account
    {
        $key = $this->getConfigurationKey();

        if (! $account) {
            $this->configurationService->delete($key);

            return $account;
        }

        $this->configurationService->set($key, $account->toStorableArray());

        return $this->save(self::STORAGE_KEY_ACCOUNT, $account);
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    protected function getFromStorage(): ?Account
    {
        return $this->retrieve(self::STORAGE_KEY_ACCOUNT, function () {
            $result = $this->configurationService->get($this->getConfigurationKey());

            return $result ? new Account($result) : null;
        });
    }

    /**
     * @return string
     */
    private function getConfigurationKey(): string
    {
        return Pdk::get('createSettingsKey')('data_account');
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\App\Account\Repository\AbstractPdkAccountRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;

/**
 * TODO: move 'account_data' to config
 */
class PdkAccountRepository extends AbstractPdkAccountRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                          $storage
     * @param  \MyParcelNL\Pdk\Account\Repository\AccountRepository                       $accountRepository
     * @param  \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        StorageInterface              $storage,
        AccountRepository             $accountRepository,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $accountRepository);

        $this->configurationService = $configurationService;
    }

    /**
     * @return null|\MyParcelNL\Pdk\Account\Model\Account
     */
    public function getFromStorage(): ?Account
    {
        $result = $this->configurationService->get('account_data');

        return $result ? new Account($result) : null;
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function store(?Account $account): ?Account
    {
        if (! $account) {
            $this->configurationService->delete('account_data');
            return $account;
        }

        $this->configurationService->set('account_data', $account->toStorableArray());

        return $this->save('account_data', $account);
    }
}

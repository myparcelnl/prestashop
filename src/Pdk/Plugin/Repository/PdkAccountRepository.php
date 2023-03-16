<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AbstractAccountRepository;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;

class PdkAccountRepository extends AbstractAccountRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface
     */
    private $configurationService;

    /**
     * @param  \MyParcelNL\Pdk\Api\Contract\ApiServiceInterface                           $apiService
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                          $storage
     * @param  \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface $configurationService
     */
    public function __construct(
        ApiServiceInterface           $apiService,
        StorageInterface              $storage,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $apiService);
        $this->configurationService = $configurationService;
    }

    public function getFromStorage(): ?Account
    {
        $result = $this->configurationService->get('account_data');

        return new Account($result);
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

        return $this->save('account', $account);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Repository\AbstractAccountRepository;

class PdkAccountRepository extends AbstractAccountRepository
{
    public function getFromStorage(): ?Account
    {
        // TODO: Implement getFromStorage() method.
        return new Account();
    }

    public function store(?Account $account): ?Account
    {
        // TODO: Implement store() method.
        return new Account();
    }
}

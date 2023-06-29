<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Action\Backend\Account;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;

final class PsUpdateAccountAction extends UpdateAccountAction
{
    /**
     * @param  null|\MyParcelNL\Pdk\Account\Model\Account $account
     *
     * @return void
     */
    protected function updateAndSaveAccount(?Account $account): void
    {
        parent::updateAndSaveAccount($account);
        // TODO: update our created ps carriers, setting enabled according to carrier configurations/options.
    }
}

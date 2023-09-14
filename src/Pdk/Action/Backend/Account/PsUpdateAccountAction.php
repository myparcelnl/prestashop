<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Action\Backend\Account;

use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;

final class PsUpdateAccountAction extends UpdateAccountAction
{
    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AccountSettings $accountSettings
     *
     * @return void
     */
    protected function updateAndSaveAccount(AccountSettings $accountSettings): void
    {
        parent::updateAndSaveAccount($accountSettings);

        /** @var PsCarrierServiceInterface $carrierService */
        $carrierService = Pdk::get(PsCarrierServiceInterface::class);
        $carrierService->updateCarriers();
    }
}

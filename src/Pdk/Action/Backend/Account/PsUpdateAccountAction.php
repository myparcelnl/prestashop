<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Action\Backend\Account;

use MyParcelNL\Pdk\App\Action\Backend\Account\UpdateAccountAction;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\PrestaShop\Contract\PsCarrierServiceInterface;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PsUpdateAccountAction extends UpdateAccountAction
{
    private ?string $mode;

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $this->mode = $request->query->has('mode') ? (string) $request->query->get('mode') : null;

        return parent::handle($request);
    }

    /**
     * @param  \MyParcelNL\Pdk\Settings\Model\AccountSettings $accountSettings
     *
     * @return void
     */
    protected function updateAndSaveAccount(AccountSettings $accountSettings): void
    {
        try {
            parent::updateAndSaveAccount($accountSettings);
        } catch (Throwable $e) {
            Logger::error('Account update partially failed', ['exception' => $e]);
        }

        /** @var string $uninstallMode */
        $uninstallMode = Pdk::get('updateAccountModeUninstall');

        if ($this->mode !== $uninstallMode) {
            try {
                /** @var PsCarrierServiceInterface $carrierService */
                $carrierService = Pdk::get(PsCarrierServiceInterface::class);
                $carrierService->updateCarriers();
            } catch (Throwable $e) {
                Logger::error('Failed to update carriers', ['exception' => $e]);
            }

            MyParcelModule::registerHooks();
        }

        EntityManager::flush();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\PrestaShop\Script\Service\PsBackendScriptService;
use MyParcelNL\PrestaShop\Script\Service\PsFrontendScriptService;

trait HasPdkScriptHooks
{
    /**
     * Load the js and css files of the admin app.
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        /** @var \MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface $viewService */
        $viewService = Pdk::get(ViewServiceInterface::class);

        if (! $viewService->isAnyPdkPage()) {
            return;
        }

        /** @var \MyParcelNL\PrestaShop\Script\Service\PsBackendScriptService $scriptService */
        $scriptService = Pdk::get(PsBackendScriptService::class);

        /** @var \AdminController $controller */
        $controller = $this->context->controller;

        $scriptService->register($controller, $this->_path);
    }

    /**
     * @return void
     */
    public function hookDisplayHeader(): void
    {
        $deliveryOptionsEnabled = Settings::get(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, CheckoutSettings::ID);

        /** @var \MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface $viewService */
        $viewService = Pdk::get(ViewServiceInterface::class);

        if (! $deliveryOptionsEnabled || ! $viewService->isCheckoutPage()) {
            return;
        }

        /** @var \MyParcelNL\PrestaShop\Script\Service\PsFrontendScriptService $scriptService */
        $scriptService = Pdk::get(PsFrontendScriptService::class);

        /** @var \FrontController $controller */
        $controller = $this->context->controller;

        $scriptService->register($controller, $this->_path);
    }
}

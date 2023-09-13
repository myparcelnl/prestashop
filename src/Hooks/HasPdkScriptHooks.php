<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;

trait HasPdkScriptHooks
{
    /**
     * Load the js and css files of the admin app.
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        /** @var ScriptServiceInterface $scriptService */
        $scriptService = Pdk::get(ScriptServiceInterface::class);

        /** @var \AdminControllerCore $controller */
        $controller = $this->context->controller;

        $scriptService->addForAdminHeader($controller, $this->_path);
    }

    /**
     * @return void
     */
    public function hookDisplayHeader(): void
    {
        /** @var \MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface $viewService */
        $viewService = Pdk::get(ViewServiceInterface::class);

        if (! $viewService->isCheckoutPage()) {
            return;
        }

        $this->loadCoreScripts();

        $this->loadDeliveryOptionsScripts();
    }

    private function loadCoreScripts(): void
    {
        $this->context->controller->addJS("{$this->_path}views/js/frontend/checkout-core/lib/checkout-core.iife.js");
        $this->context->controller->addCSS("{$this->_path}views/js/frontend/checkout-core/lib/style.css");
    }

    private function loadDeliveryOptionsScripts(): void
    {
        $version = Pdk::get('deliveryOptionsVersion');

        $this->context->controller->registerJavascript(
            'myparcelnl-delivery-options',
            sprintf('https://unpkg.com/@myparcel/delivery-options@%s/dist/myparcel.js', $version),
            ['server' => 'remote', 'position' => 'head', 'priority' => 1]
        );

        $this->context->controller->addJS(
            "{$this->_path}views/js/frontend/checkout-delivery-options/lib/checkout-delivery-options.iife.js"
        );
        $this->context->controller->addCSS("{$this->_path}views/js/frontend/checkout-delivery-options/lib/style.css");
    }
}

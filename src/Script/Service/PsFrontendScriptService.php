<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use FrontController;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Script\Contract\PsScriptServiceInterface;

final class PsFrontendScriptService extends AbstractPsScriptService implements PsScriptServiceInterface
{
    /**
     * @param  \FrontController $controller
     * @param  string           $path
     *
     * @return void
     */
    public function register($controller, string $path): void
    {
        $appInfo = Pdk::getAppInfo();

        $deliveryOptions = $appInfo->name . '-delivery-options';
        // $deliveryOptionsUrl = sprintf('https://unpkg.com/@myparcel/delivery-options@%s', 'beta');
        $deliveryOptionsUrl = 'http://127.0.0.1:8081';

        $this->addStyle($controller, $deliveryOptions, "$deliveryOptionsUrl/dist/style.css");
        $this->addScript($controller, $deliveryOptions, "$deliveryOptionsUrl/dist/myparcel.js");

        $this->addLocalScript($controller, 'checkout', "{$path}views/js/frontend/checkout/dist/index.iife.js");
        $this->addLocalStyle($controller, 'checkout', "{$path}views/js/frontend/checkout/dist/style.css");
    }

    /**
     * @param  \FrontController $controller
     * @param  string           $id
     * @param  string           $url
     * @param  array            $options
     *
     * @return void
     */
    protected function addLocalScript(FrontController $controller, string $id, string $url, array $options = []): void
    {
        $this->addScript($controller, $id, $url, array_merge(['server' => 'local'], $options));
    }

    /**
     * @param  \FrontController $controller
     * @param  string           $id
     * @param  string           $url
     * @param  array            $options
     *
     * @return void
     */
    protected function addLocalStyle(FrontController $controller, string $id, string $url, array $options = []): void
    {
        $this->addStyle($controller, $id, $url, array_merge(['server' => 'local'], $options));
    }

    /**
     * @param  \FrontController $controller
     * @param  string           $id
     * @param  string           $url
     * @param  array            $options
     *
     * @return void
     */
    protected function addScript(FrontController $controller, string $id, string $url, array $options = []): void
    {
        $controller->registerJavascript($id, $url, array_merge(['server' => 'remote'], $options));
    }

    /**
     * @param  \FrontController $controller
     * @param  string           $id
     * @param  string           $url
     * @param  array            $options
     *
     * @return void
     */
    protected function addStyle(FrontController $controller, string $id, string $url, array $options = []): void
    {
        $controller->registerStylesheet($id, $url, array_merge($options, ['server' => 'remote']));
    }
}

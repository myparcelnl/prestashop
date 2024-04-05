<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Script\Service;

use FrontController;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Script\Contract\PsScriptServiceInterface;

final class PsFrontendScriptService extends PsScriptService implements PsScriptServiceInterface
{
    private const HANDLE_DELIVERY_OPTIONS = 'myparcelnl-delivery-options';
    private const HANDLE_CHECKOUT         = 'myparcelnl-checkout';

    /**
     * @param  \FrontController $controller
     * @param  string           $path
     *
     * @return void
     */
    public function register($controller, string $path): void
    {
        $this->addStyle($controller, self::HANDLE_DELIVERY_OPTIONS, Pdk::get('deliveryOptionsCdnUrlCss'));
        $this->addScript($controller, self::HANDLE_DELIVERY_OPTIONS, Pdk::get('deliveryOptionsCdnUrlJs'));

        $checkoutPath = "{$path}views/js/frontend/checkout";

        $this->addLocalScript($controller, self::HANDLE_CHECKOUT, "$checkoutPath/dist/index.iife.js");
        $this->addLocalStyle($controller, self::HANDLE_CHECKOUT, "$checkoutPath/dist/style.css");
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

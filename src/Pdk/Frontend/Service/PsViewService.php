<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Frontend\Service;

use Dispatcher;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Service\AbstractViewService;
use RuntimeException;
final class PsViewService extends AbstractViewService
{
    /**
     * @throws \PrestaShopException
     */
    public function isCheckoutPage(): bool
    {
        return 'order' === $this->getPage();
    }

    /**
     * @return bool
     */
    public function isChildProductPage(): bool
    {
        return false;
    }

    /**
     * @throws \PrestaShopException
     */
    public function isOrderListPage(): bool
    {
        return 'AdminOrders' === $this->getPage();
    }

    /**
     * @throws \PrestaShopException
     */
    public function isOrderPage(): bool
    {
        return 'AdminOrders' === $this->getPage();
    }

    /**
     * @throws \PrestaShopException
     */
    public function isPluginSettingsPage(): bool
    {
        return Pdk::get('legacyControllerSettings') === $this->getPage();
    }

    /**
     * @throws \PrestaShopException
     */
    public function isProductPage(): bool
    {
        return 'AdminProducts' === $this->getPage();
    }

    /**
     * @return string
     * @throws \PrestaShopException
     */
    private function getPage(): string
    {
        // PS 9 uses Symfony routing; the legacy Dispatcher no longer resolves the controller name
        // for Symfony-routed pages. Check the _legacy_controller request attribute first.
        try {
            $request = Pdk::get('getPsService')('request_stack')->getCurrentRequest();
            $legacyController = $request ? $request->attributes->get('_legacy_controller') : null;

            if ($legacyController) {
                return $legacyController;
            }
        } catch (\Throwable $e) {
            // request_stack may not be available on PS 1.7/8
        }

        $dispatcher = Dispatcher::getInstance();

        if (! $dispatcher) {
            throw new RuntimeException('Dispatcher not found');
        }

        return $dispatcher->getController();
    }
}

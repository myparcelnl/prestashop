<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Frontend\Service;

use MyParcelNL\Pdk\Frontend\Service\AbstractViewService;
use PrestaShop\PrestaShop\Adapter\Entity\Dispatcher;
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
        return 'AdminModules' === $this->getPage();
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
        $dispatcher = Dispatcher::getInstance();

        if (! $dispatcher) {
            throw new RuntimeException('Dispatcher not found');
        }

        return $dispatcher->getController();
    }
}

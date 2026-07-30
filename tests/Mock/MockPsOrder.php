<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;
use OrderState;
use PrestaShopCollection;

/**
 * @see \OrderCore
 */
abstract class MockPsOrder extends ObjectModel
{
    protected bool $hasCustomIdKey = true;

    protected static function getTable(): string
    {
        return 'orders';
    }

    /**
     * Mirrors \OrderCore::getByReference(): a PrestaShopCollection holding the orders with the
     * given reference. The reference filtering happens here because the mock collection only
     * supports the primary-id "in" filter.
     *
     * @param  string $reference
     *
     * @return \PrestaShopCollection
     */
    public static function getByReference(string $reference): PrestaShopCollection
    {
        $ids = MockPsObjectModels::getByClass(static::class)
            ->filter(function ($order) use ($reference) {
                return $order->reference === $reference;
            })
            ->map(function ($order) {
                return (int) $order->id;
            })
            ->values()
            ->all();

        return (new PrestaShopCollection(static::class))->where('id_order', 'in', $ids);
    }

    public function getCurrentOrderState(): ?OrderState
    {
        $state = $this->getAttribute('current_state');

        if ($state) {
            return new OrderState($state);
        }

        return null;
    }

    /**
     * @param  int      $state
     * @param  int|null $idEmployee
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCurrentState(int $state, int $idEmployee = null): bool
    {
        $this->setAttribute('current_state', $state);

        return $this->update();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;
use OrderState;

/**
 * @see \OrderCore
 */
abstract class MockPsOrder extends ObjectModel
{
    protected $hasCustomIdKey = true;

    protected static function getTable(): string
    {
        return 'orders';
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function setCurrentState(int $state, int $idEmployee = null): bool
    {
        $this->setAttribute('current_state', $state);

        return $this->update();
    }
}

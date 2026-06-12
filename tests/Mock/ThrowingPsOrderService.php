<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use InvalidArgumentException;
use MyParcelNL\PrestaShop\Contract\PsOrderServiceInterface;
use ObjectModel;
use Order;

/**
 * Test double whose get() throws an unexpected (non-"Order not found") InvalidArgumentException.
 * Used to verify that PsPdkOrderRepository::find() re-throws exceptions it does not recognise rather
 * than swallowing them. Every other method is a no-op stub since the find()/get() path never reaches
 * them once get() throws.
 */
final class ThrowingPsOrderService implements PsOrderServiceInterface
{
    public const MESSAGE = 'unexpected order service failure';

    public function get($input): ?ObjectModel
    {
        throw new InvalidArgumentException(self::MESSAGE);
    }

    public function add(ObjectModel $model): bool
    {
        return false;
    }

    public function create(?int $id = null): ObjectModel
    {
        return new Order();
    }

    public function delete($input, bool $soft = false): bool
    {
        return false;
    }

    public function deleteMany($input, bool $soft = false): bool
    {
        return false;
    }

    public function exists($input): bool
    {
        return false;
    }

    public function getId($input): ?int
    {
        return null;
    }

    public function update(ObjectModel $model): bool
    {
        return false;
    }

    public function getOrderData($input): array
    {
        return [];
    }

    public function getOrderNotes($input): array
    {
        return [];
    }

    public function updateOrderData($input, array $orderData): void
    {
    }

    public function updateOrderNotes($input, array $notes): void
    {
    }
}

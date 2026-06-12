<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

/**
 * Request-scoped store for the rendered product-settings HTML used by the PS9
 * placeholder/footer-flush workaround in HasPdkProductHooks and HasPdkRenderHooks.
 *
 * Dedicated class so both traits share a single statically-typed storage slot.
 */
final class PendingProductSettings
{
    private static string $html = '';

    public static function set(string $html): void
    {
        self::$html = $html;
    }

    public static function get(): string
    {
        return self::$html;
    }

    public static function isEmpty(): bool
    {
        return self::$html === '';
    }
}

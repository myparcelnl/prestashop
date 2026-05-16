<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;

trait HasPdkRenderHooks
{
    /**
     * Renders the notification area and plugin settings.
     *
     * @noinspection PhpUnused
     * @return string
     */
    public function hookDisplayAdminAfterHeader(): string
    {
        /** @var PsPdkContextService $contextService */
        $contextService = Pdk::get(ContextServiceInterface::class);

        // Only render on MyParcel pages
        if (!$contextService->shouldRenderPdkComponents()) {
            return '';
        }

        // Always render all PDK components - mirroring WooCommerce PdkPluginSettingsHooks pattern
        $html = Frontend::renderNotifications();
        $html .= Frontend::renderModals();
        $html .= Frontend::renderPluginSettings(); // This handles both minimal and full boot

        return $html;
    }

    /**
     * Renders the PDK init script in the admin footer
     * This is critical for the PDK frontend to initialize properly
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function hookDisplayBackOfficeFooter(): string
    {
        /** @var PsPdkContextService $contextService */
        $contextService = Pdk::get(ContextServiceInterface::class);

        // Only render on MyParcel pages
        if (!$contextService->shouldRenderPdkComponents()) {
            return '';
        }

        $html = Frontend::renderInitScript();

        // PS9's product page runs displayAdminProductsExtra output through HTMLPurifier,
        // which strips data-pdk-context. Render the component here (not purified) and
        // move it into the placeholder rendered by displayAdminProductsExtra.
        // TODO: remove when migrated to actionProductFormBuilderModifier
        if (self::$pendingProductSettingsHtml) {
            $html .= self::$pendingProductSettingsHtml;

            if (preg_match('/id="([^"]+)"/', self::$pendingProductSettingsHtml, $idMatch)) {
                $id = $idMatch[1];
                $html .= "<script>document.getElementById('{$id}-placeholder')?.replaceWith(document.getElementById('{$id}'));</script>";
            }
        }

        return $html;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function hookDisplayAdminEndContent(): string
    {
        // This hook is not used for PDK rendering
        return '';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Service\RenderService;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

class PsRenderService extends RenderService
{
    /**
     * @param  \MyParcelNL\Pdk\Plugin\Model\PdkCart $cart
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function renderDeliveryOptions(PdkCart $cart): string
    {
        ob_start();

        $customCss = Settings::get(CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS, CheckoutSettings::ID);
        $context   = $this->contextService->createContexts([Context::ID_CHECKOUT], ['cart' => $cart]);

        printf(
            '<div id="mypa-delivery-options-wrapper" class="woocommerce-myparcel__delivery-options" data-context="%s">%s<div id="myparcel-delivery-options"></div></div>',
            $this->encodeContext($context),
            $customCss ? sprintf('<style>%s</style>', $customCss) : ''
        );

        return ob_get_clean();
    }
}

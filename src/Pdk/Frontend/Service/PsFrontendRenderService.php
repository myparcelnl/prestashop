<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Frontend\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

final class PsFrontendRenderService extends FrontendRenderService
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return string
     */
    public function renderDeliveryOptions(PdkCart $cart): string
    {
        $customCss = Settings::get(CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS, CheckoutSettings::ID);
        $context   = $this->contextService->createContexts([Context::ID_CHECKOUT], ['cart' => $cart]);

        return sprintf(
            '<div id="mypa-delivery-options-wrapper" class="mb-1" data-context="%s"><div class="card-block bg-faded">%s<div id="myparcel-delivery-options"></div></div></div>',
            $this->encodeContext($context),
            $customCss ? sprintf('<style>%s</style>', $customCss) : ''
        );
    }
}

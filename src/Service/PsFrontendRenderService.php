<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\Context\Context;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

class PsFrontendRenderService extends FrontendRenderService
{
    /**
     * @param  \MyParcelNL\Pdk\App\Cart\Model\PdkCart $cart
     *
     * @return string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function renderDeliveryOptions(PdkCart $cart): string
    {
        $customCss = Settings::get(CheckoutSettings::DELIVERY_OPTIONS_CUSTOM_CSS, CheckoutSettings::ID);
        $context   = $this->contextService->createContexts([Context::ID_CHECKOUT], ['cart' => $cart]);

        return sprintf(
            '<div id="mypa-delivery-options-wrapper" data-context="%s">%s<div id="myparcel-delivery-options"></div></div>',
            $this->encodeContext($context),
            $customCss ? sprintf('<style>%s</style>', $customCss) : ''
        );
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Service\PsCarrierService;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPdkCheckoutDeliveryOptionsHooks
{
    /**
     * @param $params
     *
     * @return false|string
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        /** @var PsCarrierService $carrierService */
        $carrierService = Pdk::get(PsCarrierService::class);

        $psCarrierId = $params['carrier']['id'] ?? null;

        if (! $carrierService->isMyParcelCarrier($psCarrierId)) {
            return false;
        }

        try {
            return $this->renderDeliveryOptions();
        } catch (Throwable $e) {
            Logger::error('Failed to render delivery options', ['exception' => $e, 'params' => $params]);

            return false;
        }
    }

    /**
     * @param  array $address
     *
     * @return string
     */
    private function encodeAddress(array $address): string
    {
        return htmlspecialchars(json_encode(Utils::filterNull($address)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function renderDeliveryOptions(): string
    {
        /** @var PsCarrierService $carrierService */
        $carrierService = Pdk::get(PsCarrierService::class);
        /** @var PdkCartRepositoryInterface $cartRepository */
        $cartRepository = Pdk::get(PdkCartRepositoryInterface::class);
        /** @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter $addressAdapter */
        $addressAdapter = Pdk::get(PsAddressAdapter::class);

        $cart = $this->context->cart;

        $this->context->smarty->setEscapeHtml(false);

        $this->context->smarty->assign([
            'carrier'         => $carrierService->getMyParcelCarrierIdentifier($cart->id_carrier),
            'shippingAddress' => $this->encodeAddress($addressAdapter->fromAddress($cart->id_address_delivery)),
            'billingAddress'  => $this->encodeAddress($addressAdapter->fromAddress($cart->id_address_invoice)),
            'content'         => Frontend::renderDeliveryOptions($cartRepository->get($cart)),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier_delivery_options.tpl');
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter;
use MyParcelNL\PrestaShop\Service\PsCarrierService;
use Throwable;

/**
 * @property \Context $context
 */
trait HasPdkCheckoutDeliveryOptionsHooks
{
    /**
     * @param  array $params
     *
     * @return false|string
     */
    public function hookDisplayBeforeCarrier(array $params)
    {
        if ($this->deliveryOptionsDisabled()) {
            return false;
        }

        try {
            return $this->renderDeliveryOptions();
        } catch (Throwable $e) {
            Logger::error('Failed to render delivery options', [
                'exception' => $e->getMessage(),
                'params'    => $params,
            ]);

            return false;
        }
    }

    /**
     * @param  array $params
     *
     * @return false|string
     */
    public function hookDisplayCarrierExtraContent(array $params)
    {
        if ($this->deliveryOptionsDisabled()) {
            return false;
        }

        /** @var PsCarrierService $carrierService */
        $carrierService = Pdk::get(PsCarrierService::class);

        $psCarrierId = $params['carrier']['id'] ?? null;

        if (! $carrierService->isMyParcelCarrier($psCarrierId)) {
            return false;
        }

        try {
            return $this->renderCarrierData($params['carrier']['id']);
        } catch (Throwable $e) {
            Logger::error('Failed to render', ['exception' => $e, 'params' => $params]);

            return false;
        }
    }

    /**
     * Check if delivery options should be disabled for the current cart.
     *
     * Delivery options are disabled when:
     * - Global checkout setting disables them, OR
     * - Any product in the cart has disableDeliveryOptions enabled, OR
     * - No items in the cart are deliverable
     *
     * @return bool
     */
    private function deliveryOptionsDisabled(): bool
    {
        // First check global checkout setting.
        if (! Settings::get(CheckoutSettings::ENABLE_DELIVERY_OPTIONS, CheckoutSettings::ID)) {
            return true;
        }

        // Check cart-level conditions (product settings and deliverability).
        try {
            /** @var PdkCartRepositoryInterface $cartRepository */
            $cartRepository = Pdk::get(PdkCartRepositoryInterface::class);

            $pdkCart = $cartRepository->get($this->context->cart);

            // Check if any product explicitly disables delivery options.
            $hasProductDisablingDeliveryOptions = $pdkCart->lines->containsStrict(
                'product.mergedSettings.disableDeliveryOptions',
                TriStateService::ENABLED
            );

            // Check if any items are deliverable.
            $hasDeliverableItems = $pdkCart->lines->isDeliverable();

            // Disable if: no deliverable items OR a product disables delivery options.
            return ! $hasDeliverableItems || $hasProductDisablingDeliveryOptions;
        } catch (Throwable $e) {
            Logger::error('Failed to determine delivery options availability', [
                'exception' => $e->getMessage(),
            ]);

            // If anything goes wrong, do not block delivery options (fail open).
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
     * @param  int $carrierId
     *
     * @return string
     */
    private function renderCarrierData(int $carrierId): string
    {
        /** @var PsCarrierService $carrierService */
        $carrierService = Pdk::get(PsCarrierService::class);

        $this->context->smarty->setEscapeHtml(false);
        $this->context->smarty->assign([
            'carrier' => $carrierService->getMyParcelCarrierIdentifier($carrierId),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier_data.tpl');
    }

    /**
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function renderDeliveryOptions(): string
    {
        /** @var PdkCartRepositoryInterface $cartRepository */
        $cartRepository = Pdk::get(PdkCartRepositoryInterface::class);
        /** @var \MyParcelNL\PrestaShop\Pdk\Base\Adapter\PsAddressAdapter $addressAdapter */
        $addressAdapter = Pdk::get(PsAddressAdapter::class);

        $cart = $this->context->cart;

        $this->context->smarty->setEscapeHtml(false);
        $this->context->smarty->assign([
            'content'         => Frontend::renderDeliveryOptions($cartRepository->get($cart)),
            'shippingAddress' => $this->encodeAddress($addressAdapter->fromAddress($cart->id_address_delivery)),
            'billingAddress'  => $this->encodeAddress($addressAdapter->fromAddress($cart->id_address_invoice)),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier_delivery_options.tpl');
    }
}

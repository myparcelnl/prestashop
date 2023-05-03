<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use Address;
use Country;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\RenderServiceInterface;
use MyParcelNL\PrestaShop\Grid\Column\LabelsColumn;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository;
use MyParcelNL\PrestaShop\Pdk\Order\Repository\PsCartRepository;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository;
use MyParcelNL\PrestaShop\Service\PsRenderService;
use PrestaShop\PrestaShop\Core\Grid\Record\RecordCollection;

trait HasPdkRenderHooks
{
    /**
     * Renders the module configuration page.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->renderService()
            ->renderPluginSettings();
    }

    public function hookActionOrderGridDefinitionModifier(array $params): void
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition
            ->getColumns()
            ->addBefore(
                'actions',
                (new LabelsColumn('myparcel'))
                    ->setName('MyParcel')
            );

        //        $bulkActions = $definition->getBulkActions();
        //        foreach ($this->getBulkActionsMap() as $action => $data) {
        //            $bulkActions->add(
        //                (new IconBulkAction($action))
        //                    ->setName(LanguageService::translate($data['label']))
        //                    ->setOptions(['icon' => $data['icon']])
        //            );
        //        }
    }

    /**
     * Renders MyParcel buttons in order grid.
     *
     * @param  array $params
     *
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function hookActionOrderGridPresenterModifier(array &$params): void
    {
        $params['presented_grid']['data']['records'] = new RecordCollection(
            array_map(static function (array $row) {
                /** @var PdkOrderRepository $repository */
                $repository = Pdk::get(PdkOrderRepository::class);
                $order      = $repository->get($row['id_order']);

                $row['myparcel'] = self::renderService()
                    ->renderOrderListItem($order);

                return $row;
            }, $params['presented_grid']['data']['records']->all())
        );
    }

    /**
     * Renders the notification area.
     *
     * @noinspection PhpUnused
     * @return string
     */
    public function hookDisplayAdminAfterHeader(): string
    {
        $html = $this->renderService()
            ->renderNotifications();
        $html .= $this->renderService()
            ->renderModals();

        return $html;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function hookDisplayAdminEndContent(): string
    {
        return $this->renderService()
            ->renderInitScript();
    }

    /**
     * Renders the shipment card on a single order page.
     *
     * @param  array $params
     *
     * @return string
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function hookDisplayAdminOrderMain(array $params): string
    {
        /** @var \MyParcelNL\PrestaShop\Pdk\Order\Repository\PdkOrderRepository $repository */
        $repository = Pdk::get(PdkOrderRepository::class);
        $order      = $repository->get($params['id_order']);

        return $this->renderService()
            ->renderOrderBox($order);
    }

    /**
     * Renders the product settings.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        /** @var \MyParcelNL\PrestaShop\Pdk\Product\Repository\PdkProductRepository $repository */
        $repository = Pdk::get(PdkProductRepository::class);
        $product    = $repository->getProduct($params['id_product']);

        return $this->renderService()
            ->renderProductSettings($product);
    }

    /**
     * Load the js and css files of the admin app.
     *
     * @return void
     */
    public function hookDisplayBackOfficeHeader(): void
    {
        /** @var ScriptServiceInterface $scriptService */
        $scriptService = Pdk::get(ScriptServiceInterface::class);

        /** @var \AdminControllerCore $controller */
        $controller = $this->context->controller;

        $scriptService->addForAdminHeader($controller, $this->_path);
    }

    /**
     * @param $params
     *
     * @return false|string
     * @throws \PrestaShop\PrestaShop\Core\Localization\Exception\LocalizationException
     * @throws \Exception
     */
    public function hookDisplayCarrierExtraContent($params)
    {
        /** @var \MyParcelNL\Pdk\Plugin\Service\RenderService $renderService */
        $renderService = Pdk::get(PsRenderService::class);

        /** @var PsCartRepository $cartRepository */
        $cartRepository = Pdk::get(PsCartRepository::class);

        //        $address->address1 = preg_replace('/\D/', '', $address->address1);

        if (empty($this->context->cart->id_carrier)) {
            $selectedDeliveryOption          = current($this->context->cart->getDeliveryOption(null, false, false));
            $this->context->cart->id_carrier = (int) $selectedDeliveryOption;
        }

        $shippingAddress = $this->getContactDetails(new Address($this->context->cart->id_address_delivery));
        $billingAddress  = $this->getContactDetails(new Address($this->context->cart->id_address_invoice));

        $cart = $cartRepository->get($this->context->cart);
        $this->context->smarty->setEscapeHtml(false);
        $renderService->renderDeliveryOptions($cart);
        $this->context->smarty->assign([
            'deliveryOptions' => $renderService->renderDeliveryOptions($cart),
            'shippingAddress' => $this->encodeAddress($shippingAddress),
            'billingAddress'  => $this->encodeAddress($billingAddress),
        ]);

        return $this->display($this->name, 'views/templates/hook/carrier.tpl');
    }

    public function hookHeader()
    {
        $version = Pdk::get('deliveryOptionsVersion');

        $this->context->controller->registerJavascript(
            'myparcelnl-frontend-scripts',
            $this->_path . 'views/js/frontend/lib/prestashop-frontend.iife.js',
            ['server' => 'local', 'position' => 'head', 'priority' => 1]
        );

        $this->context->controller->registerJavascript(
            'myparcelnl-delivery-options',
            sprintf('https://unpkg.com/@myparcel/delivery-options@%s/dist/myparcel.js', $version),
            ['server' => 'remote', 'position' => 'head', 'priority' => 1]
        );
    }

    private function encodeAddress(ContactDetails $contactDetails): string
    {
        return htmlspecialchars(
            json_encode(array_filter($contactDetails->toArray())),
            ENT_QUOTES,
            'UTF-8'
        );
    }

    private function getContactDetails(Address $address): ContactDetails
    {
        return new ContactDetails([
            'boxNumber'            => null,
            'cc'                   => Country::getIsoById($address->id_country),
            'city'                 => $address->city,
            'fullStreet'           => $address->address1,
            'number'               => null,
            'numberSuffix'         => null,
            'postalCode'           => $address->postcode,
            'region'               => null,
            'state'                => null,
            'street'               => null,
            'streetAdditionalInfo' => null,
            'person'               => $address->firstname . ' ' . $address->lastname,
            'email'                => $this->context->customer->email,
            'phone'                => $address->phone,
        ]);
    }

    private function renderService(): RenderServiceInterface
    {
        return Pdk::get(RenderServiceInterface::class);
    }
}

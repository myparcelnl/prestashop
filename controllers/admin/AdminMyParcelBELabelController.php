<?php

use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\DeliverySettings\DeliverySettings;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Label\LabelOptionsResolver;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Model\Webhook\StatusChangeWebhookPayload;
use Gett\MyparcelBE\Module\Carrier\Provider\CarrierSettingsProvider;
use Gett\MyparcelBE\Module\Carrier\Provider\DeliveryOptionsProvider;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Provider\OrderLabelProvider;
use Gett\MyparcelBE\Service\CarrierService;
use Gett\MyparcelBE\Service\Consignment\Download;
use Gett\MyparcelBE\Service\ErrorMessage;
use Gett\MyparcelBE\Service\MyparcelStatusProvider;
use Gett\MyparcelBE\Service\Order\OrderTotalWeight;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use MyParcelNL\Sdk\src\Exception\InvalidConsignmentException;
use MyParcelNL\Sdk\src\Helper\MyParcelCollection;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

if (file_exists(_PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php';
}

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelBELabelController extends ModuleAdminController
{
    public function initContent()
    {
        parent::initContent();
        switch (Tools::getValue('action')) {
            case 'return':
                $this->processReturn();

                break;
        }

        die();
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function processReturn(): void
    {
        $order = new Order(Tools::getValue('id_order'));

        if (! Validate::isLoadedObject($order)) {
            return;
        }

        $address         = new Address($order->id_address_delivery);
        $customer        = new Customer($order->id_customer);
        $platformService = PlatformServiceFactory::create();

        try {
            $consignment = ($platformService->generateConsignment())
                ->setReferenceIdentifier($order->getId())
                ->setCountry(Country::getIsoById($address->id_country))
                ->setPerson($address->firstname . ' ' . $address->lastname)
                ->setFullStreet($address->address1)
                ->setPostalCode($address->postcode)
                ->setCity($address->city)
                ->setEmail($customer->email)
                ->setContents(1);

            $collection = (new ConsignmentCollection())
                ->addConsignment($consignment)
                ->setPdfOfLabels()
                ->generateReturnConsignments(true);
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('An error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
        }

        $statusProvider = new MyparcelStatusProvider();
        $consignment    = $collection->first();

        try {
            OrderLabel::createFromConsignment($consignment, $statusProvider);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection|null
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function processCreateBulk(): ConsignmentCollection
    {
        $postValues = Tools::getAllValues();
        $printPosition = false;
        if (!empty($postValues['format']) && $postValues['format'] == 'a4') {
            $printPosition = $postValues['position'];
        }
        $factory = new ConsignmentFactory(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            $postValues,
            $this->module
        );
        $orderIds = Tools::getValue('order_ids');
        if (empty($orderIds)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('Can\'t create label for these orders', 'adminlabelcontroller'));
        }
        $orders = OrderLabel::getDataForLabelsCreate($orderIds);
        if (empty($orders)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('Can\'t create label for these orders.', 'adminlabelcontroller'));
        }

        try {
            $collection = $factory->fromOrders($orders);

            $labelOptionsResolver = new LabelOptionsResolver();

            foreach ($orderIds as $orderId) {
                $labelOptions = $labelOptionsResolver->getLabelOptions(new Order((int) $orderId));

                $options = json_decode($labelOptions);

                $consignment = $collection->getConsignmentsByReferenceId($orderId)->getOneConsignment();
                if ($options->package_type && count($consignment->getItems()) == 0) {
                    $consignment->setPackageType($options->package_type);
                } else {
                    $consignment->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
                }
                if (isset($options->return_undelivered) && true === $options->return_undelivered) {
                    $consignment->setReturn(true);
                }
                if (isset($options->package_format)
                    && Constant::PACKAGE_FORMAT_LARGE_INDEX === $options->package_format) {
                    $consignment->setLargeFormat(true);
                }
                $consignment->setDeliveryDate($this->fixPastDeliveryDate($consignment->getDeliveryDate()));
                $this->sanitizeSignature($consignment);
                $this->sanitizePackageType($consignment);
                $this->sanitizeDeliveryType($consignment);
                if (AbstractConsignment::PACKAGE_TYPE_PACKAGE === $consignment->getPackageType()) {
                    if ($options->only_to_recipient == 1
                        && $consignment->canHaveShipmentOption(
                            AbstractConsignment::SHIPMENT_OPTION_ONLY_RECIPIENT
                        )) {
                        $consignment->setOnlyRecipient(true);
                    } else {
                        $consignment->setOnlyRecipient(false);
                    }
                    if (1 == $options->age_check && count($consignment->getItems()) == 0) {
                        $consignment->setAgeCheck(true);
                    } else {
                        $consignment->setAgeCheck(false);
                    }
                    if (1 == $options->signature
                        && $consignment->canHaveShipmentOption(
                            AbstractConsignment::SHIPMENT_OPTION_SIGNATURE
                        )) {
                        $consignment->setSignature(true);
                    } else {
                        $consignment->setSignature(false);
                    }
                    if ($options->insurance) {
                        $consignment->setInsurance(2500);
                    }
                }
            }


            $collection->setPdfOfLabels($printPosition);
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            $this->logError($e);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->getParsedErrorMessage($e));
        }

        $statusProvider = new MyparcelStatusProvider();

        /** @var AbstractConsignment $consignment */
        foreach ($collection as $consignment) {
            $orderId = (int) $consignment->getReferenceIdentifier();
            if (! $this->validateConsignmentInvoice($consignment, $orderId)) {
                continue;
            }

            try {
                OrderLabel::createFromConsignment($consignment, $statusProvider);
                OrderLabel::updateOrderTrackingNumber($orderId, $consignment->getBarcode());
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        $this->updateOrderStatuses($collection);

        return $collection;
    }

    /**
     * @param \MyParcelNL\Sdk\src\Helper\MyParcelCollection $collection
     */
    private function updateOrderStatuses(MyParcelCollection $collection): void
    {
        foreach ($collection as $consignment) {
            $this->updateStatus($consignment->getConsignmentId(), $consignment->getStatus());
        }
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function processRefresh(): void
    {
        $labelIds = OrderLabel::getOrdersLabels(Tools::getValue('order_ids'));

        if (empty($labelIds)) {
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('No created labels found', 'adminlabelcontroller'));
        }

        try {
            $collection = ConsignmentCollection::findMany($labelIds, Configuration::get(Constant::API_KEY_CONFIGURATION_NAME));
            $collection->setLinkOfLabels();
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            die($this->module->l('An error occurred in the MyParcel module, please try again.', 'adminlabelcontroller'));
        }

        $status_provider = new MyparcelStatusProvider();

        foreach ($collection as $consignment) {
            $order_label = OrderLabel::findByLabelId($consignment->getConsignmentId());
            $order_label->status = $status_provider->getStatus($consignment->getStatus());
            $order_label->save();
        }

        die();
    }

    public function processPrint()
    {
        $labels = OrderLabel::getOrdersLabels(Tools::getValue('order_ids'));
        if (empty($labels)) {
            if (Tools::getIsset('id_order')) {
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], [
                    'id_order' => (int) Tools::getValue('id_order'),
                    'vieworder' => '',
                ]));
            }
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
        }
        $service = new Download(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );
        $service->downloadLabel($labels);
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function processUpdateLabel(): void
    {
        try {
            $collection = ConsignmentCollection::find(Tools::getValue('labelId'), Configuration::get(Constant::API_KEY_CONFIGURATION_NAME));
            $collection->setLinkOfLabels();
            Logger::addLog($collection->toJson());
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
        }

        $statusProvider = new MyparcelStatusProvider();

        if (! empty($collection)) {
            foreach ($collection as $consignment) {
                $orderLabel         = OrderLabel::findByLabelId($consignment->getConsignmentId());
                $orderLabel->status = $statusProvider->getStatus($consignment->getStatus());
                $orderLabel->save();
            }
        }

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
    }

    public function processDownloadLabel()
    {
        Tools::setCookieSameSite(
            'downloadPdfLabel',
            1,
            0,
            '/',
            '',
            false,
            false,
            'Strict'
        );
        $service = new Download(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );

        $service->downloadLabel([Tools::getValue('label_id')]);
    }

    public function processExportPrint()
    {
        $collection = $this->processCreateBulk();
        Tools::setCookieSameSite(
            'downloadPdfLabel',
            1,
            0,
            '/',
            '',
            false,
            false,
            'Strict'
        );
        if (!is_string($collection->getLabelPdf())) {
            $redirectParams = [];
            $idOrder = (new OrderLabelProvider($this))->provideOrderId((int) Tools::getValue('label_id'));
            if ($idOrder) {
                $redirectParams['vieworder'] = '';
                $redirectParams['id_order'] = $idOrder;
            }

            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminOrders', true, [], $redirectParams)
            );
        }
        $collection->downloadPdfOfLabels(
            'true' === Configuration::get(
                Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
                false
            )
        );
    }

    /**
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function ajaxProcessSaveConcept(): void
    {
        $postValues = Tools::getAllValues();

        if (! isset($postValues['id_order'])) {
            $this->errors[] = $this->module->l('Order not found by ID', 'adminlabelcontroller');
        }

        if (! empty($this->errors)) {
            $this->returnAjaxResponse();
        }

        $orderId = (int) $postValues['id_order'];
        $order   = new Order($orderId);

        try {
            DeliveryOptions::updateDeliveryOptions($order, $postValues);
        } catch (Exception $e) {
            $this->errors[] = $this->module->l('Error loading the delivery options.', 'adminlabelcontroller');
            $this->errors[] = $e->getMessage();
        }

        $this->returnAjaxResponse();
    }

    public function returnAjaxResponse($response = [], $idOrder = null)
    {
        $results = ['hasError' => false];
        if (!empty($this->errors)) {
            $results['hasError'] = true;
            $results['errors'] = $this->errors;
        }

        if ($idOrder) {
            $labelList = OrderLabel::getOrderLabels((int) $idOrder, []);
            $labelListHtml = $this->context->smarty->createData(
                $this->context->smarty
            );
            $labelListHtml->assign([
                'labelList' => $labelList,
                'promptForLabelPosition' => Configuration::get(Constant::LABEL_PROMPT_POSITION_CONFIGURATION_NAME),
            ]);

            $labelListHtmlTpl = $this->context->smarty->createTemplate(
                $this->getTemplatePath() . 'hook/label-list' . $this->getTemplateSuffix() . '.tpl',
                $labelListHtml
            );

            $results['labelsHtml'] = $labelListHtmlTpl->fetch();
        }

        $results = array_merge($results, $response);

        die(json_encode($results));
    }

    /**
     * @return void
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function ajaxProcessCreateLabel(): void
    {
        $postValues = Tools::getAllValues();
        $orderId    = (int) ($postValues['id_order'] ?? null);

        if (isset($postValues['insuranceAmount']) && '-1' === $postValues['insuranceAmount']) {
            $postValues['insuranceAmount'] = $postValues['insurance-amount-custom-value'];
        }
        if (! isset($postValues['insurance']) || ! $postValues['insurance'] ||
            (string) AbstractConsignment::PACKAGE_TYPE_PACKAGE !== ($postValues['packageType'] ?? '')
        ) {
            unset($postValues['insuranceAmount']);
            unset($postValues['insurance-amount-custom-value']);
        }

        if (! $orderId) {
            $this->errors[] = $this->module->l('No order ID found.', 'adminlabelcontroller');
            $this->returnAjaxResponse();
        }

        $orders = OrderLabel::getDataForLabelsCreate([$orderId]);

        if (empty($orders)) {
            $this->errors[] = $this->module->l('No order found.', 'adminlabelcontroller');
            $this->returnAjaxResponse();
        }

        $orderArray = reset($orders);
        $order      = new Order($orderId);
        $collection = null;

        $factory = new ConsignmentFactory(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            $postValues,
            $this->module
        );

        try {
            $deliveryOptions = DeliveryOptions::updateDeliveryOptions($order, $postValues);
            $collection      = $factory->fromOrder($orderArray, $deliveryOptions);

            Logger::addLog($collection->toJson());
            $collection->setLinkOfLabels();
            ApiLogger::addLog(json_encode($collection->toArray(), JSON_PRETTY_PRINT));

            if (($postValues[Constant::RETURN_PACKAGE_CONFIGURATION_NAME] ?? 0) && $this->module->isNL()) {
                $collection->generateReturnConsignments(true);
            }
        } catch (InvalidConsignmentException $e) {
            Logger::addLog($this->module->l(
                'InvalidConsignmentException exception triggered.',
                'adminlabelcontroller'
            ), true, true);
            $this->logError($e);
            $this->errors[] = sprintf($this->module->l(
                'MyParcelBE: Delivery address is not valid for order ID: %d.',
                'adminlabelcontroller'
            ), (int) $orderId);
            $this->returnAjaxResponse();
        } catch (Exception $e) {
            $this->logError($e);
            header('HTTP/1.1 500 Internal Server Error', true, 500);
            $this->errors[] = $this->getParsedErrorMessage($e);
            $this->returnAjaxResponse();
        }

        if (null === $collection) {
            $this->errors[] = $this->module->l(
                'An error occurred in the MyParcel module, please try again.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }

        $labelIds       = [];
        $statusProvider = new MyparcelStatusProvider();

        if (! empty($postValues['listingPage'])) {
            $orderId = null;
        }

        foreach ($collection as $consignment) {
            try {
                $labelId = OrderLabel::createFromConsignment($consignment, $statusProvider);
                if ($labelId) {
                    $labelIds[] = $labelId;
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
                continue;
            }

            $this->updateStatus($labelId, $consignment->getStatus());
            OrderLabel::updateOrderTrackingNumber($orderId, $consignment->getBarcode());
        }

        $this->returnAjaxResponse(['labelIds' => $labelIds], $orderId);
    }

    /**
     * @param int $labelId
     * @param int $shipmentStatus
     */
    public function updateStatus(int $labelId, int $shipmentStatus): void
    {
        if (! in_array($shipmentStatus, StatusChangeWebhookPayload::STATUS_CHANGE_DURING_EXPORT_NOT_WEBHOOK)) {
            return;
        }

        try {
            OrderLabel::updateStatus($labelId, $shipmentStatus);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            ApiLogger::addLog(sprintf('Could not set status for shipment %s', $labelId));
        }
    }

    /**
     * Prints single or bulk labels by (array) id_label and (int) id_order
     **/
    public function processPrintOrderLabel()
    {
        $labels = OrderLabel::getOrderLabels(Tools::getValue('id_order'), Tools::getValue('label_id'));
        if (empty($labels)) {
            Tools::redirectAdmin($this->context->link->getAdminLink(
                'AdminOrders',
                true,
                [],
                [
                    'vieworder' => '',
                    'id_order' => (int) Tools::getValue('id_order'),
                ]
            ));
        }
        $labelIds = [];
        foreach ($labels as $label) {
            $labelIds[] = (int) $label['id_label'];
        }
        $service = new Download(
            Configuration::get(Constant::API_KEY_CONFIGURATION_NAME),
            Tools::getAllValues(),
            new Configuration()
        );
        $service->downloadLabel($labelIds);
    }

    public function ajaxProcessDeleteLabel()
    {
        $postValues = Tools::getAllValues();
        $result = true;
        if (!empty($postValues['id_order_label'])) {
            $orderLabel = new OrderLabel((int) $postValues['id_order_label']);
            $result &= $orderLabel->delete();
        }
        if (!$result) {
            $this->errors[] = $this->module->l(
                'Error deleting the label.',
                'adminlabelcontroller'
            );
        }

        $this->returnAjaxResponse();
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     * @throws \SmartyException
     * @throws \Exception
     */
    public function ajaxProcessUpdateDeliveryOptions(): void
    {
        $postValues = Tools::getAllValues();
        $options    = $postValues['myparcel-delivery-options'] ?? null;
        $action     = $postValues['action'] ?? null;
        $order      = new Order($postValues['id_order'] ?? 0);

        if ('updateDeliveryOptions' === $action && ! empty($options)) {
            DeliverySettings::save($order->getIdCart(), json_decode($options, true));
        } else {
            $this->errors[] = $this->module->l(
                'Error updating the delivery options.',
                'adminlabelcontroller'
            );
        }

        $weight                  = (new OrderTotalWeight())->convertWeightToGrams($order->getTotalWeight());
        $extraOptions            = DeliverySettings::getExtraOptionsFromOrder($order);
        $digitalStampWeight      = $extraOptions->getDigitalStampWeight() ?? $weight;
        $deliveryOptionsProvider = new DeliveryOptionsProvider();
        $deliveryOptions         = $deliveryOptionsProvider->provide($order->getId());
        $carrierSettingsProvider = new CarrierSettingsProvider($this->module);
        $currency                = Currency::getDefaultCurrency();
        $labelOptionsResolver    = new LabelOptionsResolver();

        $labelConceptHtml = $this->context->smarty->createData($this->context->smarty);
        $labelConceptHtml->assign([
            'deliveryOptions'      => $deliveryOptions,
            'carrierSettings'      => $carrierSettingsProvider->provide($order->getIdCarrier()),
            'date_warning_display' => $deliveryOptionsProvider->provideWarningDisplay($order->getId()),
            'isBE'                 => $this->module->isBE(),
            'currencySign'         => $currency->getSign(),
            'weight'               => $weight,
            'labelAmount'          => $extraOptions->getLabelAmount(),
            'digitalStampWeight'   => $digitalStampWeight,
        ]);

        $labelConceptHtmlTpl = $this->context->smarty->createTemplate(
            $this->getTemplatePath() . 'hook/label-concept' . $this->getTemplateSuffix() . '.tpl',
            $labelConceptHtml
        );

        $this->returnAjaxResponse(['labelConceptHtml' => $labelConceptHtmlTpl->fetch()]);
    }

    public function ajaxProcessRefreshLabel()
    {
        $postValues = Tools::getAllValues();
        $labelId = $postValues['id_label'] ?? 0;
        if ((int) $labelId <= 0) {
            $this->errors[] = $this->module->l(
                'No label ID found.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }
        $labelIds = [(int) $labelId];

        $this->refreshLabels($labelIds, (int) $postValues['id_order']);
    }

    public function ajaxProcessBulkActionRefreshLabels()
    {
        $postValues = Tools::getAllValues();
        if (empty($postValues['labelBox'])) {
            $this->errors[] = $this->module->l(
                'No label ID found. Please select at least one label.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }
        $labelIds = [];
        foreach ($postValues['labelBox'] as $idOrderLabel) {
            $orderLabel = new OrderLabel((int) $idOrderLabel);
            if (!empty($orderLabel->id_label)) {
                $labelIds[] = (int) $orderLabel->id_label;
            }
        }
        if (empty($labelIds)) {
            $this->errors[] = $this->module->l(
                'No label found.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }

        $this->refreshLabels($labelIds, (int) $postValues['id_order']);
    }

    /**
     * @param  array      $labelIds
     * @param  int|string $idOrder
     *
     * @return void
     * @throws \Exception
     */
    public function refreshLabels(array $labelIds, $idOrder): void
    {
        try {
            $collection = ConsignmentCollection::findMany(
                $labelIds,
                Configuration::get(
                    Constant::API_KEY_CONFIGURATION_NAME
                )
            );

            $collection->setLinkOfLabels();
            Logger::addLog($collection->toJson());
            $status_provider = new MyparcelStatusProvider();

            foreach ($collection as $consignment) {
                $order_label         = OrderLabel::findByLabelId($consignment->getConsignmentId());
                $order_label->status = $status_provider->getStatus($consignment->getStatus());
                $order_label->save();
            }
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
            $this->errors[] = $this->module->l(
                'An error occurred in the MyParcel module, please try again.',
                'adminlabelcontroller'
            );
            $this->returnAjaxResponse();
        }

        $this->returnAjaxResponse([], (int) $idOrder);
    }

    /**
     * @throws \PrestaShopException
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function ajaxProcessCreateReturnLabel()
    {
        $postValues   = Tools::getAllValues();
        $idOrderLabel = $postValues['id_order_label'] ?? 0;
        $orderLabel   = new OrderLabel((int) $idOrderLabel);

        if (! Validate::isLoadedObject($orderLabel)) {
            $this->errors[] = $this->module->l('Order label not found by ID', 'adminlabelcontroller');
        }
        $idOrder = $postValues['id_order'] ?? 0;
        $order   = new Order((int) $idOrder);

        if (! Validate::isLoadedObject($order) && $order->getId() === (int) $orderLabel->id_order) {
            $this->errors[] = $this->module->l('Order not found by label ID', 'adminlabelcontroller');
            $this->returnAjaxResponse();
        }

        $address         = new Address($order->id_address_delivery);
        $customer        = new Customer($order->id_customer);
        $platformService = PlatformServiceFactory::create();

        try {
            $carrierId   = CarrierService::getMyParcelCarrier($order->getIdCarrier());
            $consignment = ($platformService->generateConsignment($carrierId))
                ->setReferenceIdentifier($order->getId())
                ->setCountry(Country::getIsoById($address->id_country))
                ->setPerson($postValues['label_name'] ?? ($address->firstname . ' ' . $address->lastname))
                ->setFullStreet($address->address1)
                ->setPostalCode($address->postcode)
                ->setCity($address->city)
                ->setEmail($postValues['label_email'] ?? $customer->email)
                ->setContents(1)
                ->setPackageType(isset($postValues['packageType']) ? (int) $postValues['packageType'] : 1)
                // This may be overridden
                ->setLabelDescription($postValues['label_description'] ?? $orderLabel->barcode);

            if (isset($postValues['packageFormat'])) {
                $consignment->setLargeFormat((int) $postValues['packageFormat'] == 2);
            }
            if (isset($postValues['onlyRecipient'])) {
                $consignment->setOnlyRecipient(true);
            }
            if (isset($postValues['signatureRequired'])) {
                $consignment->setSignature(true);
            }
            if (isset($postValues['returnUndelivered'])) {
                $consignment->setReturn(true);
            }
            if (isset($postValues['ageCheck'])) {
                $consignment->setAgeCheck(true);
            }
            if (isset($postValues['insurance'])) {
                $insuranceValue = $postValues['returnInsuranceAmount'] ?? 0;
                if (strpos($insuranceValue, 'amount') !== false) {
                    $insuranceValue = (int) str_replace(
                        'amount',
                        '',
                        $insuranceValue
                    );
                }
                if ((int) $insuranceValue == -1) {
                    $insuranceValue = $postValues['insurance-amount-custom-value'] ?? 0;
                }
                $consignment->setInsurance((int) $insuranceValue * 100);
            }

            $collection = (new ConsignmentCollection())
                ->addConsignment($consignment)
                ->setPdfOfLabels()
                ->generateReturnConsignments(true);
            Logger::addLog($collection->toJson());

            $consignment                 = $collection->first();
            $orderLabel                  = new OrderLabel();
            $orderLabel->id_label        = $consignment->getConsignmentId();
            $orderLabel->id_order        = $consignment->getReferenceId();
            $orderLabel->barcode         = $consignment->getBarcode();
            $orderLabel->track_link      = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $status_provider             = new MyparcelStatusProvider();
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status          = $status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
        } catch (Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
            $this->errors[] = $this->module->l(
                'An error occurred in the MyParcel module, please try again.',
                'adminlabelcontroller'
            );
            $this->errors[] = $e->getMessage();
            $this->returnAjaxResponse();
        }

        $this->returnAjaxResponse([], (int) $idOrder);
    }

    /**
     * @param  \Exception $e
     *
     * @return string
     */
    private function getParsedErrorMessage(Exception $e): string
    {
        $parsedErrorMessage = (new ErrorMessage($this->module))->get($e->getMessage());

        if (empty($parsedErrorMessage)) {
            $parsedErrorMessage = _PS_MODE_DEV_
                ? $e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage()
                : $this->module->l(
                    'An error occurred in MyParcel module, please try again.',
                    'adminlabelcontroller'
                );
        }

        return $parsedErrorMessage;
    }

    /**
     * @return string
     */
    private function getTemplateSuffix(): string
    {
        $suffix = '';

        if (version_compare(_PS_VERSION_, '1.7.7.0', '>=')) {
            $suffix = '-177';
        }

        return $suffix;
    }

    /**
     * @param  \Exception $e
     *
     * @return void
     */
    private function logError(Exception $e): void
    {
        Logger::addLog($e->getMessage(), true, true);
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     * @param  int                                                       $orderId
     *
     * @return bool
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function validateConsignmentInvoice(AbstractConsignment $consignment, int $orderId): bool
    {
        if ($consignment->isCdCountry()) {
            $products = OrderLabel::getCustomsOrderProducts($orderId);
            if ($products) {
                $orderObject = new Order($orderId);
                if (! $orderObject->hasInvoice()) {
                    $this->errors[] = sprintf(
                        $this->module->l('International order ID#%s must have invoice.', 'adminlabelcontroller'),
                        $orderId
                    );
                    return false;
                }
            }
        }
        return true;
    }

    public function fixPastDeliveryDate(?string $deliveryDate): ?string
    {
        if (!$deliveryDate) {
            return $deliveryDate;
        }
        $tomorrow = new DateTime('tomorrow');
        try {
            $deliveryDateObj = new DateTime($deliveryDate);
        } catch (Exception $e) {
            return $tomorrow->format('Y-m-d H:i:s');
        }
        $oldDate = clone $deliveryDateObj;
        $tomorrow->setTime(0, 0, 0, 0);
        $oldDate->setTime(0, 0, 0, 0);
        if ($tomorrow > $oldDate) {
            do {
                $deliveryDateObj->add(new DateInterval('P1D'));
            } while ($tomorrow > $deliveryDateObj || $deliveryDateObj->format('w') == 0);
            $deliveryDate = $deliveryDateObj->format('Y-m-d H:i:s');
        }

        return $deliveryDate;
    }

    /**
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public function sanitizeSignature(AbstractConsignment $consignment): void
    {
        if (! $consignment->canHaveShipmentOption(AbstractConsignment::SHIPMENT_OPTION_SIGNATURE)
            || $consignment->getCountry() !== $this->module->getModuleCountry()) {
            $consignment->setSignature(false);
        }
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return void
     */
    public function sanitizeDeliveryType(AbstractConsignment $consignment): void
    {
        if (
            AbstractConsignment::PACKAGE_TYPE_PACKAGE === $consignment->getPackageType()
            &&
            ! $this->module->isBE()
        ) {
            return;
        }

        $consignment->setDeliveryType(
            $consignment->getDeliveryType() < AbstractConsignment::DELIVERY_TYPE_PICKUP
                ? AbstractConsignment::DELIVERY_TYPE_STANDARD
                : AbstractConsignment::DELIVERY_TYPE_PICKUP
        );
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return void
     * @throws \Exception
     * @throws \Exception
     */
    public function sanitizePackageType(AbstractConsignment $consignment): void
    {
        if ($this->module->isBE()) {
            $consignment->setPackageType(AbstractConsignment::PACKAGE_TYPE_PACKAGE);
        }
    }

}

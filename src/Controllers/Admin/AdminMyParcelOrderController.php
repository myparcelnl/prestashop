<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Exception;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Hooks\AdminPanelRenderService;
use Gett\MyparcelBE\Module\Tools\Tools;
use MyParcelNL\Sdk\src\Support\Arr;
use OrderLabel;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelOrderController extends AbstractAdminController
{
    /**
     * @var \Gett\MyparcelBE\Controllers\Admin\AdminOrderService
     */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AdminOrderService();
    }

    /**
     * Called from shipment options modal and "New shipment (& print)" buttons on single order view.
     *
     * @param  bool $print
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export(bool $print = false): Response
    {
        $orderIds    = $this->service->getPostedIds('orderIds');
        $orderLabels = [];

        foreach ($orderIds as $orderId) {
            try {
                $collection    = $this->service->exportOrder($orderId);
                $orderLabels[] = $this->service->getOrderLabels($collection);
            } catch (Exception $e) {
                $this->addError($e, "Error exporting order $orderId: " . $e->getMessage());
            }
        }

        $orderLabels = Arr::collapse($orderLabels);
        $response    = ['shipmentLabels' => $orderLabels];

        if ($print && ! $this->hasErrors()) {
            try {
                $response += $this->service->printLabels(Arr::pluck($orderLabels, 'id_label'));
            } catch (Exception $e) {
                $this->addError($e);
            }
        }

        return $this->sendResponse($response);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function exportPrint(): Response
    {
        return $this->export(true);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getShipmentOptionsContext(): Response
    {
        $orderId = Tools::getValue('orderId', null);

        try {
            $order         = $orderId ? new Order($orderId) : null;
            $renderService = AdminPanelRenderService::getInstance();
            $this->setResponse([
                'context' => $renderService->getShipmentOptionsContext($order),
            ]);
        } catch (Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function print(): Response
    {
        $orderIds = $this->service->getPostedIds('orderIds');
        $labelIds = OrderLabel::getOrdersLabels($orderIds);

        try {
            $response = $this->service->printLabels($labelIds);
            $this->setResponse($response);
        } catch (Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refreshLabels(): Response
    {
        $orderIds = $this->service->getPostedIds('orderIds');
        $labelIds = OrderLabel::getOrdersLabels($orderIds);

        try {
            $response = $this->service->refreshLabels($labelIds);
            $this->setResponse($response);
        } catch (Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse();
    }

    /**
     * Called by "Save" button in MyParcel settings on single order view.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function saveDeliveryOptions(): Response
    {
        $newDeliveryOptions = $this->service->updateDeliveryOptions(
            $this->service->getOrder(Tools::getValue('orderId', null)),
            Tools::getAllValues()
        );

        return $this->sendResponse(['deliveryOptions' => $newDeliveryOptions->toArray()]);
    }
}

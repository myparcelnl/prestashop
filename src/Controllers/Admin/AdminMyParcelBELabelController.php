<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Exception;
use OrderLabel;
use Symfony\Component\HttpFoundation\Response;

if (file_exists(_PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php')) {
    require_once _PS_MODULE_DIR_ . 'myparcelbe/vendor/autoload.php';
}

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelBELabelController extends AbstractAdminController
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReturnLabel(): Response
    {
        try {
            $returnLabel = $this->service->createReturnLabel($this->service->getPostedOrderLabelIds());
            $this->setResponse([$returnLabel]);
        } catch (Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse();
    }

    /**
     * Called when deleting a label from single order view.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(): Response
    {
        $labelIds      = $this->service->getPostedOrderLabelIds();
        $orderLabels   = OrderLabel::findByLabelIds($labelIds);
        $deletedLabels = [];

        foreach ($orderLabels as $orderLabel) {
            try {
                $result = $orderLabel->delete();

                if (! $result) {
                    $this->addError(null, 'Could not delete label ' . $orderLabel->id);
                    continue;
                }

                $deletedLabels[] = $orderLabel->id_label;
            } catch (Exception $e) {
                $this->addError($e);
            }
        }

        return $this
            ->setResponse(['labelIds' => $deletedLabels])
            ->sendResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function print(): Response
    {
        $labelIds = $this->service->getPostedOrderLabelIds();

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
    public function refresh(): Response
    {
        try {
            $this->setResponse([
                'shipmentLabels' => $this->service->refreshLabels($this->service->getPostedOrderLabelIds()),
            ]);
        } catch (Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse();
    }
}

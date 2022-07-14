<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Exception;
use Gett\MyparcelBE\Service\AdminOrderService;
use OrderLabel;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelLabelController extends AbstractAdminController
{
    /**
     * @var \Gett\MyparcelBE\Service\AdminOrderService
     */
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AdminOrderService($this->configuration);
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

        [$response, $errors] = $this->service->printLabels($labelIds);

        $this->setResponse($response + $this->refreshLabels());
        $this->addErrors($errors);

        return $this->sendResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function refresh(): Response
    {
        $this->setResponse($this->refreshLabels());

        return $this->sendResponse();
    }

    /**
     * @return array
     */
    private function refreshLabels(): array
    {
        $labels = [];

        try {
            $labels = $this->service->refreshLabels($this->service->getPostedOrderLabelIds());
        } catch (Exception $e) {
            $this->addError($e);
        }

        return ['shipmentLabels' => $labels];
    }
}

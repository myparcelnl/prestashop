<?php

namespace Gett\MyparcelBE\Service\Consignment;

use Doctrine\ORM\EntityManagerInterface;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Service\MyparcelStatusProvider;
use OrderLabel;

class Create
{
    /**
     * @var \Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory
     */
    private $consignment_factory;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entity_manager;

    /**
     * @var \Gett\MyparcelBE\Service\MyparcelStatusProvider
     */
    private $status_provider;

    /**
     * @param  \Doctrine\ORM\EntityManagerInterface                    $entityManager
     * @param  \Gett\MyparcelBE\Service\MyparcelStatusProvider         $statusProvider
     * @param  \Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory $factory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MyparcelStatusProvider $statusProvider,
        ConsignmentFactory     $factory
    ) {
        $this->entity_manager      = $entityManager;
        $this->status_provider     = $statusProvider;
        $this->consignment_factory = $factory;
    }

    /**
     * @param  array $orders
     *
     * @return bool
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createLabels(array $orders): bool
    {
        if (isset($orders['id_order'])) {
            $collection = $this->consignment_factory->fromOrder($orders);
        } else {
            $collection = $this->consignment_factory->fromOrders($orders);
        }

        $this->process($collection);

        return true;
    }

    /**
     * @param  array $order
     *
     * @return bool
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createReturnLabel(array $order): bool
    {
        $collection = $this->consignment_factory->fromOrder($order);

        $this->process($collection, true);

        return true;
    }

    /**
     * @param  \Gett\MyparcelBE\Collection\ConsignmentCollection $collection
     * @param  bool                                              $return
     *
     * @return void
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function process(ConsignmentCollection $collection, bool $return = false): void
    {
        $collection->setPdfOfLabels();

        if ($return) {
            $collection->generateReturnConsignments(true);
        }

        foreach ($collection as $consignment) {
            $orderLabel                  = new OrderLabel();
            $orderLabel->id_label        = $consignment->getConsignmentId();
            $orderLabel->id_order        = $consignment->getReferenceId();
            $orderLabel->barcode         = $consignment->getBarcode();
            $orderLabel->track_link      = $consignment->getBarcodeUrl(
                $consignment->getBarcode(),
                $consignment->getPostalCode(),
                $consignment->getCountry()
            );
            $orderLabel->new_order_state = $consignment->getStatus();
            $orderLabel->status          = $this->status_provider->getStatus($consignment->getStatus());
            $orderLabel->add();
        }
    }
}

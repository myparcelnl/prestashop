<?php

namespace Gett\MyparcelBE\Service\Consignment;

use Doctrine\ORM\EntityManagerInterface;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Service\MyparcelStatusProvider;
use OrderLabel;

class Update
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entity_manager;

    /**
     * @var \Gett\MyparcelBE\Service\MyparcelStatusProvider
     */
    private $status_provider;

    /**
     * @param  string                                          $apiKey
     * @param  \Doctrine\ORM\EntityManagerInterface            $entityManager
     * @param  \Gett\MyparcelBE\Service\MyparcelStatusProvider $statusProvider
     */
    public function __construct(
        string                 $apiKey,
        EntityManagerInterface $entityManager,
        MyparcelStatusProvider $statusProvider
    ) {
        $this->api_key         = $apiKey;
        $this->entity_manager  = $entityManager;
        $this->status_provider = $statusProvider;
    }

    /**
     * @param  array $labelIds
     *
     * @return bool
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function updateLabel(array $labelIds): bool
    {
        $collection = ConsignmentCollection::findMany($labelIds, $this->api_key);
        $collection->setLinkOfLabels();

        foreach ($collection as $consignment) {
            $orderLabel         = OrderLabel::findByLabelId($consignment->getConsignmentId());
            $orderLabel->status = $this->status_provider->getStatus($consignment->getStatus());
            $orderLabel->save();
        }

        return true;
    }
}

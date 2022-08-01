<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Address;
use Configuration;
use Country;
use Customer;
use Exception;
use Gett\MyparcelBE\Adapter\DeliveryOptionsFromFormAdapter;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptions;
use Gett\MyparcelBE\DeliveryOptions\DeliveryOptionsMerger;
use Gett\MyparcelBE\DeliverySettings\ExtraOptions;
use Gett\MyparcelBE\Factory\Consignment\ConsignmentFactory;
use Gett\MyparcelBE\Factory\OrderSettingsFactory;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\OrderLogger;
use Gett\MyparcelBE\Model\Core\Order;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\Consignment\Download;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use Gett\MyparcelBE\Timer;
use InvalidArgumentException;
use MyParcelBE;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use OrderLabel;
use Validate;
use Throwable;

class AdminOrderService extends AbstractService
{
    /**
     * @param  array                                                                           $postValues
     * @param  \Gett\MyparcelBE\Model\Core\Order                                               $order
     * @param  \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter|null $deliveryOptions
     *
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function createConsignments(
        array                          $postValues,
        Order                          $order,
        AbstractDeliveryOptionsAdapter $deliveryOptions = null
    ): ConsignmentCollection {
        $factory      = new ConsignmentFactory($postValues);
        $collection   = $factory->fromOrder($order, $deliveryOptions);
        $conceptFirst = ConsignmentFactory::isConceptFirstConfiguration();

        $conceptFirst
            ? $collection->createConcepts()
            : $collection->setLinkOfLabels();

        OrderLogger::addLog([
            'message' => sprintf(
                'Creating %s: %s',
                $conceptFirst ? 'concepts' : 'labels',
                $collection->toJson()
            ),
            'order'   => $order,
        ]);

        if (($postValues[Constant::RETURN_PACKAGE_CONFIGURATION_NAME] ?? 0)
            && MyParcelBE::getModule()
                ->isNL()) {
            $collection->generateReturnConsignments(true);
        }

        return $collection;
    }

    /**
     * @param  array $labelIds
     *
     * @return \OrderLabel
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    public function createReturnLabel(array $labelIds): OrderLabel
    {
        $orderLabels = OrderLabel::findByLabelIds($labelIds);
        $orderLabel  = reset($orderLabels);
        $order       = $this->getOrder($orderLabel->id_order);

        $address         = new Address($order->id_address_delivery);
        $customer        = new Customer($order->id_customer);
        $platformService = PlatformServiceFactory::create();
        $carrier         = CarrierService::getMyParcelCarrier($order->getIdCarrier());
        $postValues      = Tools::getAllValues();
        $packageType     = ((int) $postValues['packageType']) ?: AbstractConsignment::DEFAULT_PACKAGE_TYPE;
        $largeFormat     = Constant::PACKAGE_FORMAT_LARGE === ((int) $postValues['largeFormat']);

        $consignment = ($platformService->generateConsignment($carrier))
            ->setConsignmentId((int) $orderLabel->id_label)
            ->setReferenceIdentifier((string) $order->getId())
            ->setCountry(Country::getIsoById($address->id_country))
            ->setPerson(sprintf('%s %s', $address->firstname, $address->lastname))
            ->setFullStreet($address->address1)
            ->setPostalCode($address->postcode)
            ->setCity($address->city)
            ->setEmail($customer->email)
            ->setContents(5)
            ->setPackageType($packageType)

            // This may be overridden
            ->setLabelDescription($postValues['labelDescription'] ?? $orderLabel->barcode)
            ->setLargeFormat($largeFormat);

        $collection = (new ConsignmentCollection())
            ->addConsignment($consignment)
            ->generateReturnConsignments(true);

        OrderLogger::addLog(['message' => 'Creating return shipments: ' . $collection->toJson(), 'order' => $order]);

        $consignment                 = $collection->where('status', '!=', null)->first();
        $orderLabel                  = new OrderLabel();
        $orderLabel->id_label        = $consignment->getConsignmentId();
        $orderLabel->id_order        = $consignment->getReferenceId();
        $orderLabel->new_order_state = $consignment->getStatus();
        $orderLabel->status          = MyParcelStatusProvider::getInstance()
            ->getStatus($consignment->getStatus());
        $orderLabel->add();

        return $orderLabel;
    }

    /**
     * @param  int $orderId
     *
     * @return \Gett\MyparcelBE\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function exportOrder(int $orderId): ConsignmentCollection
    {
        OrderLogger::addLog(['message' => 'Starting export', 'order' => $orderId]);
        $postValues      = $this->setLabelOptionsInsurance(Tools::getAllValues());
        $order           = $this->getOrder($orderId);
        $deliveryOptions = $this->updateDeliveryOptions($order, $postValues);
        $collection      = $this->createConsignments($postValues, $order, $deliveryOptions);
        $consignment     = $collection->first();

        if (! ConsignmentFactory::isConceptFirstConfiguration()) {
            OrderLabel::updateOrderTrackingNumber($order, $consignment->getBarcode());
        }

        return $collection;
    }

    /**
     * @param  mixed $orderId
     *
     * @return \Gett\MyparcelBE\Model\Core\Order
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrder($orderId): Order
    {
        if (! $orderId) {
            throw new InvalidArgumentException('No order ID found.');
        }

        $order = new Order((int) $orderId);
        if (! Validate::isLoadedObject($order)) {
            throw new InvalidArgumentException("Order $orderId not found");
        }

        return $order;
    }

    /**
     * @param  \Gett\MyparcelBE\Collection\ConsignmentCollection $collection
     *
     * @return array[]
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getOrderLabels(ConsignmentCollection $collection): array
    {
        $orderLabels = [];
        foreach ($collection as $consignment) {
            $orderLabel = OrderLabel::createFromConsignment($consignment);

            if (! $orderLabel) {
                continue;
            }

            if ($consignment->isPartOfMultiCollo()) {
                $orderLabels[] = $orderLabel;
                return $orderLabels;
            }

            $orderLabels[] = $orderLabel;
        }

        return $orderLabels;
    }

    /**
     * @param  string $key
     *
     * @return int[]
     */
    public function getPostedIds(string $key): array
    {
        $value = $this->getValue($key);
        return array_map('intval', explode(',', urldecode($value)));
    }

    /**
     * @return int[]
     */
    public function getPostedOrderLabelIds(): array
    {
        $labelIds = $this->getPostedIds('labelIds');
        $this->validateOrderLabels($labelIds);
        return $labelIds;
    }

    /**
     * @param  array $labelIds
     *
     * @return array
     */
    public function printLabels(array $labelIds): array
    {
        $response = [];
        $errors   = [];

        try {
            $response = $this->downloadLabels($labelIds);
        } catch (Exception $e) {
            ApiLogger::addLog('Error printing labels: ' . implode(', ', $labelIds));
            ApiLogger::addLog($e);
            $errors[] = $e;
        }

        return [$response, $errors];
    }

    /**
     * @param  int[] $labelIds
     *
     * @return array
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public function refreshLabels(array $labelIds): array
    {
        $apiKey     = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);
        $collection = ConsignmentCollection::findMany($labelIds, $apiKey);
        $collection->setLinkOfLabels();

        $orderLabels = [];

        foreach ($collection as $consignment) {
            try {
                $orderLabels[] = $this->consignmentToOrderLabel($consignment);
            } catch (Throwable $exception) {
                /*
                 * Throws error when looping through multicollo shipments, because subsequent consignments don't have
                 * an associated OrderLabel. Suppressed like this until it's solved in the pdk.
                 */
            }
        }

        return $orderLabels;
    }

    /**
     * @param  \MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment $consignment
     *
     * @return \OrderLabel
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function consignmentToOrderLabel(AbstractConsignment $consignment): OrderLabel
    {
        $orderLabel             = OrderLabel::findByLabelId($consignment->getConsignmentId());
        $orderLabel->barcode    = $consignment->getBarcode();
        $orderLabel->status     = MyParcelStatusProvider::getInstance()
            ->getStatus($consignment->getStatus());
        $orderLabel->track_link = $consignment->getBarcodeUrl(
            $consignment->getBarcode(),
            $consignment->getPostalCode(),
            $consignment->getCountry()
        );
        $orderLabel->save();

        $order = new Order((int) $orderLabel->id_order);

        OrderLogger::addLog(
            [
                'order'   => $order,
                'message' => "Refreshed label $orderLabel->id_label",
            ]
        );

        return $orderLabel;
    }


    private function setLabelOptionsInsurance(array $postValues): array
    {
        try {
            $hasInsurance                            =
                ('0' !== $postValues['deliveryOptions']['shipmentOptions']['insurance']);
            $postValues['labelOptions']['insurance'] = $hasInsurance;
        } catch (\Throwable $e) {
            /**
             * When one or both fields are not present, there is no need to adjust any of them.
             */
        }

        return $postValues;
    }

    /**
     * @param  \Gett\MyparcelBE\Model\Core\Order $order
     * @param  array                             $values
     *
     * @return \MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function updateDeliveryOptions(Order $order, array $values): AbstractDeliveryOptionsAdapter
    {
        $orderDeliveryOptions = OrderSettingsFactory::create($order)->getDeliveryOptions();
        $deliveryOptions      = DeliveryOptionsMerger::create(
            $orderDeliveryOptions,
            new DeliveryOptionsFromFormAdapter($values)
        );

        if (isset($values['extraOptions'])) {
            $extraOptionsArray = (new ExtraOptions($values['extraOptions']))->toArray();
        }

        DeliveryOptions::save($order->getIdCart(), $deliveryOptions->toArray(), $extraOptionsArray ?? []);
        return $deliveryOptions;
    }

    /**
     * @param  int[] $labelIds
     *
     * @return \Exception[]
     */
    public function updateOrderLabelStatusesAfterPrint(array $labelIds): array
    {
        $status = (int) $this->configuration->get(Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME);

        foreach ($labelIds as $labelId) {
            try {
                $timer = new Timer();
                OrderLabel::updateStatus($labelId, $status);
                ApiLogger::addLog("Updating status for $labelId took {$timer->getTimeTaken()}ms");
            } catch (Exception $e) {
                $errors[] = $e;
                ApiLogger::addLog($e, ApiLogger::ERROR);
            }
        }

        return $errors ?? [];
    }

    /**
     * @param  array $labelIds
     *
     * @return array
     * @throws \Exception
     */
    private function downloadLabels(array $labelIds): array
    {
        if (empty($labelIds)) {
            throw new InvalidArgumentException('No labels found');
        }

        $service       = new Download();
        $labelUrlOrPdf = $service->downloadLabel($labelIds);

        return [
            'label_ids' => $labelIds,
            'pdf'       => $labelUrlOrPdf,
        ];
    }

    /**
     * @param  string $key
     *
     * @return mixed
     */
    private function getValue(string $key)
    {
        $value = Tools::getValue($key, null);

        if (! $value) {
            throw new InvalidArgumentException("Parameter '$key' missing.");
        }

        return $value;
    }

    /**
     * @param  int[] $labelIds
     *
     * @return void
     */
    private function validateOrderLabels(array $labelIds): void
    {
        $orderLabels = OrderLabel::findByLabelIds($labelIds);
        $difference  = array_diff($labelIds, Arr::pluck($orderLabels, 'id_label'));

        if (! empty($difference)) {
            throw new InvalidArgumentException(sprintf('Order label(s) not found. %s', implode(',', $difference)));
        }
    }
}

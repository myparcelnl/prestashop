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
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
use OrderLabel;
use Validate;

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
        $factory        = new ConsignmentFactory($postValues);
        $collection     = $factory->fromOrder($order, $deliveryOptions);

        if (! ConsignmentFactory::isConceptFirstConfiguration()) {
            $collection->setLinkOfLabels();
        } else {
            $collection->createConcepts();
        }

        OrderLogger::addLog([
            'message' => sprintf('Creating consignments: %s', $collection->toJson()),
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

        $carrierId   = CarrierService::getMyParcelCarrier($order->getIdCarrier());
        $consignment = ($platformService->generateConsignment($carrierId))
            ->setReferenceIdentifier((string) $order->getId())
            ->setCountry(Country::getIsoById($address->id_country))
            ->setPerson($postValues['label_name'] ?? ($address->firstname . ' ' . $address->lastname))
            ->setFullStreet($address->address1)
            ->setPostalCode($address->postcode)
            ->setCity($address->city)
            ->setEmail($postValues['label_email'] ?? $customer->email)
            ->setContents(1)
            ->setPackageType(
                isset($postValues['packageType']) ? (int) $postValues['packageType']
                    : AbstractConsignment::DEFAULT_PACKAGE_TYPE
            )
            // This may be overridden
            ->setLabelDescription($postValues['label_description'] ?? $orderLabel->barcode);

        if (isset($postValues['packageFormat'])) {
            $consignment->setLargeFormat(Constant::PACKAGE_FORMAT_LARGE === (int) $postValues['packageFormat']);
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

            if (Str::contains($insuranceValue, 'amount')) {
                $insuranceValue = (int) str_replace('amount', '', $insuranceValue);
            }

            if (-1 === (int) $insuranceValue) {
                $insuranceValue = $postValues['insurance-amount-custom-value'] ?? 0;
            }

            $consignment->setInsurance((int) $insuranceValue * 100);
        }

        $collection = (new ConsignmentCollection())
            ->addConsignment($consignment)
            ->setPdfOfLabels()
            ->generateReturnConsignments(true);

        OrderLogger::addLog(['message' => 'Creating return shipments: ' . $collection->toJson(), 'order' => $order]);

        $consignment            = $collection->first();
        $orderLabel             = new OrderLabel();
        $orderLabel->id_label   = $consignment->getConsignmentId();
        $orderLabel->id_order   = $consignment->getReferenceId();
        $orderLabel->barcode    = $consignment->getBarcode();
        $orderLabel->track_link = $consignment->getBarcodeUrl(
            $consignment->getBarcode(),
            $consignment->getPostalCode(),
            $consignment->getCountry()
        );

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
     * @throws \PrestaShopException
     */
    public function refreshLabels(array $labelIds): array
    {
        $apiKey     = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);
        $collection = ConsignmentCollection::findMany($labelIds, $apiKey);
        $collection->setLinkOfLabels();

        $orderLabels = [];

        foreach ($collection as $consignment) {
            $orderLabels[] = $this->consignmentToOrderLabel($consignment);
        }

        return $orderLabels;
    }

    /**
     * @param  object $consignment
     *
     * @return object $orderLabel
     * @throws \PrestaShopException
     */
    private function consignmentToOrderLabel(object $consignment): object
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

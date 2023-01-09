<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Address;
use Configuration;
use Country;
use Customer;
use Exception;
use MyParcelNL\Pdk\Shipment\Service\DeliveryOptionsMerger;
use MyParcelNL\PrestaShop\Adapter\DeliveryOptionsFromFormAdapter;
use MyParcelNL\PrestaShop\Collection\ConsignmentCollection;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\DeliverySettings\ExtraOptions;
use MyParcelNL\PrestaShop\Factory\Consignment\ConsignmentFactory;
use MyParcelNL\PrestaShop\Factory\OrderSettingsFactory;
use MyParcelNL\PrestaShop\Model\Core\Order;
use MyParcelNL\PrestaShop\Module\Facade\ModuleService;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\PrestaShop\Pdk\Facade\OrderLogger;
use MyParcelNL\PrestaShop\Service\Consignment\Download;
use MyParcelNL\PrestaShop\Service\Platform\PlatformServiceFactory;
use MyParcelNL\PrestaShop\Timer;
use InvalidArgumentException;
use MyParcelNL;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;
use MyParcelNL\Sdk\src\Support\Arr;
use OrderLabel;
use Throwable;
use Validate;

/**
 * @deprecated
 */
class AdminOrderService extends AbstractService
{
    /**
     * @var \MyParcelNL\Pdk\Base\Service\CountryService
     */
    private $countryService;

    /**
     * @param  \MyParcelNL                                 $module
     * @param  \MyParcelNL\Pdk\Base\Service\CountryService $countryService
     */
    public function __construct(MyParcelNL $module, CountryService $countryService)
    {
        parent::__construct($module);
        $this->countryService = $countryService;
    }

    /**
     * @param  array                                                                           $postValues
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order                                               $order
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions|null $deliveryOptions
     *
     * @return \MyParcelNL\PrestaShop\Collection\ConsignmentCollection
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
        DeliveryOptions $deliveryOptions = null
    ): ConsignmentCollection {
        $factory      = new ConsignmentFactory($postValues);
        $collection   = $factory->fromOrder($order, $deliveryOptions);
        $conceptFirst = ConsignmentFactory::isConceptFirstConfiguration();

        $conceptFirst
            ? $collection->createConcepts()
            : $collection->setLinkOfLabels();

        OrderLogger::debug(
            sprintf('Creating %s', $conceptFirst ? 'concepts' : 'labels'),
            [
                'order' => $order,
                'collection' => $collection->toArray(),
            ]
        );

        if (($postValues[Constant::RETURN_PACKAGE_CONFIGURATION_NAME] ?? 0) && ModuleService::isNL()) {
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

        OrderLogger::debug(
            'Creating return shipments',
            [
                'order' => $order,
                'collection' => $collection->toArray(),
            ]
        );

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
     * @return \MyParcelNL\PrestaShop\Collection\ConsignmentCollection
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function exportOrder(int $orderId): ConsignmentCollection
    {
        OrderLogger::debug('Starting export', ['order' => $orderId]);
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
     * @return \MyParcelNL\PrestaShop\Model\Core\Order
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
     * @param  \MyParcelNL\PrestaShop\Collection\ConsignmentCollection $collection
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

            $orderLabels[] = $orderLabel;

            if ($consignment->isPartOfMultiCollo()) {
                return $orderLabels;
            }
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
        } catch (Throwable $exception) {
            DefaultLogger::debug('Error while printing labels', compact('exception', 'labelIds'));
            $errors[] = $exception;
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

        OrderLogger::debug(
            'Refreshed label',
            [
                'order' => $order,
                'orderLabel' => $orderLabel->id_label,
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
        } catch (Throwable $e) {
            /**
             * When one or both fields are not present, there is no need to adjust any of them.
             */
        }

        return $postValues;
    }

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Core\Order $order
     * @param  array                             $values
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions
     * @throws \PrestaShopDatabaseException
     * @throws \Exception
     */
    public function updateDeliveryOptions(Order $order, array $values): DeliveryOptions
    {
        $orderDeliveryOptions = OrderSettingsFactory::create($order)
            ->getDeliveryOptions();

        $deliveryOptions = DeliveryOptionsMerger::create([
            $orderDeliveryOptions,
            (new DeliveryOptionsFromFormAdapter($values)),
        ]);

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
        $status = (int) Configuration::get(Constant::LABEL_CREATED_ORDER_STATUS_CONFIGURATION_NAME);

        foreach ($labelIds as $labelId) {
            try {
                $timer = new Timer();
                OrderLabel::updateStatus($labelId, $status);
                DefaultLogger::debug(
                    'Updated status for label',
                    ['labelId' => $labelId, 'timeTaken' => $timer->getTimeTaken()]
                );
            } catch (Exception $exception) {
                $errors[] = $exception;
                DefaultLogger::error($exception->getMessage(), compact('exception'));
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

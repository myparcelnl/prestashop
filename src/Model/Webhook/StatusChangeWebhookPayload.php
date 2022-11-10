<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Model\Webhook;

use OrderLabel;

class StatusChangeWebhookPayload implements AbstractWebhookPayload
{
    public const REQUIRED_PROPERTIES = [
        'account_id',
        'barcode',
        'shipment_id',
        'shipment_reference_identifier',
        'shop_id',
        'status',
    ];

    public const SHIPMENT_STATUS_PENDING_CONCEPT       = 1;
    public const SHIPMENT_STATUS_PENDING_REGISTERED    = 2;
    public const SHIPMENT_STATUS_PRINTED_LETTER        = 12;
    public const SHIPMENT_STATUS_PRINTED_DIGITAL_STAMP = 14;

    public const STATUS_CHANGE_DURING_EXPORT_NOT_WEBHOOK = [
        self::SHIPMENT_STATUS_PENDING_CONCEPT,
        self::SHIPMENT_STATUS_PENDING_REGISTERED,
        self::SHIPMENT_STATUS_PRINTED_LETTER,
        self::SHIPMENT_STATUS_PRINTED_DIGITAL_STAMP,
    ];

    /**
     * @var int|null
     */
    private $accountId;

    /**
     * @var string|null
     */
    private $barcode;

    /**
     * @var int|null
     */
    private $shipmentId;

    /**
     * @var string|null
     */
    private $shipmentReferenceIdentifier;

    /**
     * @var int|null
     */
    private $shopId;

    /**
     * @var int|null
     */
    private $status;

    /**
     * @param  array $hookData
     */
    public function __construct(array $hookData)
    {
        $this->accountId                   = $hookData['account_id'] ?? null;
        $this->barcode                     = $hookData['barcode'] ?? null;
        $this->shipmentId                  = $hookData['shipment_id'] ?? null;
        $this->shipmentReferenceIdentifier = $hookData['shipment_reference_identifier'] ?? null;
        $this->shopId                      = $hookData['shop_id'] ?? null;
        $this->status                      = $hookData['status'] ?? null;
    }

    /**
     * @return int|null
     */
    public function getAccountId(): ?int
    {
        return $this->accountId;
    }

    /**
     * @return string|null
     */
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    /**
     * @return int|null
     */
    public function getShipmentId(): ?int
    {
        return $this->shipmentId;
    }

    /**
     * @return string|null
     */
    public function getShipmentReferenceIdentifier(): ?string
    {
        return $this->shipmentReferenceIdentifier;
    }

    /**
     * @return int|null
     */
    public function getShopId(): ?int
    {
        return $this->shopId;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * Find the related order using shipment id and update its status.
     *
     * @throws \Exception
     */
    public function onReceive(): void
    {
        if (in_array($this->getStatus(), self::STATUS_CHANGE_DURING_EXPORT_NOT_WEBHOOK)) {
            return;
        }

        OrderLabel::updateStatus((int) $this->shipmentId, (int) $this->status);
    }
}

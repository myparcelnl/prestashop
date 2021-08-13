<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Model\Webhook;

interface AbstractWebhookPayload
{
    /**
     * @param  array $hookData
     */
    public function __construct(array $hookData);

    /**
     * Logic that should be executed on receiving the webhook.
     */
    public function onReceive(): void;
}

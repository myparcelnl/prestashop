<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Model\Webhook;

class WebhookPayloadFactory
{
    /**
     * @param  array $payload
     *
     * @return \Gett\MyparcelBE\Model\Webhook\AbstractWebhookPayload
     * @throws \Gett\MyparcelBE\Model\Webhook\WebhookException
     */
    public static function create(array $payload): AbstractWebhookPayload
    {
        if (self::hasAllKeys($payload, StatusChangeWebhookPayload::REQUIRED_PROPERTIES)) {
            return new StatusChangeWebhookPayload($payload);
        }

        self::throwException();
    }

    /**
     * @param  array $hookData
     * @param  array $properties
     *
     * @return bool
     */
    private static function hasAllKeys(array $hookData, array $properties): bool
    {
        return ! array_diff_key(array_flip($properties), $hookData);
    }

    /**
     * @throws \Gett\MyparcelBE\Model\Webhook\WebhookException
     */
    private static function throwException(): void
    {
        throw new WebhookException('Webhook payload not recognized');
    }
}

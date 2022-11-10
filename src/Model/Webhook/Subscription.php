<?php

namespace MyParcelNL\PrestaShop\Model\Webhook;

use Exception;
use MyParcelNL\Sdk\src\Support\Str;

class Subscription
{
    public const SHIPMENT_STATUS_CHANGE_HOOK_NAME = 'shipment_status_change';
    public const SHIPMENT_LABEL_CREATED_HOOK_NAME = 'shipment_label_created';

    private $id;
    private $hook;
    private $url;
    private $account_id;
    private $shop_id;

    /**
     * @param  string   $hook
     * @param  string   $url
     * @param  int|null $id
     * @param  int|null $account_id
     * @param  int|null $shop_id
     *
     * @throws \Exception
     */
    public function __construct(string $hook, string $url, int $id = null, int $account_id = null, int $shop_id = null)
    {
        $this->hook = $this->validateHookParam($hook);
        $this->url = $this->validateUrlParam($url);
        $this->id = $id;
        $this->account_id = $account_id;
        $this->shop_id = $shop_id;
    }

    public function encode(): string
    {
        $array['data']['webhook_subscriptions'][] = array_filter([
            'id' => $this->id,
            'hook' => $this->hook,
            'url' => $this->url,
            'account_id' => $this->account_id,
            'shop_id' => $this->shop_id,
        ]);

        return str_replace('\\n', ' ', json_encode($array));
    }

    /**
     * @param  string $hook
     *
     * @return string
     * @throws \Exception
     */
    private function validateHookParam(string $hook): string
    {
        if ($hook !== self::SHIPMENT_LABEL_CREATED_HOOK_NAME && $hook !== self::SHIPMENT_STATUS_CHANGE_HOOK_NAME) {
            throw new Exception('Unsupported hook name');
        }

        return $hook;
    }

    /**
     * @param  string $url
     *
     * @return string
     * @throws \Exception
     */
    private function validateUrlParam(string $url): string
    {
        if (!Str::startsWith($url, 'https://')) {
            throw new Exception('Webhook url should be https');
        }

        return $url;
    }
}

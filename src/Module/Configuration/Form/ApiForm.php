<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Configuration\Form;

use Configuration;
use Exception;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Model\Webhook\Subscription;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\PrestaShop\Service\WebhookService;
use MyParcelNL\Pdk\Facade\DefaultLogger;

class ApiForm extends AbstractForm
{
    private const BUTTON_CLEAR_CACHE = 'clearCache';
    private const BUTTON_RESET_HOOK  = 'resetHook';
    private const BUTTON_DELETE_HOOK = 'deleteHook';

    protected function getNamespace(): string
    {
        return 'apiform';
    }

    /**
     * @return array[]
     */
    public function getButtons(): array
    {
        $buttons = [
            'reset'       => [
                'title' => $this->module->l('Create webhook', 'apiform'),
                'name'  => self::BUTTON_RESET_HOOK,
                'type'  => 'submit',
                'icon'  => 'process-icon-reset',
            ],
            'clear-cache' => [
                'title' => $this->module->l('Clear cache', 'apiform'),
                'name'  => self::BUTTON_CLEAR_CACHE,
                'type'  => 'submit',
            ],
        ];

        if (Configuration::get(Constant::WEBHOOK_ID_CONFIGURATION_NAME)) {
            $buttons['reset']['title'] = $this->module->l('Refresh Webhook', 'apiform');
            $buttons['delete']         = [
                'title' => $this->module->l('Delete Webhook', 'apiform'),
                'name'  => self::BUTTON_DELETE_HOOK,
                'type'  => 'submit',
                'class' => 'btn btn-default pull-left',
                'icon'  => 'process-icon-delete',
            ];
        }

        return $buttons;
    }

    /**
     * @return array[]
     */
    protected function getFields(): array
    {
        return [
            Constant::API_KEY_CONFIGURATION_NAME     => [
                'type'     => 'text',
                'label'    => $this->module->l('Your API key', 'apiform'),
                'name'     => Constant::API_KEY_CONFIGURATION_NAME,
                'required' => false,
            ],
            Constant::API_LOGGING_CONFIGURATION_NAME => [
                'type'     => 'switch',
                'label'    => $this->module->l('Api logging', 'apiform'),
                'name'     => Constant::API_LOGGING_CONFIGURATION_NAME,
                'required' => false,
                'is_bool'  => true,
                'values'   => [
                    [
                        'id'    => 'active_on',
                        'value' => 1,
                        'label' => $this->module->l('Enabled', 'apiform'),
                    ],
                    [
                        'id'    => 'active_off',
                        'value' => 0,
                        'label' => $this->module->l('Disabled', 'apiform'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getLegend(): string
    {
        return $this->module->l('API Settings', 'apiform');
    }

    /**
     * @return string
     */
    protected function update(): string
    {
        $parent        = parent::update();
        $apiKeyChanged = Tools::getValue(Constant::API_KEY_CONFIGURATION_NAME)
            !== Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);

        try {
            if (Tools::isSubmit(self::BUTTON_CLEAR_CACHE)) {
                $this->clearCache();
            }

            if ($apiKeyChanged || Tools::isSubmit(self::BUTTON_RESET_HOOK)) {
                $this->refreshWebhook();
            }

            if (Tools::isSubmit(self::BUTTON_DELETE_HOOK)) {
                $this->deleteWebhook();
            }
        } catch (Exception $exception) {
            DefaultLogger::debug($exception->getMessage(), compact('exception'));
            return $this->module->displayError($exception->getMessage());
        }

        return $parent;
    }

    /**
     * Delete existing webhook subscription.
     *
     * @throws \Exception
     */
    private function deleteWebhook(): void
    {
        $subscriptionId = Configuration::get(Constant::WEBHOOK_ID_CONFIGURATION_NAME);
        $service        = $this->initializeWebhookService();
        $response       = $service->deleteSubscription($subscriptionId);

        if ($response) {
            Configuration::updateValue(Constant::WEBHOOK_ID_CONFIGURATION_NAME, null);
            Configuration::updateValue(Constant::WEBHOOK_HASH_CONFIGURATION_NAME, null);
            DefaultLogger::debug('Webhook subscription deleted', compact('subscriptionId'));
        }
    }

    /**
     * @return \MyParcelNL\PrestaShop\Service\WebhookService
     */
    private function initializeWebhookService(): WebhookService
    {
        return new WebhookService(Tools::getValue(Constant::API_KEY_CONFIGURATION_NAME));
    }

    /**
     * Creates a new webhook subscription. Adds in a randomly generated hash to secure the webhook.
     *
     * @return void
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     * @throws \Exception
     */
    private function refreshWebhook(): void
    {
        $hash         = md5(uniqid((string) mt_rand(), true));
        $siteUrl      = Tools::getShopDomainSsl(true, true);
        $webhookUrl   = $siteUrl . "/index.php?fc=module&module={$this->module->name}&controller=hook&hash=$hash";
        $subscription = new Subscription(Subscription::SHIPMENT_STATUS_CHANGE_HOOK_NAME, $webhookUrl);

        $service  = $this->initializeWebhookService();
        $response = $service->addSubscription($subscription);

        $subscriptionId = $response['data']['ids'][0]['id'] ?? null;

        if ($subscriptionId) {
            Configuration::updateValue(Constant::WEBHOOK_ID_CONFIGURATION_NAME, $subscriptionId);
            Configuration::updateValue(Constant::WEBHOOK_HASH_CONFIGURATION_NAME, $hash);
            DefaultLogger::debug('New webhook subscription created', compact('subscriptionId', 'webhookUrl'));
        }
    }

    /**
     * @return void
     */
    private function clearCache(): void
    {
        Tools::clearAllCache();
        DefaultLogger::info('Cache cleared');
    }
}

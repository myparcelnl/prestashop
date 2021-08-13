<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Configuration;

use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Model\Webhook\Subscription;
use Gett\MyparcelBE\Service\WebhookService;
use Tools;

class ApiForm extends AbstractForm
{
    /**
     * @return array[]
     */
    public function getButtons(): array
    {
        $buttons = [
            'reset' => [
                'title' => $this->module->l('Create webhook', 'apiform'),
                'name'  => 'resetHook',
                'type'  => 'submit',
                'class' => 'btn btn-default pull-left',
                'icon'  => 'process-icon-reset',
            ],
        ];

        if (Configuration::get(Constant::WEBHOOK_ID_CONFIGURATION_NAME)) {
            $buttons['reset']['title'] = $this->module->l('Refresh Webhook', 'apiform');
            $buttons['delete']         = [
                'title' => $this->module->l('Delete Webhook', 'apiform'),
                'name'  => 'deleteHook',
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
        $apiKeyChanged = Tools::getValue(Constant::API_KEY_CONFIGURATION_NAME) !== Configuration::get(
                Constant::API_KEY_CONFIGURATION_NAME
            );

        try {
            if ($apiKeyChanged || Tools::isSubmit('resetHook')) {
                $this->refreshWebhook();
            }

            if (Tools::isSubmit('deleteHook')) {
                $this->deleteWebhook();
            }
        } catch (Exception $e) {
            return $this->module->displayError($e->getMessage());
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
            Logger::addLog("Webhook subscription ($subscriptionId) deleted.");
        }
    }

    /**
     * @return \Gett\MyparcelBE\Service\WebhookService
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
            Logger::addLog("New webhook subscription ($subscriptionId) created. URL: $webhookUrl");
        }
    }
}

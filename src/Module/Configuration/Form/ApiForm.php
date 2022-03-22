<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Module\Configuration\Form;

use Configuration;
use Exception;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\FileLogger;
use Gett\MyparcelBE\Model\Core\AccountSettings;
use Gett\MyparcelBE\Model\Webhook\Subscription;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\WebhookService;

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
        } catch (Exception $e) {
            ApiLogger::addLog($e);
            return $this->module->displayError($e->getMessage());
        }

        return $parent;
    }


    /**
     * @return void
     */
    private function clearCache(): void
    {
        Tools::clearAllCache();
        FileLogger::addLog('Cache cleared', FileLogger::INFO);
    }
}

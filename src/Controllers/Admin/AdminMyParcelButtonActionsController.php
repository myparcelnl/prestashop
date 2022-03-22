<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Logger\FileLogger;
use Gett\MyparcelBE\Model\Webhook\Subscription;
use Gett\MyparcelBE\Module\Hooks\ModuleSettingsRenderService;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\CarrierConfigurationProvider;
use Gett\MyparcelBE\Service\ModuleSettingsService;
use Gett\MyparcelBE\Service\WebhookService;
use Module;
use MyParcelBE;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use PrestaShop\PrestaShop\Adapter\Entity\Carrier;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelButtonActionsController extends AbstractAdminController
{
    /**
     * @var \Gett\MyparcelBE\Service\ModuleSettingsService
     */
    private $service;

    private $module;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ModuleSettingsService();
        $this->module  = Module::getInstanceByName(MyParcelBE::MODULE_NAME);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function clearCache()
    {
        Tools::clearAllCache();
        FileLogger::addLog('Cache cleared', FileLogger::INFO);

        return $this->sendResponse(['messages' => [['message' => $this->module->l('Cache cleared')]]]);
    }

    public function resetHook()
    {
        $this->refreshWebhook();
    }

    public function deleteHook()
    {
        $this->deleteWebhook();
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
            ApiLogger::addLog("Webhook subscription ($subscriptionId) deleted.", ApiLogger::INFO);
        }
    }

    /**
     * @return \Gett\MyparcelBE\Service\WebhookService
     */
    private function initializeWebhookService(): WebhookService
    {
        $a = Tools::getValue(Constant::API_KEY_CONFIGURATION_NAME);
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
            ApiLogger::addLog("New webhook subscription ($subscriptionId) created. URL: $webhookUrl", ApiLogger::INFO);
        }
    }

    public function addCarrier(): void
    {
        $this->sendResponse(['success'=> (new ModuleSettingsRenderService())->getCarriersLayout()]);
        return;

        //Tools::getAllValues();

        $carrier = [];

        $this->sendResponse(['carriers' => [$carrier]]);

        $carrierType = Tools::getValue(Constant::ADD_CARRIER_TYPE);
        $carrierName = Tools::getValue(Constant::ADD_CARRIER_NAME);

        $a = null;
//        $carrierType = Tools::getValue('carrierType');
//        $carrierName = Tools::getValue('carrierName');
//
//        $image = 'postnl.jpg';
//
//        if ($this->module->isBE()) {
//            $image = 'dpd.jpg';
//            if ($carrierType == CarrierBpost::NAME) {
//                $image = 'bpost.jpg';
//            }
//        }
//
        if (Tools::getValue('psCarriers')) {
            $carrier                       = new Carrier(Tools::getValue('psCarriers'));
            $carrier->external_module_name = 'myparcelbe';
            $carrier->is_module            = true;
            $carrier->need_range           = 1;
            $carrier->shipping_external    = true;
            $carrier->update();
        } else {
            $carrier = $this->addCarrier(['name' => $carrierName, 'image' => $image]);

            $this->addZones($carrier);
            $this->addGroups($carrier);
            $this->addRanges($carrier);
        }
//
//        $psCarriersConfig               = (array) json_decode(Configuration::get('MYPARCEL_PSCARRIERS'));
//        $psCarriersConfig[$carrier->id] = $carrierType;
//        Configuration::updateValue('MYPARCEL_PSCARRIERS', json_encode($psCarriersConfig));
//
//        $configurationPsCarriers = CarrierConfigurationProvider::get($carrier->id, 'carrierType');
//        if (is_null($configurationPsCarriers)) {
//            $this->updateConfigurationFields($carrier->id, true);
//        } else {
//            $this->updateConfigurationFields($carrier->id);
//        }
    }
}

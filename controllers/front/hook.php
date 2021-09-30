<?php

declare(strict_types=1);

use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Model\Webhook\WebhookException;
use Gett\MyparcelBE\Model\Webhook\WebhookPayloadFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

if (! defined('_PS_VERSION_')) {
    return;
}

class MyParcelBEHookModuleFrontController extends FrontController
{
    /**
     * Initialize content and block unauthorized calls.
     *
     * @since 2.0.0
     */
    public function initContent()
    {
        if (! Module::isEnabled(MyParcelBE::MODULE_NAME)) {
            $this->sendResponse(400, 'Module is not enabled');
        }

        $this->processWebhook();

        Response::create()->setStatusCode(204)->send();
        die(0);
    }

    /**
     * Disable the maintenance page
     */
    protected function displayMaintenancePage(): void
    {
    }

    /**
     * Handle the webhook.
     */
    protected function processWebhook(): void
    {
        if (! $this->validateHash()) {
            $this->sendResponse(400, 'Invalid hash');
        }

        $content  = file_get_contents('php://input');
        $data     = json_decode($content, true);
        $hookData = $data['data']['hooks'] ?? null;

        if (! is_array($hookData)) {
            ApiLogger::addLog('Invalid data format', ApiLogger::WARNING);
            $this->sendResponse(400, 'Invalid data format');
        }

        ApiLogger::addLog('Incoming webhook: ' . json_encode($hookData));

        foreach ($hookData as $webhook) {
            try {
                $webhook = WebhookPayloadFactory::create($webhook);
            } catch (WebhookException $e) {
                ApiLogger::addLog($e->getMessage(), ApiLogger::WARNING);
                continue;
            }

            $webhook->onReceive();
        }
    }

    /**
     * @param  int    $statusCode
     * @param  string $message
     */
    protected function sendResponse(int $statusCode, string $message): void
    {
        JsonResponse::create([
            'data' => [
                'message' => $message,
            ],
        ])
            ->setStatusCode($statusCode)
            ->send();
        die(1);
    }

    /**
     * @return bool
     */
    private function validateHash(): bool
    {
        $hash = $_REQUEST['hash'] ?? null;

        if (! $hash || $hash !== Configuration::get(Constant::WEBHOOK_HASH_CONFIGURATION_NAME)) {
            ApiLogger::addLog('Invalid webhook hash used: ' . $hash);
            return false;
        }

        return true;
    }
}

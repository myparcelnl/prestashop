<?php /** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Model\Webhook\WebhookException;
use MyParcelNL\PrestaShop\Model\Webhook\WebhookPayloadFactory;
use MyParcelNL\Pdk\Facade\DefaultLogger;
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
        if (! Module::isEnabled(MyParcelNL::MODULE_NAME)) {
            $this->sendResponse(400, 'Module is not enabled');
        }

        $this->processWebhook();

        Response::create()
            ->setStatusCode(204)
            ->send();

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
            DefaultLogger::warning('Incoming webhook data is invalid', compact('data'));
            $this->sendResponse(400, 'Invalid data format');
        }

        DefaultLogger::debug('Incoming webhook', compact('hookData'));

        foreach ($hookData as $webhook) {
            try {
                $webhook = WebhookPayloadFactory::create($webhook);
            } catch (WebhookException $exception) {
                DefaultLogger::warning($exception->getMessage(), compact('exception'));
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
            DefaultLogger::notice('Invalid webhook hash passed', compact('hash'));
            return false;
        }

        return true;
    }
}

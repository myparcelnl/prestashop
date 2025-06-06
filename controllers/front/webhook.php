<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Webhook\PdkWebhookManager;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

if (! defined('_PS_VERSION_')) {
    return;
}

class MyParcelNLWebhookModuleFrontController extends FrontController
{
    public function initContent(): void
    {
        if (! MyParcelModule::isEnabled()) {
            $this->sendResponse(400, 'Module is not enabled');
        }

        /** @var \MyParcelNL\Pdk\App\Webhook\PdkWebhookManager $webhooks */
        $webhooks = Pdk::get(PdkWebhookManager::class);
        $webhooks->call(Request::createFromGlobals());

        exit;// bugfix for smarty missing $template variable if execution is not stopped in front controller
    }

    /**
     * @param  int    $statusCode
     * @param  string $message
     */
    protected function sendResponse(int $statusCode, string $message): void
    {
        (new JsonResponse([
            'data' => [
                'message' => $message,
            ],
        ]))
            ->setStatusCode($statusCode)
            ->send();
        die(1);
    }
}

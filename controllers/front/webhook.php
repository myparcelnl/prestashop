<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Webhook\PdkWebhook;
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
        if (! Module::isEnabled(MyParcelNL::MODULE_NAME)) {
            $this->sendResponse(400, 'Module is not enabled');
        }

        /** @var \MyParcelNL\Pdk\App\Webhook\PdkWebhook $webhooks */
        $webhooks = Pdk::get(PdkWebhook::class);
        $webhooks->call(Request::createFromGlobals());
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

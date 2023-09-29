<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelBE\PrestaShop\Facade\MyParcelModule;
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

        /** @var PdkEndpoint $endpoint */
        $endpoint = Pdk::get(PdkEndpoint::class);
        $endpoint->call(Request::createFromGlobals(), PdkEndpoint::CONTEXT_BACKEND);
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

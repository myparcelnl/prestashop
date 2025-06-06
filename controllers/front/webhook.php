<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

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
        file_put_contents('/var/www/prestashop-dev/var/logs/myparcelnl/notice.log', var_export(Request::createFromGlobals(), true) . "\n^ -------------- REQUEST ------------\n", FILE_APPEND);
        file_put_contents('/var/www/prestashop-dev/var/logs/myparcelnl/notice.log', var_export(json_decode(file_get_contents('php://input'), false), true) . "\n^ -------------- php://input ------------\n", FILE_APPEND);

        /** @var PdkEndpoint $endpoint */
        $endpoint = Pdk::get(PdkEndpoint::class);
        $endpoint->call(Request::createFromGlobals(), PdkEndpoint::CONTEXT_BACKEND);

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

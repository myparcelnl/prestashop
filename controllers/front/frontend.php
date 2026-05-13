<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use Symfony\Component\HttpFoundation\Request;

if (! defined('_PS_VERSION_')) {
    return;
}

class MyParcelNLFrontendModuleFrontController extends ModuleFrontController
{
    public function initContent(): void
    {
        if (! MyParcelModule::isEnabled()) {
            $this->sendResponse(400, 'Module is not enabled');
        }

        try {
            $request  = Request::createFromGlobals();
            $endpoint = Pdk::get(PdkEndpoint::class);
            $response = $endpoint->call($request, PdkEndpoint::CONTEXT_FRONTEND);
        } catch (\Throwable $e) {
            Logger::error($e->getMessage(), ['action' => $_REQUEST['action'] ?? 'unknown']);
            $this->sendResponse(400, 'An error occurred');
        }

        EntityManager::flush();
        $response->send();
        die(1);
    }

    private function sendResponse(int $statusCode, string $message): void
    {
        (new \Symfony\Component\HttpFoundation\Response($message))
            ->setStatusCode($statusCode)
            ->send();
        die(1);
    }
}

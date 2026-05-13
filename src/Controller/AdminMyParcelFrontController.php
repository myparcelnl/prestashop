<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AdminMyParcelFrontController extends AbstractAdminController
{
    public function index(): Response
    {
        try {
            $request  = Request::createFromGlobals();
            $endpoint = Pdk::get(PdkEndpoint::class);
            $response = $endpoint->call($request, PdkEndpoint::CONTEXT_FRONTEND);
        } catch (Throwable $e) {
            Logger::error($e->getMessage(), ['action' => $_REQUEST['action'] ?? 'unknown']);

            return new Response('An error occurred', 400);
        }

        EntityManager::flush();

        return $response;
    }
}

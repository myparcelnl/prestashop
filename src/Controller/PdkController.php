<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PdkController extends AbstractAdminController
{
    private const PRESTASHOP_TOKEN_PARAMETER = '_token';

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): Response
    {
        try {
            $request = $this->createNormalizedRequest();

            /** @var \MyParcelNL\Pdk\App\Api\PdkEndpoint $endpoint */
            $endpoint = Pdk::get(PdkEndpoint::class);

            $response = $endpoint->call($request, PdkEndpoint::CONTEXT_BACKEND);
        } catch (Throwable $e) {
            Logger::error($e->getMessage(), ['values' => $_REQUEST]);

            return new Response($e->getMessage(), 400);
        }

        EntityManager::flush();

        return $response;
    }

    /**
     * Remove the _token parameter that's included with all requests before passing it to the PDK endpoints.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function createNormalizedRequest(): Request
    {
        $request = Request::createFromGlobals();
        $request->query->remove(self::PRESTASHOP_TOKEN_PARAMETER);

        return $request;
    }
}

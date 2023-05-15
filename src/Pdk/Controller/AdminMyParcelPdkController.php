<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Controller;

use MyParcelNL;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @property \MyParcelNL $module
 * @noinspection PhpUnused
 */
class AdminMyParcelPdkController extends FrameworkBundleAdminController
{
    private const PRESTASHOP_TOKEN_PARAMETER = '_token';

    public function __construct()
    {
        parent::__construct();

        // Trigger PDK setup
        MyParcelNL::getModule();
    }

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

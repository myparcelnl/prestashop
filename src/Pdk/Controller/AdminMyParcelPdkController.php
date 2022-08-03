<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Controller;

use MyParcelNL\Pdk\Base\PdkEndpoint;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
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
        \MyParcelNL::getModule();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): Response
    {
        try {
            $request = $this->createNormalizedRequest();

            /** @var \MyParcelNL\Pdk\Base\PdkEndpoint $endpoint */
            $endpoint = Pdk::get(PdkEndpoint::class);

            $response = $endpoint->call($request);
        } catch (Throwable $e) {
            DefaultLogger::error($e->getMessage(), ['values' => Tools::getAllValues()]);
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

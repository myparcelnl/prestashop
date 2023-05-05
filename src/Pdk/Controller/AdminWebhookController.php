<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Controller;

use MyParcelNL;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Api\PdkWebhook;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @property \MyParcelNL $module
 * @noinspection PhpUnused
 */
class AdminWebhookController extends FrameworkBundleAdminController
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
        die(' does it work? '); //todo
        try {
            $request = $this->createNormalizedRequest();

            DefaultLogger::info('Webhook received', ['request' => $request->query]);

            /** @var \MyParcelNL\Pdk\Plugin\Api\PdkWebhook $webhooks */
            $webhooks = Pdk::get(PdkWebhook::class);

            $response = $webhooks->call($request);
        } catch (Throwable $e) {
            DefaultLogger::error($e->getMessage(), ['values' => $_REQUEST]);
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

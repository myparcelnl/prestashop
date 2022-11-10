<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Controller;

use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\Pdk\Base\PdkEndpoint;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @property \MyParcelNL $module
 * @noinspection PhpUnused
 */
class AdminMyParcelPdkController extends FrameworkBundleAdminController
{
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
            $action = Tools::getValue('action') ?: null;

            /** @var \MyParcelNL\Pdk\Base\PdkEndpoint $endpoint */
            $endpoint = Pdk::get(PdkEndpoint::class);

            $response = $endpoint->call($action);
        } catch (Throwable $e) {
            DefaultLogger::error($e->getMessage(), ['values' => Tools::getAllValues()]);
            return new Response($e->getMessage(), 400);
        }

        return $response;
    }
}

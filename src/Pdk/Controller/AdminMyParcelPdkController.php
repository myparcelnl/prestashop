<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Pdk\Controller;

use Gett\MyparcelBE\Module\Tools\Tools;
use MyParcelNL\Pdk\Base\PdkEndpoint;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use MyParcelNL\Pdk\Facade\Pdk;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @property \MyParcelBE $module
 * @noinspection PhpUnused
 */
class AdminMyParcelPdkController extends FrameworkBundleAdminController
{
    public function __construct()
    {
        parent::__construct();

        // Trigger PDK setup
        \MyParcelBE::getModule();
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

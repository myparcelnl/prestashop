<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Gett\MyparcelBE\Module\Hooks\RenderService;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see https://prestashop.dev.myparcel.nl/admin1/index.php/modules/myparcelbe/loading
 */
class LoadingController extends FrameworkBundleAdminController
{
    /**
     * @throws \Exception
     */
    public function index(): Response
    {
        return new Response((new RenderService())->render('admin/loading.twig'));
    }
}

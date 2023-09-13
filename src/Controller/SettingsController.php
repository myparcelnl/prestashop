<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL\Pdk\Facade\Frontend;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \MyParcelNL $module
 */
class SettingsController extends AbstractAdminController
{
    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('@Modules/myparcelnl/views/templates/admin/page.twig', [
            'content' => Frontend::renderPluginSettings(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Exception;
use Gett\MyparcelBE\Concern\SendsResponse;
use Gett\MyparcelBE\Module\Tools\Tools;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractAdminController extends FrameworkBundleAdminController
{
    use SendsResponse;

    /**
     * @var array|string
     */
    private $response;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): Response
    {
        $response = null;
        $action   = Tools::getValue('action', null);

        if (! $action) {
            $this->addError('Parameter "action" is missing or invalid.');
        }

        if (is_string($action) && ! method_exists($this, $action)) {
            $this->addError("Action \"$action\" does not exist.");
        }

        if (! $this->hasErrors()) {
            try {
                $response = $this->{$action}();
            } catch (Exception $e) {
                $this->addError($e);
            }
        }

        return $response ?? $this->sendResponse();
    }
}

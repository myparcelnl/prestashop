<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Controllers\Admin;

use Gett\MyparcelBE\Logger\FileLogger;
use Gett\MyparcelBE\Module\Tools\Tools;
use Gett\MyparcelBE\Service\ModuleSettingsService;
use Module;
use MyParcelBE;
use Symfony\Component\HttpFoundation\Response;

/**
 * @property \MyParcelBE $module
 */
class AdminMyParcelModuleSettingsController extends AbstractAdminController
{
    /**
     * @var \Gett\MyparcelBE\Service\ModuleSettingsService
     */
    private $service;

    private $module;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ModuleSettingsService();
        $this->module  = Module::getInstanceByName(MyParcelBE::MODULE_NAME);
    }

    public function save(): Response
    {
        try {
            $this->service->upsertModuleSettings(
                Tools::getAllValues()
            );
        } catch(\Exception $e) {
            $this->addError($e);
        }

        return $this->sendResponse(['messages' => [['message' => $this->module->l('Updated')]]]);
    }
}

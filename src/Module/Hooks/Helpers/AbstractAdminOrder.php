<?php

namespace Gett\MyparcelBE\Module\Hooks\Helpers;

use Configuration;
use Gett\MyparcelBE\Constant;

abstract class AbstractAdminOrder
{
    /**
     * @return array
     * @throws \PrestaShopException
     */
    public function getLabelDefaultConfiguration(): array
    {
        return Configuration::getMultiple([
            Constant::LABEL_SIZE_CONFIGURATION_NAME,
            Constant::LABEL_POSITION_CONFIGURATION_NAME,
            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
        ]);
    }
}

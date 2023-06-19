<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use Tools;

trait HasPdkProductHooks
{
    /**
     * @param  array $params
     *
     * @return void
     */
    public function hookActionProductUpdate(array $params): void
    {
        $productId               = (int) $params['id_product'];
        $postValues              = Tools::getAllValues();
        $myparcelProductSettings = array_filter($postValues, static function ($key) {
            return str_starts_with($key, 'myparcelnl');
        }, ARRAY_FILTER_USE_KEY);

        $productSettingsBody = [];

        foreach ($myparcelProductSettings as $key => $value) {
            $explodedKey                  = explode('-', $key);
            $newKey                       = end($explodedKey);
            $productSettingsBody[$newKey] = $value;
        }

        // todo: refactor to use a \Symfony\Component\HttpFoundation\Request, pass body as json with data.product_settings[0]
        Actions::execute(PdkBackendActions::UPDATE_PRODUCT_SETTINGS, [
            'productId'       => $productId,
            'productSettings' => $productSettingsBody,
        ]);
    }
}

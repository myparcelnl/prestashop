<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Module\Hooks;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Facade\Actions;
use Symfony\Component\HttpFoundation\Request;
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

        $request = new Request(
            ['productId' => $productId],
            ['action' => PdkBackendActions::UPDATE_PRODUCT_SETTINGS],
            [],
            [],
            [],
            [],
            json_encode([
                'data' => [
                    'product_settings' => $productSettingsBody,
                ],
            ])
        );

        Actions::execute($request);
    }

    /**
     * Renders the product settings.
     *
     * @param  array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $repository */
        $repository          = Pdk::get(PdkProductRepositoryInterface::class);
        $product             = $repository->getProduct($params['id_product']);
        $productSettingsView = Frontend::renderProductSettings($product);

        $this->context->smarty->assign(
            [
                'productSettingsView' => $productSettingsView,
            ]
        );

        // todo move to twig
        return $this->display($this->name, 'views/templates/hook/product_settings.tpl');
    }
}

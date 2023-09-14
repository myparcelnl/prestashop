<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\Sdk\src\Support\Str;
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
        $this->saveProductSettings((int) $params['id_product']);
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
        return $this->renderProductSettings((int) $params['id_product']);
    }

    /**
     * @param  int $idProduct
     *
     * @return string
     */
    private function renderProductSettings(int $idProduct): string
    {
        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $repository */
        $repository = Pdk::get(PdkProductRepositoryInterface::class);
        $product    = $repository->getProduct($idProduct);

        return Frontend::renderProductSettings($product);
    }

    /**
     * @param  int $idProduct
     *
     * @return void
     */
    private function saveProductSettings(int $idProduct): void
    {
        $postValues = Tools::getAllValues();
        $name       = Pdk::getAppInfo()->name;

        $productSettings = array_filter($postValues, static function ($key) use ($name) {
            return Str::startsWith((string) $key, $name);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($productSettings)) {
            return;
        }

        $requestBody = [];

        foreach ($productSettings as $key => $value) {
            $trimmedKey               = Arr::last(explode('-', $key));
            $requestBody[$trimmedKey] = $value;
        }

        $request = new Request(
            [
                'action'    => PdkBackendActions::UPDATE_PRODUCT_SETTINGS,
                'productId' => $idProduct,
            ],
            [],
            [],
            [],
            [],
            [],
            json_encode(['data' => ['product_settings' => $requestBody]])
        );

        Actions::execute($request);

        EntityManager::flush();
    }
}

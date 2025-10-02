<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Hooks;

use MyParcelNL;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Frontend;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\EntityManager;
use MyParcelNL\Sdk\src\Support\Str;
use Symfony\Component\HttpFoundation\Request;
use Throwable;
use Tools;

trait HasPdkProductHooks
{
    /**
     * Saves the MyParcel product settings when a product is updated.
     *
     * @param  array $params
     *
     * @return void
     */
    public function hookActionProductUpdate(array $params): void
    {
        try {
            $this->saveProductSettings((int) $params['id_product']);
        } catch (Throwable $e) {
            Logger::error(sprintf('Failed to save product settings for product %d', (int) $params['id_product']), [
                'error' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
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
     * @param  int   $productId
     * @param  array $productSettings
     *
     * @return \Symfony\Component\HttpFoundation\Request
     * @throws \JsonException
     */
    private function createRequest(int $productId, array $productSettings): Request
    {
        $parameters = [
            'action'    => PdkBackendActions::UPDATE_PRODUCT_SETTINGS,
            'productId' => $productId,
        ];

        $requestBody = [];

        foreach ($productSettings as $key => $value) {
            $trimmedKey               = Arr::last(explode('-', $key));
            $requestBody[$trimmedKey] = $value;
        }

        $content = json_encode(['data' => ['product_settings' => $requestBody]], JSON_THROW_ON_ERROR);

        return new Request($parameters, [], [], [], [], [], $content);
    }

    /**
     * The product update hook is called multiple (about 8) times by PrestaShop. This method checks if the product
     * settings are already saved by comparing a checksum of the settings with the checksum we add to $_POST.
     *
     * @see https://www.prestashop.com/forums/topic/591295-ps17-hookactionproductupdate-gets-multiple-called-and-no-image-uploaded/
     *
     * @param  int   $idProduct
     * @param  array $productSettings
     *
     * @return bool
     * @throws \JsonException
     */
    private function isAlreadySaved(int $idProduct, array $productSettings): bool
    {
        $appName          = MyParcelNL::MODULE_NAME;
        $checksumKey      = "_$appName-product-save-checksum-$idProduct";
        $existingChecksum = $_POST[$checksumKey] ?? null;
        $checksum         = md5(json_encode($productSettings, JSON_THROW_ON_ERROR));

        if ($existingChecksum === $checksum) {
            return true;
        }

        $_POST[$checksumKey] = $checksum;

        return false;
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
     * @param  int $productId
     *
     * @return void
     * @throws \JsonException
     */
    private function saveProductSettings(int $productId): void
    {
        $name            = MyParcelNL::MODULE_NAME;
        $productSettings = array_filter(Tools::getAllValues(), static function ($key) use ($name) {
            return Str::startsWith((string) $key, $name);
        }, ARRAY_FILTER_USE_KEY);

        if (empty($productSettings) || $this->isAlreadySaved($productId, $productSettings)) {
            return;
        }

        $request = $this->createRequest($productId, $productSettings);

        Logger::debug(sprintf('Saving product settings for product %d', $productId), ['settings' => $productSettings]);

        Actions::execute($request);

        EntityManager::flush();
    }
}

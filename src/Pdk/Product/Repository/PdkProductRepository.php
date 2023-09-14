<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Product\Repository;

use Context;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkPdkProductRepository;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use Product;

class PdkProductRepository extends AbstractPdkPdkProductRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $productSettingsRepository;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $psProductRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService
     */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface             $storage
     * @param  \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService       $weightService
     * @param  \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository $productSettingsRepository
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface        $currencyService
     */
    public function __construct(
        StorageInterface            $storage,
        PsWeightService             $weightService,
        PsProductSettingsRepository $productSettingsRepository,
        CurrencyServiceInterface    $currencyService
    ) {
        parent::__construct($storage);
        $this->weightService             = $weightService;
        $this->productSettingsRepository = $productSettingsRepository;
        $this->currencyService           = $currencyService;

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager             = Pdk::get('ps.entityManager');
        $this->psProductRepository = $entityManager->getRepository(MyparcelnlProductSettings::class);
    }

    /**
     * @param  int|string $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     */
    public function getProduct($identifier): PdkProduct
    {
        return $this->retrieve('product_' . $identifier, function () use ($identifier) {
            $psProduct = new Product($identifier);
            $translate = static function (array $strings) {
                return $strings[Context::getContext()->language->id] ?? $strings[1] ?? reset($strings);
            };

            return new PdkProduct([
                'externalIdentifier' => $psProduct->id,
                'name'               => $translate($psProduct->name),
                'weight'             => $this->weightService->convertToGrams($psProduct->weight),
                'settings'           => $this->getProductSettings($identifier),
                'isDeliverable'      => $this->isDeliverable($psProduct),
                'price'              => [
                    'currency' => Context::getContext()->currency->iso_code,
                    'amount'   => $this->currencyService->convertToCents($psProduct->price),
                ],
            ]);
        });
    }

    /**
     * @param  int|string $identifier
     *
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    public function getProductSettings($identifier): ProductSettings
    {
        return $this->retrieve("product_settings_$identifier", function () use ($identifier) {
            /** @var \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings $psProductSettings */
            $psProductSettings = $this->psProductRepository->findOneBy(['productId' => $identifier]);
            $data              = $psProductSettings ? $psProductSettings->toArray() : [];
            $parameters        = json_decode($data['data'] ?? '', true);

            return new ProductSettings($parameters);
        });
    }

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection
     */
    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        $products = array_map(function ($identifier) {
            return $this->getProduct($identifier);
        }, $identifiers);

        return new PdkProductCollection($products);
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(PdkProduct $product): void
    {
        $this->productSettingsRepository->updateOrCreate(
            [
                'productId' => (int) $product->externalIdentifier,
            ],
            [
                'data' => json_encode($product->settings->toArray()),
            ]
        );
    }

    /**
     * @param  \Product $psProduct
     *
     * @return bool
     */
    private function isDeliverable(Product $psProduct): bool
    {
        return $psProduct->available_for_order
            && $psProduct->active
            && ! $psProduct->is_virtual;
    }
}

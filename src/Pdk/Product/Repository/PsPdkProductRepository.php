<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Product\Repository;

use Context;
use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkPdkProductRepository;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use MyParcelNL\PrestaShop\Service\PsProductService;
use MyParcelNL\Sdk\Support\Arr;
use Product as PsProduct;

class PsPdkProductRepository extends AbstractPdkPdkProductRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var \MyParcelNL\PrestaShop\Service\PsProductService
     */
    private PsProductService $psProductService;

    /**
     * @var \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository
     */
    private $psProductSettingsRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService
     */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface             $storage
     * @param  \MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService       $weightService
     * @param  \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository $productSettingsRepository
     * @param  \MyParcelNL\PrestaShop\Service\PsProductService               $psProductService
     * @param  \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface        $currencyService
     */
    public function __construct(
        StorageInterface            $storage,
        PsWeightService             $weightService,
        PsProductSettingsRepository $productSettingsRepository,
        PsProductService            $psProductService,
        CurrencyServiceInterface    $currencyService
    ) {
        parent::__construct($storage);
        $this->weightService               = $weightService;
        $this->psProductSettingsRepository = $productSettingsRepository;
        $this->psProductService            = $psProductService;
        $this->currencyService             = $currencyService;
    }

    /**
     * @param  int|string $identifier
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     */
    public function getProduct($identifier): PdkProduct
    {
        if (! $this->psProductService->exists($identifier)) {
            Logger::error("Product with id $identifier not found");
            throw new InvalidArgumentException('Product not found');
        }

        return $this->retrieve((string) $identifier, function () use ($identifier) {
            /** @var PsProduct $psProduct */
            $psProduct = $this->psProductService->get($identifier);

            $translate = static function (array $strings) {
                return $strings[Context::getContext()->language->id] ?? $strings[1] ?? Arr::last($strings);
            };

            return new PdkProduct([
                'externalIdentifier' => $psProduct->id,
                'name'               => $translate($psProduct->name ?? []),
                'weight'             => $this->weightService->convertToGrams((float) ($psProduct->weight ?? 0)),
                'settings'           => $this->getProductSettings($identifier),
                'isDeliverable'      => $this->isDeliverable($psProduct),
                'price'              => [
                    'currency' => Context::getContext()->currency->iso_code,
                    'amount'   => $this->currencyService->convertToCents($psProduct->price ?? 0),
                ],
            ]);
        });
    }

    /**
     * Get the product of a cart line or an order line, with the weight of one item on that line.
     *
     * A combination and a customization can change the weight of a product. PrestaShop adds those
     * weight impacts to the product weight and stores the result on the cart line and on the order
     * line. A line without a combination or a customization weighs the same as the base product.
     * The cached base product keeps its own weight.
     *
     * @param  int|string            $identifier      The product id
     * @param  int                   $combinationId   The id_product_attribute of the line, 0 for none
     * @param  int                   $customizationId The id_customization of the line, 0 for none
     * @param  null|int|float|string $lineWeight      The weight of one item in the shop weight unit
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkProduct
     */
    public function getLineProduct($identifier, int $combinationId, int $customizationId, $lineWeight): PdkProduct
    {
        $product = $this->getProduct($identifier);

        if (! $combinationId && ! $customizationId) {
            return $product;
        }

        $lineProduct = clone $product;
        // Without a line weight the weight is unknown: the base product weight is wrong for a combination.
        $lineProduct->weight = is_numeric($lineWeight) && $lineWeight > 0
            ? $this->weightService->convertToGrams((float) $lineWeight)
            : 0;

        return $lineProduct;
    }

    /**
     * @param  int|string $identifier
     *
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    public function getProductSettings($identifier): ProductSettings
    {
        /** @var \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings $psProductSettings */
        $psProductSettings = $this->psProductSettingsRepository->findOneBy(['productId' => $identifier]);

        $array = $psProductSettings ? $psProductSettings->toArray() : [];

        return new ProductSettings(Arr::get($array, 'data.settings', []));
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
        $this->psProductSettingsRepository->updateOrCreate(
            [
                'productId' => (int) $product->externalIdentifier,
            ],
            [
                'data' => json_encode($product->toStorableArray()),
            ]
        );

        $this->save($product->externalIdentifier, $product);
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return 'product_';
    }

    /**
     * @param  \Product $psProduct
     *
     * @return bool
     */
    private function isDeliverable(PsProduct $psProduct): bool
    {
        return $psProduct->available_for_order
            && $psProduct->active
            && ! $psProduct->is_virtual;
    }
}

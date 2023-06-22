<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Product\Repository;

use Context;
use MyParcelNL\Pdk\App\Order\Collection\PdkProductCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkPdkProductRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository;
use MyParcelNL\PrestaShop\Service\PsWeightService;
use Product;

class PdkProductRepository extends AbstractPdkPdkProductRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $psProductRepository;

    /**
     * @var \MyParcelNL\PrestaShop\Service\PsWeightService
     */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface             $storage
     * @param  \MyParcelNL\PrestaShop\Service\PsWeightService                $weightService
     * @param  \MyParcelNL\PrestaShop\Repository\PsProductSettingsRepository $productSettingsRepository
     */
    public function __construct(
        StorageInterface            $storage,
        PsWeightService             $weightService,
        PsProductSettingsRepository $productSettingsRepository
    ) {
        parent::__construct($storage);
        $this->weightService             = $weightService;
        $this->productSettingsRepository = $productSettingsRepository;

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
                'price'              => [
                    'currency' => Context::getContext()->currency->iso_code,
                    'amount'   => $psProduct->price,
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
        return $this->retrieve('product_settings_' . $identifier, function () use ($identifier) {
            /** @var \MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings $psProductSettings */
            $psProductSettings = $this->psProductRepository->findOneBy(['idProduct' => $identifier]);
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

        return (new PdkProductCollection($products));
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @throws \Doctrine\ORM\ORMException
     */
    public function update(PdkProduct $product): void
    {
        $this->productSettingsRepository->updateOrCreate(
            [
                'idProduct' => (int) $product->externalIdentifier,
            ],
            [
                'data' => json_encode($product->settings->toArray()),
            ]
        );
    }
}

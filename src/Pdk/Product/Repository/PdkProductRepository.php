<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Product\Repository;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;
use MyParcelNL\Pdk\Product\Repository\AbstractProductRepository;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Entity\MyparcelnlProductSettings;
use MyParcelNL\PrestaShop\Module\Tools\Tools;
use MyParcelNL\PrestaShop\Service\PsWeightService;
use Product;

class PdkProductRepository extends AbstractProductRepository
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
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     * @param  \MyParcelNL\PrestaShop\Service\PsWeightService    $weightService
     */
    public function __construct(StorageInterface $storage, PsWeightService $weightService)
    {
        parent::__construct($storage);
        $this->weightService = $weightService;

        /** @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager             = Pdk::get('ps.entityManager');
        $this->psProductRepository = $entityManager->getRepository(MyparcelnlProductSettings::class);
    }

    /**
     * @param  int|string $identifier
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkProduct
     */
    public function getProduct($identifier): PdkProduct
    {
        return $this->retrieve('product_' . $identifier, function () use ($identifier) {
            $psProduct = new Product($identifier);

            return new PdkProduct([
                'sku'      => $psProduct->id,
                'name'     => Tools::getTranslatedString($psProduct->name),
                'weight'   => $this->weightService->convertToGrams($psProduct->weight),
                'settings' => $this->getProductSettings($identifier),
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

            return new ProductSettings($data);
        });
    }

    /**
     * @param  array $identifiers
     *
     * @return \MyParcelNL\Pdk\Plugin\Collection\PdkProductCollection
     */
    public function getProducts(array $identifiers = []): PdkProductCollection
    {
        $products = array_map(function ($identifier) {
            return $this->getProduct($identifier);
        }, $identifiers);

        return (new PdkProductCollection($products));
    }

    public function update(PdkProduct $product): void
    {
        // TODO: Implement update() method.
    }
}

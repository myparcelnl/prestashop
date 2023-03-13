<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkCartRepository;
use MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;

class PsCartRepository extends AbstractPdkCartRepository
{
    /**
     * @var \MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\StorageInterface                      $storage
     * @param  \MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface $productRepository
     */
    public function __construct(StorageInterface $storage, ProductRepositoryInterface $productRepository)
    {
        parent::__construct($storage);
        $this->productRepository = $productRepository;
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkCart
     */
    public function get($input): PdkCart
    {
        // TODO: Convert Prestashop cart to PdkCart, see Woocommerce plugin for inspiration
        return new PdkCart();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use MyParcelNL\Pdk\Plugin\Model\PdkCart;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkCartRepository;
use MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class PsCartRepository extends AbstractPdkCartRepository
{
    /**
     * @var \MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface           $storage
     * @param  \MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface $productRepository
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

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Order\Repository;

use Address;
use Cart;
use InvalidArgumentException;
use MyParcelNL\Pdk\Plugin\Contract\TaxServiceInterface;
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
     * @var \MyParcelNL\Pdk\Plugin\Contract\TaxServiceInterface
     */
    private $taxService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface           $storage
     * @param  \MyParcelNL\Pdk\Product\Contract\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        StorageInterface           $storage,
        ProductRepositoryInterface $productRepository,
        TaxServiceInterface        $taxService
    ) {
        parent::__construct($storage);
        $this->productRepository = $productRepository;
        $this->taxService        = $taxService;
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkCart
     * @throws \Exception
     */
    public function get($input): PdkCart
    {
        if (! $input instanceof Cart) {
            throw new InvalidArgumentException('Invalid input for cart repository');
        }

        $address = new Address($input->id_address_delivery);

        return $this->retrieve((string) $input->id, function () use ($input, $address): PdkCart {
            $data = [
                'externalIdentifier'    => $input->id,
                'shipmentPrice'         => '',
                'shipmentPriceAfterVat' => '',
                'shipmentVat'           => '',
                'orderPrice'            => (int) (100 * $input->getOrderTotal()),
                'orderPriceAfterVat'    => '',
                'orderVat'              => '',
                'shippingMethod'        => [
                    'shippingAddress' => [
                        'cc'         => $address->country,
                        'postalCode' => $address->postcode,
                        'fullStreet' => $address->address1,
                    ],
                ],
                'lines'                 => array_map(function ($item) {
                    $product = $this->productRepository->getProduct($item['id_product']);

                    /** @noinspection UnnecessaryCastingInspection <- because it is definitely necessary */
                    return [
                        'quantity'      => (int) $item['cart_quantity'],
                        'price'         => (int) $item['price'],
                        'vat'           => '',
                        'priceAfterVat' => '',
                        'product'       => $product,
                    ];
                }, array_values($input->getProducts())),
            ];

            return new PdkCart($data);
        });
    }
}

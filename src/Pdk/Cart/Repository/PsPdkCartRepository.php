<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Cart\Repository;

use Address;
use Cart;
use InvalidArgumentException;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Cart\Repository\AbstractPdkCartRepository;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class PsPdkCartRepository extends AbstractPdkCartRepository
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository
     */
    public function __construct(
        StorageInterface              $storage,
        PdkProductRepositoryInterface $productRepository
    ) {
        parent::__construct($storage);
        $this->productRepository = $productRepository;
    }

    /**
     * @param  mixed $input
     *
     * @return \MyParcelNL\Pdk\App\Cart\Model\PdkCart
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

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Cart\Repository;

use Address;
use Cart;
use InvalidArgumentException;
use MyParcelNL\Pdk\App\Cart\Model\PdkCart;
use MyParcelNL\Pdk\App\Cart\Repository\AbstractPdkCartRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\PrestaShop\Pdk\Product\Repository\PsPdkProductRepository;
use PrestaShop\PrestaShop\Adapter\Entity\Country;

class PsPdkCartRepository extends AbstractPdkCartRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Pdk\Product\Repository\PsPdkProductRepository
     */
    private $productRepository;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                   $storage
     * @param  \MyParcelNL\PrestaShop\Pdk\Product\Repository\PsPdkProductRepository $productRepository
     */
    public function __construct(
        StorageInterface       $storage,
        PsPdkProductRepository $productRepository
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
                        'cc'         => Country::getIsoById($address->id_country),
                        'postalCode' => $address->postcode,
                        'fullStreet' => $address->address1,
                        // The PDK Address derives isBusiness from a company name and drops the name
                        // itself, so passing it keeps the cart PII-free while flagging B2B recipients.
                        'company'    => $address->company,
                    ],
                ],
                'lines'                 => array_map(function ($item) {
                    $product = $this->productRepository->getLineProduct(
                        $item['id_product'],
                        (int) ($item['id_product_attribute'] ?? 0),
                        (int) ($item['id_customization'] ?? 0),
                        $item['weight'] ?? null
                    );

                    return [
                        'quantity'      => (int) $item['cart_quantity'],
                        'price'         => (int) $item['price'],
                        'vat'           => 0,
                        'priceAfterVat' => 0,
                        'product'       => $product,
                    ];
                }, array_values($input->getProducts())),
            ];

            return new PdkCart($data);
        });
    }
}

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
use MyParcelNL\PrestaShop\Pdk\Base\Service\PsWeightService;
use PrestaShop\PrestaShop\Adapter\Entity\Country;

class PsPdkCartRepository extends AbstractPdkCartRepository
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface
     */
    private $productRepository;

    /** @var PsWeightService */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface                $storage
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface $productRepository
     * @param  PsWeightService                                                  $weightService
     */
    public function __construct(
        StorageInterface              $storage,
        PdkProductRepositoryInterface $productRepository,
        PsWeightService               $weightService
    ) {
        parent::__construct($storage);
        $this->productRepository = $productRepository;
        $this->weightService     = $weightService;
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
                    // getProducts() already includes combination and customization weight in shop units.
                    // Keep the per-line weight off the cached base product and other variants of it.
                    $product = clone $this->productRepository->getProduct($item['id_product']);

                    if (array_key_exists('weight', $item)) {
                        $product->weight = is_numeric($item['weight']) && $item['weight'] > 0
                            ? $this->weightService->convertToGrams((float) $item['weight'])
                            : 0;
                    } elseif (! empty($item['id_product_attribute']) || ! empty($item['id_customization'])) {
                        // The base product cannot establish a known weight for a missing variant weight.
                        $product->weight = 0;
                    }

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

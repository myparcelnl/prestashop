<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use Doctrine\ORM\Mapping as ORM;
use MyParcelNL\PrestaShop\Database\Table;
use MyParcelNL\PrestaShop\Entity\Concern\HasJsonData;
use MyParcelNL\PrestaShop\Entity\Concern\HasTimestamps;
use MyParcelNL\PrestaShop\Entity\Contract\EntityWithTimestampsInterface;

/**
 * @ORM\Table
 * @ORM\Entity
 * @see \MyParcelNL\PrestaShop\Database\CreateOrderDataTableDatabaseMigration
 */
final class MyparcelnlCartDeliveryOptions extends AbstractEntity implements EntityWithTimestampsInterface
{
    use HasJsonData;
    use HasTimestamps;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="cart_id", type="integer")
     */
    private $cartId;

    public static function getTable(): string
    {
        return Table::TABLE_CART_DELIVERY_OPTIONS;
    }

    public function getCartId(): int
    {
        return $this->cartId;
    }

    public function setCartId(int $cartId): MyparcelnlCartDeliveryOptions
    {
        $this->cartId = $cartId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'cartId'  => $this->getCartId(),
            'data'    => $this->getData(),
            'dateAdd' => $this->getDateAdd(),
            'dateUpd' => $this->getDateUpd(),
        ];
    }
}

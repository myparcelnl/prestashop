<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Model\Core;

/**
 * Overridden core Order class to allow circumventing some bugs when doing strict typing.
 */
class Order extends \Order
{
    /**
     * @var int|string
     * @deprecated This is more often than not a string instead of int. Use getId() instead.
     */
    public $id;

    /**
     * @var int|string
     * @deprecated This is more often than not a string instead of int. Use getIdCarrier() instead.
     */
    public $id_carrier;

    /**
     * @var int|string
     * @deprecated Might be a string instead of int. Use getIdCart() instead.
     */
    public $id_cart;

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }

    /**
     * @return int
     */
    public function getIdCarrier(): int
    {
        return (int) $this->id_carrier;
    }

    /**
     * @return mixed
     */
    public function getIdCart(): int
    {
        return (int) $this->id_cart;
    }

    /**
     * @return int
     * @noinspection PhpCastIsUnnecessaryInspection
     */
    public function getIdOrderCarrier(): int
    {
        return (int) parent::getIdOrderCarrier();
    }
}

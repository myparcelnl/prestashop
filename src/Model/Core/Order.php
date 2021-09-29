<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Model\Core;

use OrderCore;

/**
 * Overridden core Order class to allow circumventing some bugs when doing strict typing.
 */
class Order extends OrderCore
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
}

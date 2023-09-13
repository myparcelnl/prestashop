<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withId(int $id)
 * @method self withIdOrder(int $idOrder)
 * @method self withIdShipment(int $idShipment)
 * @method self withData(string $data)
 * @method self withCreated(string $created)
 * @method self withUpdated(string $updated)
 */
final class MyparcelnlOrderShipmentFactory extends AbstractPsEntityFactory
{
    protected function getEntityClass(): string
    {
        return MyparcelnlOrderShipment::class;
    }
}

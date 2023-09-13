<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withId(int $id)
 * @method self withIdCarrier(int $idCarrier)
 * @method self withMyparcelCarrier(string $myparcelCarrier)
 * @method self withCreated(string $created)
 * @method self withUpdated(string $updated)
 */
final class MyparcelnlCarrierMappingFactory extends AbstractPsEntityFactory
{
    protected function getEntityClass(): string
    {
        return MyparcelnlCarrierMapping::class;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withCarrierId(int $carrierId)
 * @method self withMyparcelCarrier(string $myparcelCarrier)
 */
final class MyparcelnlCarrierMappingFactory extends AbstractPsEntityFactory
{
    protected function getEntityClass(): string
    {
        return MyparcelnlCarrierMapping::class;
    }
}

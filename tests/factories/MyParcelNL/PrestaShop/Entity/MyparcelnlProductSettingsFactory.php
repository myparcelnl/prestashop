<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withId(int $id)
 * @method self withIdProduct(int $idProduct)
 * @method self withData(string $data)
 * @method self withCreated(string $created)
 * @method self withUpdated(string $updated)
 */
final class MyparcelnlProductSettingsFactory extends AbstractPsEntityFactory
{
    protected function getEntityClass(): string
    {
        return MyparcelnlProductSettings::class;
    }
}

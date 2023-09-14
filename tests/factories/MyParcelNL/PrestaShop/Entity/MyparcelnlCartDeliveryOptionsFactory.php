<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Entity;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsEntityFactory;

/**
 * @method self withId(int $id)
 * @method self withCartId(int $cartId)
 * @method self withData(string $data)
 * @method self withDateAdd(string $dateAdd)
 * @method self withDateUpd(string $dateUpd)
 */
final class MyparcelnlCartDeliveryOptionsFactory extends AbstractPsEntityFactory
{
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withDateAdd('2020-01-01 08:00:00')
            ->withDateUpd('2020-01-02 12:00:00');
    }

    protected function getEntityClass(): string
    {
        return MyparcelnlCartDeliveryOptions::class;
    }
}

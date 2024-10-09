<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithActive;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithSupplier;

/**
 * @see \SupplierCore
 * @method $this withDescription(string|array $description)
 * @method $this withLinkRewrite(string $linkRewrite)
 * @method $this withMetaDescription(string|array $metaDescription)
 * @method $this withMetaKeywords(string|array $metaKeywords)
 * @method $this withMetaTitle(string|array $metaTitle)
 * @method $this withName(string $name)
 * @extends AbstractPsObjectModelFactory<Supplier>
 * @see \SupplierCore
 */
final class SupplierFactory extends AbstractPsObjectModelFactory implements WithSupplier, WithActive
{
    protected function getObjectModelClass(): string
    {
        return Supplier::class;
    }
}

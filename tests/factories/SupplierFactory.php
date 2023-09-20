<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @see \SupplierCore
 * @method self withActive(bool $active)
 * @method self withDescription(string|array $description)
 * @method self withIdSupplier(int $idSupplier)
 * @method self withLinkRewrite(string $linkRewrite)
 * @method self withMetaDescription(string|array $metaDescription)
 * @method self withMetaKeywords(string|array $metaKeywords)
 * @method self withMetaTitle(string|array $metaTitle)
 * @method self withName(string $name)
 * @method self withSupplier(Supplier|SupplierFactory $supplier)
 */
final class SupplierFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Supplier::class;
    }
}

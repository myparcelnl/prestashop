<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;

/**
 * @extends AbstractPsObjectModelFactory<Lang>
 * @see \LangCore
 */
final class LangFactory extends AbstractPsObjectModelFactory
{
    protected function getObjectModelClass(): string
    {
        return Lang::class;
    }
}

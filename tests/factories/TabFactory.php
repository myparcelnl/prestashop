<?php

declare(strict_types=1);

use MyParcelNL\PrestaShop\Tests\Factory\AbstractPsObjectModelFactory;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithActive;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithLang;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithShop;

/**
 * @method $this withIdParent(int $idParent)
 * @method $this withClassName(string $className)
 * @method $this withModule(string $module)
 * @method $this withName(array $name)
 * @method $this withRouteName(string $routeName)
 * @extends AbstractPsObjectModelFactory<Tab>
 * @see \TabCore
 */
final class TabFactory extends AbstractPsObjectModelFactory implements WithLang, WithShop, WithActive
{
    /**
     * @param  int|Tab|TabFactory $input
     * @param  array              $attributes
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public function withParent($input, array $attributes = []): self
    {
        return $this->withRelation(Tab::class, 'parent', $input, $attributes);
    }

    protected function getObjectModelClass(): string
    {
        return Tab::class;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use PrestaShop\PrestaShop\Core\Foundation\Database\EntityInterface;

abstract class MockPsObjectModel extends BaseMock implements EntityInterface
{
    /**
     * @param  null|int $id
     * @param  null|int $id_lang
     * @param  null|int $id_shop
     * @param  null|int $translator
     */
    public function __construct(?int $id = null, ?int $id_lang = null, ?int $id_shop = null, ?int $translator = null)
    {
        $this->attributes['id']      = $id;
        $this->attributes['id_lang'] = $id_lang;
        $this->attributes['id_shop'] = $id_shop;
    }

    public static function getRepositoryClassName(): string
    {
        return sprintf('%sRepository', static::class);
    }

    public function delete(): void
    {
        MockPsEntities::delete($this->attributes['id']);
    }

    public function hydrate(array $keyValueData): void
    {
        $this->fill($keyValueData);
    }

    public function save(): void
    {
        MockPsEntities::save($this);
    }
}

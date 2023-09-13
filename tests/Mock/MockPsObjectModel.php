<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
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
        $this->setId($id);

        $this->attributes['id_lang'] = $id_lang;
        $this->attributes['id_shop'] = $id_shop;

        if ($id) {
            $existing = MockPsObjectModels::get($id);

            if (! $existing instanceof static) {
                return;
            }

            $this->hydrate($existing->toArray());
        }
    }

    public static function getRepositoryClassName(): string
    {
        return sprintf('%sRepository', static::class);
    }

    /**
     * @param  bool $auto_date
     * @param  bool $null_values
     *
     * @return bool
     */
    public function add(bool $auto_date = true, bool $null_values = false): bool
    {
        return MockPsObjectModels::add($this);
    }

    public function delete(): void
    {
        MockPsObjectModels::delete($this->attributes['id']);
    }

    public function hydrate(array $keyValueData): void
    {
        $this->fill($keyValueData);

        $this->setId();
    }

    public function save(): void
    {
        $this->update();
    }

    /**
     * @return void
     * @see \ObjectModel::update()
     */
    public function update($null_values = false): bool
    {
        MockPsObjectModels::update($this);

        return true;
    }

    /**
     * @param  null|int $id
     *
     * @return void
     */
    private function setId(?int $id = null): void
    {
        if (! $id) {
            $id = $this->attributes['id'] ?? null;

            if (! $id) {
                return;
            }
        }

        $idKey = sprintf('id_%s', Str::snake(Utils::classBasename(static::class)));

        $this->attributes['id']   = $id;
        $this->attributes[$idKey] = $id;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityInterface;

abstract class MockPsObjectModel extends BaseMock implements EntityInterface
{
    protected $hasCustomIdKey = false;

    /**
     * @param  null|int $id
     * @param  null|int $id_lang
     * @param  null|int $id_shop
     * @param  null|int $translator
     */
    public function __construct(?int $id = null, ?int $id_lang = null, ?int $id_shop = null, ?int $translator = null)
    {
        $this->updateId($id);

        $this->setAttribute('id_lang', $id_lang);
        $this->setAttribute('id_shop', $id_shop);

        if ($id) {
            /** @var $this $existing */
            $existing = MockPsObjectModels::get(static::class, $id);

            $this->hydrate($existing->toArray());
        }
    }

    /**
     * @param $class
     * @param $field
     *
     * @return string[]
     */
    public static function getDefinition($class, $field = null): array
    {
        return [
            'table' => static::getTable(),
        ];
    }

    /**
     * @return string
     */
    public static function getRepositoryClassName(): string
    {
        return sprintf('%sRepository', static::class);
    }

    /**
     * @return string
     */
    protected static function getObjectModelIdentifier(): string
    {
        return Str::snake(Utils::classBasename(static::class));
    }

    /**
     * @return string
     */
    protected static function getTable(): string
    {
        return self::getObjectModelIdentifier();
    }

    /**
     * @param  bool $auto_date
     * @param  bool $null_values
     *
     * @return bool
     */
    public function add(bool $auto_date = true, bool $null_values = false): bool
    {
        MockPsDb::insertRow(static::getTable(), $this->getStorable());

        return MockPsObjectModels::add($this);
    }

    /**
     * @return void
     */
    public function delete(): void
    {
        MockPsDb::deleteRows(static::getTable(), $this->getStorable());

        MockPsObjectModels::delete($this->getId());
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->getAttribute('id');
    }

    /**
     * @param  array $keyValueData
     *
     * @return void
     */
    public function hydrate(array $keyValueData): void
    {
        $this->fill($keyValueData);

        $this->updateId();
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
        MockPsDb::updateRow(static::getTable(), $this->getStorable());

        MockPsObjectModels::update($this);

        return true;
    }

    /**
     * @param  int $id
     *
     * @return $this
     */
    public function withId(int $id): self
    {
        return $this->updateId($id);
    }

    /**
     * @return string
     */
    protected function getAdditionalIdKey(): string
    {
        return sprintf('id_%s', self::getObjectModelIdentifier());
    }

    /**
     * @return array
     */
    protected function getStorable(): array
    {
        $key = $this->hasCustomIdKey ? $this->getAdditionalIdKey() : 'id';

        return array_replace($this->getAttributes(), [$key => $this->getId()]);
    }

    /**
     * @param  int $id
     *
     * @return $this
     */
    protected function setId(int $id): self
    {
        $this->setAttribute('id', $id);

        return $this->hasCustomIdKey ? $this->setAdditionalId($id) : $this;
    }

    /**
     * @param  int $id
     *
     * @return $this
     */
    private function setAdditionalId(int $id): MockPsObjectModel
    {
        $idKey = $this->getAdditionalIdKey();

        return $this->setAttribute($idKey, $id);
    }

    /**
     * @param  null|int $id
     *
     * @return $this
     */
    private function updateId(?int $id = null): self
    {
        $id = $id ?? $this->getId();

        if (! $id) {
            return $this;
        }

        return $this->setId($id);
    }
}

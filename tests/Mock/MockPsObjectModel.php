<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use Exception;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Str;
use ObjectModel;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityInterface;

/**
 * @template T of ObjectModel
 * @property bool $deleted
 */
abstract class MockPsObjectModel extends BaseMock implements EntityInterface
{
    protected bool $hasCustomIdKey = false;

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

            if (! $existing) {
                $this->setId(null);

                return;
            }

            $this->hydrate($existing->toArray(Arrayable::SKIP_NULL));
        }
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public static function all(): Collection
    {
        return MockPsObjectModels::getByClass(static::class);
    }

    /**
     * @param  array $wheres
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public static function findBy(array $wheres): Collection
    {
        $all = MockPsObjectModels::getByClass(static::class);

        foreach ($wheres as $key => $value) {
            $all = $all->filter(function (ObjectModel $model) use ($value, $key) {
                return $model->getAttribute($key) === $value;
            });
        }

        return $all;
    }

    /**
     * @return null|T
     */
    public static function first(): ?ObjectModel
    {
        return MockPsObjectModels::getByClass(static::class)
            ->first();
    }

    /**
     * @param  array $wheres
     *
     * @return T|null
     */
    public static function firstWhere(array $wheres): ?ObjectModel
    {
        return self::findBy($wheres)
            ->first();
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
     * @see          \ObjectModel::add()
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function add(bool $auto_date = true, bool $null_values = false)
    {
        MockPsDb::insertRow(static::getTable(), $this->getStorable());

        return MockPsObjectModels::add($this);
    }

    /**
     * @return bool
     * @see          \ObjectModel::delete()
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function delete()
    {
        $where = $this->hasCustomIdKey
            ? [$this->getAdditionalIdKey() => $this->getId()]
            : ['id' => $this->getId()];

        try {
            MockPsDb::deleteRows(static::getTable(), $where);
            MockPsObjectModels::delete($this->getId());
        } catch (Exception $e) {
            return false;
        }

        $this->setId(null);
        $this->deleted = true;

        return true;
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
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function hydrate(array $keyValueData)
    {
        $this->fill($keyValueData);

        $this->updateId();
    }

    /**
     * @return int
     * @see          \ObjectModel::save()
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function save()
    {
        return (int) $this->update();
    }

    /**
     * @return bool
     * @see          \ObjectModel::softDelete()
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function softDelete()
    {
        $this->setId(null);
        $this->deleted = true;

        return $this->update();
    }

    /**
     * @param  bool $null_values
     *
     * @return bool
     * @see          \ObjectModel::update()
     * @noinspection PhpMissingParamTypeInspection
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
     * @param  null|int $id
     *
     * @return $this
     */
    protected function setId(?int $id): self
    {
        $this->setAttribute('id', $id);

        return $this->hasCustomIdKey ? $this->setAdditionalId($id) : $this;
    }

    /**
     * @param  null|int $id
     *
     * @return $this
     */
    private function setAdditionalId(?int $id): MockPsObjectModel
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

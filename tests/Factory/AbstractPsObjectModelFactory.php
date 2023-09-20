<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModel;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;
use MyParcelNL\Sdk\src\Support\Str;
use ObjectModel;

/**
 * @template T of ObjectModel
 * @method self withDateAdd(string $dateAdd)
 * @method self withDateUpd(string $dateUpd)
 * @method self withDeleted(bool $deleted)
 * @implements PsObjectModelFactoryInterface<T>
 * @extends AbstractPsFactory<T>
 */
abstract class AbstractPsObjectModelFactory extends AbstractPsModelFactory implements PsObjectModelFactoryInterface
{
    /**
     * @var PsObjectModelFactoryInterface[]
     */
    private $additionalModelsToStore = [];

    /**
     * @var null|int
     */
    private $id;

    /**
     * @param  null|int $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;

        parent::__construct();
    }

    /**
     * @return T
     */
    public function store(): ObjectModel
    {
        $result = parent::store();

        foreach ($this->additionalModelsToStore as $modelToStore) {
            $modelToStore->store();
        }

        return $result;
    }

    /**
     * @param  int $id
     *
     * @return $this
     */
    public function withId(int $id): self
    {
        return $this->with(['id' => $id]);
    }

    /**
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return \MyParcelNL\PrestaShop\Tests\Factory\AbstractPsFactory
     */
    protected function addAttribute(string $attribute, $value, array $attributes = []): AbstractPsFactory
    {
        if (Str::startsWith($attribute, 'id_')) {
            return $this->withModel(Str::after($attribute, 'id_'), $value, $attributes);
        }

        if ($value instanceof PsObjectModelFactoryInterface || $value instanceof MockPsObjectModel) {
            return $this->withModel($attribute, $value, $attributes);
        }

        return parent::addAttribute($attribute, $value);
    }

    /**
     * @return $this
     */
    protected function createDefault(): FactoryInterface
    {
        return $this
            ->withId($this->id ?? $this->getNextId())
            ->withDeleted(false)
            ->withDateAdd('2023-01-01 00:00:00')
            ->withDateUpd('2023-01-01 00:00:00');
    }

    /**
     * @param  string $class
     * @param  array  $attributes
     *
     * @return T
     */
    protected function createObject(string $class, array $attributes): ObjectModel
    {
        /** @var T $created */
        $created = new $class();
        $created->hydrate(
            array_map(static function ($value) {
                return $value instanceof FactoryInterface ? $value->make() : $value;
            }, $attributes)
        );

        return $created;
    }

    /**
     * @return string
     */
    protected function getClass(): string
    {
        return $this->getObjectModelClass();
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getObjectModelClass(): string;

    /**
     * @return array
     */
    protected function resolveAttributes(): array
    {
        return array_replace(parent::resolveAttributes(), ['id' => $this->getId()]);
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save($model): void
    {
        MockPsObjectModels::update($model);
    }

    /**
     * @param  string                                        $key
     * @param  int|ObjectModel|PsObjectModelFactoryInterface $input
     * @param  array                                         $attributes
     *
     * @return $this
     */
    protected function withModel(string $key, $input, array $attributes = []): self
    {
        if (is_int($input)) {
            $model = Str::after($key, 'id_');

            return $this->withModel($model, MockPsObjectModels::get($model, $input), $attributes);
        }

        if ($input instanceof PsFactoryInterface) {
            $this->additionalModelsToStore[] = $input;

            $model = $input
                ->with($attributes)
                ->make();

            return $this->withModel($key, $model);
        }

        $idKey = sprintf('id_%s', Str::snake($key));

        return $this->with([$idKey => $input->id]);
    }

    /**
     * @param  string                                        $key
     * @param  int|ObjectModel|PsObjectModelFactoryInterface $input
     * @param  array                                         $attributes
     * @param  string                                        $foreignKey
     *
     * @return $this
     */
    protected function withRelation(string $key, $input, array $attributes, string $foreignKey): self
    {
        return $this->withModel($key, $input, array_replace($attributes, [$foreignKey => $this->getId()]));
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Factory;

use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithActive;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithSoftDeletes;
use MyParcelNL\PrestaShop\Tests\Factory\Concern\WithTimestamps;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Factory\Contract\PsObjectModelFactoryInterface;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModel;
use MyParcelNL\PrestaShop\Tests\Mock\MockPsObjectModels;
use MyParcelNL\Sdk\src\Support\Str;
use ObjectModel;
use function MyParcelNL\PrestaShop\psFactory;

/**
 * @template T of ObjectModel
 * @method $this withId(int $id)
 * @implements PsObjectModelFactoryInterface<T>
 * @extends AbstractPsFactory<T>
 */
abstract class AbstractPsObjectModelFactory extends AbstractPsModelFactory implements PsObjectModelFactoryInterface
{
    /**
     * @var PsObjectModelFactoryInterface[]
     */
    private array $additionalModelsToStore = [];

    /**
     * @var null|int
     */
    private ?int $id;

    /**
     * @param  null|int $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;

        parent::__construct();
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getObjectModelClass(): string;

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
     * @param  string $attribute
     * @param  mixed  $value
     * @param  array  $attributes
     *
     * @return \MyParcelNL\PrestaShop\Tests\Factory\AbstractPsFactory
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function addAttribute(string $attribute, $value, array $attributes = []): AbstractPsFactory
    {
        if (Str::startsWith($attribute, 'id_')) {
            return $this->withModel($attribute, $value, $attributes);
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
        $factory = $this
            ->withId($this->id ?? $this->getNextId());

        if ($factory instanceof WithTimestamps) {
            $factory
                ->withDateAdd('2023-01-01 00:00:00')
                ->withDateUpd('2023-01-01 00:00:00');
        }

        if ($factory instanceof WithSoftDeletes) {
            $factory->withDeleted(false);
        }

        if ($factory instanceof WithActive) {
            $factory->withActive(true);
        }

        return $factory;
    }

    /**
     * @param  string $key
     *
     * @return string
     */
    protected function createIdKey(string $key): string
    {
        return sprintf('id_%s', Str::snake($key));
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
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    protected function save($model): void
    {
        $model->update();
    }

    /**
     * @param  string                                        $key
     * @param  int|ObjectModel|PsObjectModelFactoryInterface $input
     * @param  array                                         $attributes
     * @param  null|string                                   $keyOverride
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function withModel(string $key, $input, array $attributes = [], ?string $keyOverride = null): self
    {
        if (is_int($input)) {
            $class         = Str::after($key, 'id_');
            $existingModel = MockPsObjectModels::get($class, $input);

            if ($existingModel) {
                return $this->withModel($class, $existingModel, $attributes, $keyOverride);
            }

            /** @var PsObjectModelFactoryInterface $factory */
            $factory = psFactory(Str::studly($class), $input);

            return $this->withModel($class, $factory, $attributes, $keyOverride);
        }

        if ($input instanceof PsFactoryInterface) {
            $this->additionalModelsToStore[] = $input;

            $model = $input
                ->with($attributes)
                ->make();

            return $this->withModel($key, $model, [], $keyOverride);
        }

        $idKey = $this->createIdKey($keyOverride ?? $key);

        return $this->with([$idKey => $input->id]);
    }

    /**
     * @template Model of ObjectModel
     * @template Instance of Model
     * @param  class-string<Model>                                  $class
     * @param  string                                               $key
     * @param  int|Instance|PsObjectModelFactoryInterface<Instance> $input
     * @param  array                                                $attributes
     *
     * @return $this
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    protected function withRelation(
        string $class,
        string $key,
               $input,
        array  $attributes
    ): self {
        return $this->withModel($class, $input, $attributes, $key);
    }
}

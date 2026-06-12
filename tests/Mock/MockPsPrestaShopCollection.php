<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use ObjectModel;

/**
 * Minimal stand-in for PrestaShop's PrestaShopCollection. Supports only the subset the plugin uses:
 * an optional primary-id "in" filter plus getResults(). Backed by the same in-memory model registry
 * as new Order($id), so collection results stay consistent with single-model lookups in tests.
 */
abstract class MockPsPrestaShopCollection
{
    /**
     * @var class-string<ObjectModel>
     */
    private $className;

    /**
     * @var null|int[]
     */
    private $ids;

    /**
     * @param  class-string<ObjectModel> $className
     * @param  null|int                  $idLang
     */
    public function __construct(string $className, ?int $idLang = null)
    {
        $this->className = $className;
        $this->ids       = null;
    }

    /**
     * Record a filter. Only the primary-id "in" filter the repository relies on is supported; the
     * field and operator are accepted for signature compatibility but not otherwise interpreted.
     *
     * @param  string $field
     * @param  string $operator
     * @param  mixed  $value
     *
     * @return $this
     */
    public function where(string $field, string $operator, $value): self
    {
        $this->ids = array_map('intval', (array) $value);

        return $this;
    }

    /**
     * Return the matching models. With an id filter, hydrates each existing id through the same path
     * as new Order($id) and drops ids that do not exist; without a filter, returns every stored model.
     *
     * @return \ObjectModel[]
     */
    public function getResults(): array
    {
        if (null === $this->ids) {
            return MockPsObjectModels::getByClass($this->className)
                ->values()
                ->all();
        }

        $results = [];

        foreach ($this->ids as $id) {
            $model = new $this->className($id);

            if ($model->id) {
                $results[] = $model;
            }
        }

        return $results;
    }
}

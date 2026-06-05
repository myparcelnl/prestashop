<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

/**
 * Minimal Doctrine QueryBuilder/Query mock used by migration tests to iterate a
 * collection in batches via setFirstResult/setMaxResults/getQuery()->getResult().
 */
final class MockPsQueryBuilder
{
    /**
     * @var array<int, object>
     */
    private $entities;

    /**
     * @var int
     */
    private $firstResult = 0;

    /**
     * @var null|int
     */
    private $maxResults;

    /**
     * @param  array<int, object> $entities
     */
    public function __construct(array $entities)
    {
        $this->entities = array_values($entities);
    }

    public function setFirstResult(int $firstResult): self
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    public function setMaxResults(?int $maxResults): self
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    public function getQuery(): self
    {
        return $this;
    }

    /**
     * @return array<int, object>
     */
    public function getResult(): array
    {
        return array_slice($this->entities, $this->firstResult, $this->maxResults);
    }
}

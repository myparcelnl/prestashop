<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Tests\Mock;

use MyParcelNL\Sdk\Support\Str;

/**
 * Minimal Doctrine QueryBuilder/Query mock used by migration tests. Mirrors the keyset pagination the
 * migration performs: where('e.<field> > :cursor') + setParameter('cursor', N) + orderBy('e.<field>')
 * + setMaxResults(), iterated via getQuery()->getResult().
 */
final class MockPsQueryBuilder
{
    /**
     * @var array<int, object>
     */
    private $entities;

    /**
     * @var null|string
     */
    private $cursorField;

    /**
     * @var int
     */
    private $cursorValue = 0;

    /**
     * @var null|string
     */
    private $orderField;

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

    /**
     * Parses the keyset predicate "e.<field> > :cursor" to learn which identifier to page on.
     */
    public function where(string $predicate): self
    {
        if (preg_match('/e\.(\w+)\s*>/', $predicate, $matches)) {
            $this->cursorField = $matches[1];
        }

        return $this;
    }

    /**
     * @param  string $name
     * @param  mixed  $value
     */
    public function setParameter(string $name, $value): self
    {
        if ('cursor' === $name) {
            $this->cursorValue = (int) $value;
        }

        return $this;
    }

    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        if (preg_match('/e\.(\w+)/', $field, $matches)) {
            $this->orderField = $matches[1];
        }

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
        $rows = $this->entities;

        if (null !== $this->cursorField) {
            $getter = $this->getterFor($this->cursorField);
            $rows   = array_values(array_filter($rows, function (object $entity) use ($getter): bool {
                return $entity->{$getter}() > $this->cursorValue;
            }));
        }

        if (null !== $this->orderField) {
            $getter = $this->getterFor($this->orderField);
            usort($rows, static function (object $a, object $b) use ($getter): int {
                return $a->{$getter}() <=> $b->{$getter}();
            });
        }

        return array_slice($rows, 0, $this->maxResults);
    }

    private function getterFor(string $field): string
    {
        return sprintf('get%s', Str::studly($field));
    }
}

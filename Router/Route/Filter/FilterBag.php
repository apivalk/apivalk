<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

/**
 * @implements \IteratorAggregate<string, FilterInterface>
 */
class FilterBag implements \IteratorAggregate, \Countable
{
    /** @var FilterInterface[] */
    private array $filters = [];

    /** @var array<int, array{field: string, operator: string}> */
    private array $violations = [];

    public function set(FilterInterface $filter): void
    {
        $this->filters[$filter->getField()] = $filter;
    }

    public function has(string $field): bool
    {
        return isset($this->filters[$field]);
    }

    public function get(string $field): ?FilterInterface
    {
        return $this->filters[$field] ?? null;
    }

    /** @return FilterInterface[] */
    public function all(): array
    {
        return $this->filters;
    }

    /**
     * @return \Iterator<int|string, FilterInterface>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->filters);
    }

    public function count(): int
    {
        return \count($this->filters);
    }

    /**
     * Operators a client sent that the field does not allow. Turned into validation
     * errors by the middleware, so an unknown operator is a 422 rather than a filter
     * that silently does nothing.
     */
    public function addViolation(string $field, string $operator): void
    {
        $this->violations[] = ['field' => $field, 'operator' => $operator];
    }

    /** @return array<int, array{field: string, operator: string}> */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Magic getter for easy access to filters.
     *
     * $filterBag->status -> FilterInterface|null
     *
     * @return FilterInterface|null
     */
    public function __get(string $key): ?FilterInterface
    {
        return $this->get($key);
    }
}

<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Filter;

/**
 * @implements \IteratorAggregate<string, AbstractFilter>
 */
class FilterBag implements \IteratorAggregate, \Countable
{
    /** @var AbstractFilter[] */
    private $filters = [];

    public function set(AbstractFilter $filter): void
    {
        $this->filters[$filter->getField()] = $filter;
    }

    public function has(string $field): bool
    {
        return isset($this->filters[$field]);
    }

    public function get(string $field): ?AbstractFilter
    {
        return $this->filters[$field] ?? null;
    }

    /**
     * @return \Iterator<int|string, AbstractFilter>
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
     * Magic getter for easy access to filters.
     *
     * $filterBag->status -> AbstractFilter|null
     *
     * @return AbstractFilter|null
     */
    public function __get(string $key): ?AbstractFilter
    {
        return $this->get($key);
    }
}

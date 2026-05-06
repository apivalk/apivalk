<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Sort;

/**
 * @implements \IteratorAggregate<string, Sort>
 */
class SortBag implements \IteratorAggregate, \Countable
{
    /** @var Sort[] */
    private $sorts = [];
    /** @var null|Sort[] */
    private $requested = null;

    public function set(Sort $sort): void
    {
        $this->sorts[$sort->getField()] = $sort;
        $this->requested = null;
    }

    public function has(string $field): bool
    {
        return isset($this->sorts[$field]);
    }

    public function get(string $field): ?Sort
    {
        return $this->sorts[$field] ?? null;
    }

    /**
     * Sorts the user explicitly requested via `?order_by=…`, in submission order.
     *
     * Empty if the user did not provide `order_by`. Route-default sorts are excluded.
     *
     * @return list<Sort>
     */
    public function getRequested(): array
    {
        if ($this->requested !== null) {
            return $this->requested;
        }

        $requested = [];

        foreach ($this->sorts as $sort) {
            if ($sort->isRequested()) {
                $requested[] = $sort;
            }
        }

        $this->requested = $requested;

        return $this->requested;
    }

    /**
     * @return \Iterator<int|string, Sort>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->sorts);
    }

    public function count(): int
    {
        return \count($this->sorts);
    }

    /**
     * Magic getter to direct access values of an order bag.
     *
     * $sortBag->status is the same as $oderBag->get('status')
     *
     * In requests, you can access it like this:
     * $request->sorting()->status -> Sort|null
     *
     * @return Sort|null
     */
    public function __get(string $key): ?Sort
    {
        $sort = $this->get($key);

        return $sort ?? null;
    }
}

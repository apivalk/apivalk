<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Sort;

/**
 * @implements \IteratorAggregate<string, Sort>
 */
class SortBag implements \IteratorAggregate, \Countable
{
    /** @var Sort[] */
    private $orders = [];

    public function set(Sort $order): void
    {
        $this->orders[$order->getField()] = $order;
    }

    public function has(string $field): bool
    {
        return isset($this->orders[$field]);
    }

    public function get(string $field): ?Sort
    {
        return $this->orders[$field] ?? null;
    }

    /**
     * @return \Iterator<int|string, Sort>
     */
    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->orders);
    }

    public function count(): int
    {
        return \count($this->orders);
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
        $order = $this->get($key);
        if ($order === null) {
            return null;
        }

        return $order;
    }
}

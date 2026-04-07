<?php

declare(strict_types=1);

namespace apivalk\apivalk\Router\Route\Order;

/**
 * @implements \IteratorAggregate<string, Order>
 */
class OrderBag implements \IteratorAggregate, \Countable
{
    /** @var Order[] */
    private $orders = [];

    public function set(Order $order): void
    {
        $this->orders[$order->getField()] = $order;
    }

    public function has(string $field): bool
    {
        return isset($this->orders[$field]);
    }

    public function get(string $field): ?Order
    {
        return $this->orders[$field] ?? null;
    }

    /**
     * @return \Iterator<int|string, Order>
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
     * $orderBag->status is the same as $oderBag->get('status')
     *
     * In requests, you can access it like this:
     * $request->ordering()->status -> Order|null
     *
     * @return Order|null
     */
    public function __get(string $key): ?Order
    {
        $order = $this->get($key);
        if ($order === null) {
            return null;
        }

        return $order;
    }
}

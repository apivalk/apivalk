<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Order;

use apivalk\apivalk\Router\Route\Order\Order;
use apivalk\apivalk\Router\Route\Order\OrderBag;
use PHPUnit\Framework\TestCase;

class OrderBagTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $orderBag = new OrderBag();
        $order = Order::asc('status');

        $orderBag->set($order);

        $this->assertTrue($orderBag->has('status'));
        $this->assertSame($order, $orderBag->get('status'));
    }

    public function testGetReturnsNullForUnknownField(): void
    {
        $orderBag = new OrderBag();

        $this->assertFalse($orderBag->has('unknown'));
        $this->assertNull($orderBag->get('unknown'));
    }

    public function testCount(): void
    {
        $orderBag = new OrderBag();

        $this->assertCount(0, $orderBag);

        $orderBag->set(Order::asc('status'));
        $this->assertCount(1, $orderBag);

        $orderBag->set(Order::desc('price'));
        $this->assertCount(2, $orderBag);
    }

    public function testSetOverridesExistingField(): void
    {
        $orderBag = new OrderBag();

        $orderBag->set(Order::asc('status'));
        $orderBag->set(Order::desc('status'));

        $this->assertCount(1, $orderBag);
        $this->assertTrue($orderBag->has('status'));
        $this->assertTrue($orderBag->get('status')->isDesc());
        $this->assertFalse($orderBag->get('status')->isAsc());
    }

    public function testIterator(): void
    {
        $orderBag = new OrderBag();
        $statusOrder = Order::asc('status');
        $priceOrder = Order::desc('price');

        $orderBag->set($statusOrder);
        $orderBag->set($priceOrder);

        $orders = iterator_to_array($orderBag->getIterator());

        $this->assertArrayHasKey('status', $orders);
        $this->assertArrayHasKey('price', $orders);
        $this->assertSame($statusOrder, $orders['status']);
        $this->assertSame($priceOrder, $orders['price']);
    }

    public function testMagicGet(): void
    {
        $orderBag = new OrderBag();
        $order = Order::asc('status');

        $orderBag->set($order);

        $this->assertSame($order, $orderBag->status);
    }

    public function testMagicGetReturnsNullForUnknownField(): void
    {
        $orderBag = new OrderBag();

        $this->assertNull($orderBag->unknown);
    }
}

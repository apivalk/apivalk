<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Order;

use apivalk\apivalk\Router\Route\Order\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testAscFactory(): void
    {
        $order = Order::asc('status');

        $this->assertInstanceOf(Order::class, $order);
        $this->assertTrue($order->isAsc());
        $this->assertFalse($order->isDesc());
        $this->assertSame('status', $order->getField());
    }

    public function testDescFactory(): void
    {
        $order = Order::desc('price');

        $this->assertInstanceOf(Order::class, $order);
        $this->assertFalse($order->isAsc());
        $this->assertTrue($order->isDesc());
        $this->assertSame('price', $order->getField());
    }

    public function testConstructorDefaultsToAsc(): void
    {
        $order = new Order('id');

        $this->assertTrue($order->isAsc());
        $this->assertFalse($order->isDesc());
        $this->assertSame('id', $order->getField());
    }

    public function testIsDescIsInverseOfIsAsc(): void
    {
        $ascOrder = Order::asc('foo');
        $descOrder = Order::desc('bar');

        $this->assertSame(!$ascOrder->isAsc(), $ascOrder->isDesc());
        $this->assertSame(!$descOrder->isAsc(), $descOrder->isDesc());
    }
}

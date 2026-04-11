<?php

declare(strict_types=1);

namespace apivalk\apivalk\Tests\PhpUnit\Router\Route\Sort;

use apivalk\apivalk\Router\Route\Sort\Sort;
use apivalk\apivalk\Router\Route\Sort\SortBag;
use PHPUnit\Framework\TestCase;

class SortBagTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $sortBag = new SortBag();
        $sort = Sort::asc('status');

        $sortBag->set($sort);

        $this->assertTrue($sortBag->has('status'));
        $this->assertSame($sort, $sortBag->get('status'));
    }

    public function testGetReturnsNullForUnknownField(): void
    {
        $sortBag = new SortBag();

        $this->assertFalse($sortBag->has('unknown'));
        $this->assertNull($sortBag->get('unknown'));
    }

    public function testCount(): void
    {
        $sortBag = new SortBag();

        $this->assertCount(0, $sortBag);

        $sortBag->set(Sort::asc('status'));
        $this->assertCount(1, $sortBag);

        $sortBag->set(Sort::desc('price'));
        $this->assertCount(2, $sortBag);
    }

    public function testSetOverridesExistingField(): void
    {
        $sortBag = new SortBag();

        $sortBag->set(Sort::asc('status'));
        $sortBag->set(Sort::desc('status'));

        $this->assertCount(1, $sortBag);
        $this->assertTrue($sortBag->has('status'));
        $this->assertTrue($sortBag->get('status')->isDesc());
        $this->assertFalse($sortBag->get('status')->isAsc());
    }

    public function testIterator(): void
    {
        $sortBag = new SortBag();
        $statusSort = Sort::asc('status');
        $priceSort = Sort::desc('price');

        $sortBag->set($statusSort);
        $sortBag->set($priceSort);

        $sorts = iterator_to_array($sortBag->getIterator());

        $this->assertArrayHasKey('status', $sorts);
        $this->assertArrayHasKey('price', $sorts);
        $this->assertSame($statusSort, $sorts['status']);
        $this->assertSame($priceSort, $sorts['price']);
    }

    public function testMagicGet(): void
    {
        $sortBag = new SortBag();
        $sort = Sort::asc('status');

        $sortBag->set($sort);

        $this->assertSame($sort, $sortBag->status);
    }

    public function testMagicGetReturnsNullForUnknownField(): void
    {
        $sortBag = new SortBag();

        $this->assertNull($sortBag->unknown);
    }
}
